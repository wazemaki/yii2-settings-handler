<?php

namespace wazemaki\settings;

use Yii;
use yii\base\Component;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * SettingsHandler Component
 * 
 * Flexible database-backed settings manager with caching and type casting.
 * 
 * Usage in config:
 * ```php
 * 'components' => [
 *     'settings' => [
 *         'class' => 'wazemaki\settings\SettingsHandler',
 *         'cacheDuration' => 3600,
 *         'definitions' => [
 *             'site_name' => [
 *                 'label' => 'Site Name',
 *                 'dataType' => 'string',
 *                 'inputType' => 'text',
 *                 'defaultValue' => 'My Site',
 *             ],
 *         ],
 *     ],
 * ],
 * ```
 */
class SettingsHandler extends Component
{
    /**
     * Configuration definitions array.
     * Structure: ['key' => ['label' => '...', 'dataType' => '...', 'defaultValue' => ...]]
     * 
     * @var array
     */
    public $definitions = [];

    /**
     * Cache duration in seconds (default: 1 hour)
     * 
     * @var int
     */
    public $cacheDuration = 3600;

    /**
     * Cache key prefix
     * 
     * @var string
     */
    public $cacheKey = 'settings_handler_';

    /**
     * Database table name for settings
     * 
     * @var string
     */
    public $tableName = '{{%system_settings}}';

    /**
     * Internal storage for loaded values
     * 
     * @var array
     */
    private $_values = [];

    public $saveOnlyDefined = true; // Only allow saving keys that are defined in $definitions

    /**
     * Enable tab mode for delimiter sections
     * If true, delimiters will create tabs instead of inline section headers
     * 
     * @var bool
     */
    public $enableTabs = false;

