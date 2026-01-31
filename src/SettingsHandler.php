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
        if (!isset($this->definitions[$key])) {
            return false;
        }

        if ($value === null) {
            return $this->delete($key);
        }
        if(($this->definitions[$key]['emptyMeansDefault'] ?? false) && ($value === '' || $value === null)) {
            return $this->delete($key);
        }

        $value = $this->castValue($key, $value);
        
        // Database save (UPSERT logic)
        $db = Yii::$app->db;
        $exists = (new Query())
            ->from($this->tableName)
            ->where(['key_name' => $key])
            ->exists();

        $success = false;

        if ($exists) {
            $success = $db->createCommand()
                ->update($this->tableName, [
                    'value' => $value, 
                    'updated_at' => date('Y-m-d H:i:s')
                ], ['key_name' => $key])
                ->execute();
        } else {
            $success = $db->createCommand()
                ->insert($this->tableName, [
                    'key_name' => $key,
                    'value' => $value,
                ])
                ->execute();
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

        if ($success || !isset($this->_values[$key])) {
            // Clear cache
            $this->deleteCache();
            // Update internal array
            unset($this->_values[$key]);
            return true;
        }

        return false;
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
}