    private $modifiedCount = 0;
    private $defaultValuesCount = 0;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->loadSettings();
    }

    /**
     * Get setting value. Returns default if NULL or empty.
     *
     * @param string $key Setting key
     * @return mixed
     */
    public function get($key)
    {
        // Check if there's a concrete value in the database (not NULL, not empty)
        if (isset($this->_values[$key]) && $this->_values[$key] !== null && $this->_values[$key] !== '') {
            return $this->castValue($key, $this->_values[$key]);
        }

        // Fall back to default value
        if (isset($this->definitions[$key]['defaultValue'])) {
            return $this->castValue($key, $this->definitions[$key]['defaultValue']);
        }

        return null;
    }

    /**
     * Check if the setting uses its default value (NULL or not in DB)
     *
     * @param string $key Setting key
     * @return bool
     */
    public function isDefault($key): bool
    {
        return !isset($this->_values[$key]) || $this->_values[$key] === null;
    }

    /**
     * Save setting value
     * 
     * @param string $key Setting key
     * @param mixed $value Setting value
     * @return bool
     */
    public function set($key, $value)
    {
        // Don't allow saving if definition doesn't exist
        if ($this->saveOnlyDefined && !isset($this->definitions[$key])) {
            return false;
        }

        if(($this->definitions[$key]['emptyMeansDefault'] ?? false) && ($value === '' || $value === null)) {
            return $this->delete($key);
        }
        if ($value === null) {
            return $this->delete($key);
        }

        // Array/json-backed settings (e.g. multiselect) must be JSON-encoded
        // for storage - castValue() below only decodes JSON strings for
        // reading, it never encodes a PHP array back into a string.
        $dataType = $this->definitions[$key]['dataType'] ?? null;
        if (in_array($dataType, ['array', 'json'], true)) {
            $value = is_string($value) ? $value : json_encode($value);
        } else {
            $value = $this->castValue($key, $value);
        }
        
        // Database save (UPSERT logic)
        $db = Yii::$app->db;
        $exists = (new Query())
            ->from($this->tableName)
            ->where(['key_name' => $key])
            ->exists();

        $success = false;

        if ($exists) {
            $oldValue = (new Query())
                ->select('value')
                ->from($this->tableName)
                ->where(['key_name' => $key])
                ->scalar();
            if ($oldValue === $value) {
                return true; // No change, no update needed
            }
            
            $success = $db->createCommand()
                ->update($this->tableName, [
                    'value' => $value, 
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['key_name' => $key])
                ->execute();
            if ($success && $oldValue != $value) {
                $this->modifiedCount++;
            }
        } else {
            $success = $db->createCommand()
                ->insert($this->tableName, [
                    'key_name' => $key,
                    'value' => $value,
                ])
                ->execute();
            if ($success) {
                $this->modifiedCount++;
            }
        }

        if ($success) {
            // Clear cache and update internal array
            $this->deleteCache();
            $this->_values[$key] = $value;
        }

        return $success;
    }

    /**
     * Delete setting from database (revert to default)
     *
     * @param string $key Setting key
     * @return bool
     */
    public function delete($key): bool
    {
        $success = Yii::$app->db->createCommand()
            ->delete($this->tableName, ['key_name' => $key])
            ->execute();

        if ($success) {
            $this->modifiedCount++;
        } elseif (isset($this->definitions[$key]['defaultValue'])) {
            $this->defaultValuesCount++;
        }

        if ($success || !isset($this->_values[$key])) {
            // Clear cache
            $this->deleteCache();
            // Update internal array
            unset($this->_values[$key]);
            return true;
        }

        return false;
    }

    public function clearModifiedCount()
    {
        $this->modifiedCount = 0;
    }
    public function getModifiedCount(): int
    {
        return $this->modifiedCount;
    }

    public function clearDefaultValuesCount()
    {
        $this->defaultValuesCount = 0;
    }
    public function getDefaultValuesCount(): int
    {
        return $this->defaultValuesCount;
    }

    /**
     * Get all definitions
     * 
     * @return array
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * Load settings from cache or database
     */
    protected function loadSettings()
    {
        $dbValues = Yii::$app->cache->getOrSet($this->cacheKey, function () {
            return $this->getAllFromDb();
        }, $this->cacheDuration);

        // Merge with params.php if available
        $this->_values = array_merge(Yii::$app->params, $dbValues);
    }

    /**
     * Get all settings from database
     * 
     * @return array
     */
    public function getAllFromDb()
    {
        $rows = (new Query())
            ->select(['key_name', 'value'])
            ->from($this->tableName)
            ->all();
        
        return ArrayHelper::map($rows, 'key_name', 'value');
    }

    /**
     * Cast value to appropriate type based on definition
     * 
     * @param string $key Setting key
     * @param mixed $value Raw value
     * @return mixed Casted value
     */
    protected function castValue($key, $value)
    {
        switch ($this->definitions[$key]['dataType'] ?? null) {
            case 'integer':
            case 'int':
                return (int)$value;
            case 'boolean':
            case 'bool':
                return (bool)$value;
            case 'float':
                return (float)$value;
            case 'array':
            case 'json':
                if(is_string($value)) {
                    return json_decode($value, true);
                }
                return $value;
            default:
                return (string)$value;
        }
    }

    public function deleteCache()
    {
        Yii::$app->cache->delete($this->cacheKey);
    }

    /**
     * Get delimiter sections from definitions
     * 
     * @return array Array of delimiter keys
     */
    public function getDelimiters()
    {
        $delimiters = [];
        foreach ($this->definitions as $key => $def) {
            if (($def['inputType'] ?? '') === 'delimiter') {
                $delimiters[] = $key;
            }
        }
        return $delimiters;
    }

    /**
     * Group settings by delimiter sections
     * Returns array where keys are delimiter keys and values are arrays of setting keys
     * 
     * @return array
     */
    public function getGroupedByDelimiter()
    {
        $groups = [];
        $currentGroup = '__default__';
        $groups[$currentGroup] = [];

        foreach ($this->definitions as $key => $def) {
            if (($def['inputType'] ?? '') === 'delimiter') {
                $currentGroup = $key;
                $groups[$currentGroup] = [];
            } else {
                $groups[$currentGroup][] = $key;
            }
        }

        return $groups;
    }
}
