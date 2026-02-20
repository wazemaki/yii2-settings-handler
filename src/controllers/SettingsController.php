<?php

namespace wazemaki\settings\controllers;

use Yii;
use yii\web\Controller;
use yii\base\DynamicModel;
use yii\filters\AccessControl;

/**
 * Settings Controller
 * 
 * Manages system settings through a web interface.
 * 
 * IMPORTANT: Configure access control in your application!
 * This controller allows all authenticated users by default.
 * Override behaviors() in your application to restrict access to admins only.
 */
class SettingsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->viewPath = dirname(__DIR__) . '/views';
    }
    
    /**
     * @inheritdoc
     * 
     * Default: Allow all authenticated users.
     * Override this in your application to restrict to admins only:
     * 
     * ```php
     * 'matchCallback' => function() {
     *     return Yii::$app->user->identity->isAdmin;
     * }
     * ```
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Authenticated users
                        // Uncomment and customize for admin-only access:
                        // 'matchCallback' => function() {
                        //     return Yii::$app->user->identity->isAdmin ?? false;
                        // }
                    ],
                ],
            ],
        ];
    }

    /**
     * Settings management page
     * 
     * @return string
     */
    public function actionIndex()
    {
        $settings = Yii::$app->settings;
        $definitions = $settings->getDefinitions();

        // Collect all non-delimiter keys
        $keys = [];
        foreach ($definitions as $key => $def) {
            $inputType = $def['inputType'] ?? '';
            if (!in_array($inputType, ['delimiter'])) {
                $keys[] = $key;
            }
        }
        
        $model = new DynamicModel($keys);
        
        // Build dynamic model with validation rules
        foreach ($definitions as $key => $def) {
            $inputType = $def['inputType'] ?? '';
            if (in_array($inputType, ['delimiter'])) {
                continue;
            }

            // Set attribute label
            if (isset($def['label'])) {
                $model->setAttributeLabel($key, $def['label']);
            }
            
            // Add custom rules if defined
            if (isset($def['rules'])) {
                foreach ($def['rules'] as $rule) {
                    $validator = array_shift($rule);
                    $model->addRule($key, $validator, $rule);
                }
            } else {
                // Default rules based on data type
                switch ($def['dataType'] ?? 'string') {
                    case 'integer':
                        $model->addRule($key, 'integer');
                        break;
                    case 'boolean':
                        $model->addRule($key, 'boolean');
                        break;
                    case 'string':
                    default:
                        $model->addRule($key, 'string');
                        break;
                }
            }
        }

        // Handle form submission
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $settings = Yii::$app->settings;
            $settingsData = $model->attributes;
            $resetData = Yii::$app->request->post('settings-reset', []);
            
            $settings->clearModifiedCount();
            $settings->clearDefaultValuesCount();
            
            foreach ($definitions as $key => $def) {
                $inputType = $def['inputType'] ?? '';
                if (in_array($inputType, ['delimiter'])) {
                    continue;
                }
                
                // If reset checkbox is checked, delete from database
                if (isset($resetData[$key]) && $resetData[$key]) {
                    $settings->delete($key);
                    continue;
                }
                
                if (isset($settingsData[$key])) {
                    $value = $settingsData[$key];
                    $settings->set($key, $value);
                }
            }
            
            $message = [];
            $successCount = $settings->getModifiedCount();
            $resetCount = $settings->getDefaultValuesCount();
            if ($successCount > 0) {
                $message[] = "$successCount beállítás mentve";
            }
            if ($resetCount > 0) {
                $message[] = "$resetCount visszaállítva alapértelmezettre";
            }
            
            Yii::$app->session->addFlash('success', implode(', ', $message) ?: 'Nincs változtatás.');
            return $this->refresh();
        }

        // Load current values
        $model->load($settings->getAllFromDb(), '');

        // Determine which view and tab to use
        $viewFile = 'index';
        $activeTab = null;
        $groupedByDelimiter = [];
        
        if ($settings->enableTabs) {
            $viewFile = 'index-tabs';
            $groupedByDelimiter = $settings->getGroupedByDelimiter();
            $delimiters = $settings->getDelimiters();
            
            // Get active tab from GET parameter
            $requestedTab = Yii::$app->request->get('tab');
            
            if ($requestedTab && isset($definitions[$requestedTab])) {
                $activeTab = $requestedTab;
            } elseif (!empty($delimiters)) {
                // Default to first delimiter
                $activeTab = $delimiters[0];
            } else {
                $activeTab = '__default__';
            }
        }

        return $this->render($viewFile, [
            'model' => $model,
            'definitions' => $definitions,
            'groupedByDelimiter' => $groupedByDelimiter,
            'activeTab' => $activeTab,
        ]);
    }

    /**
     * Clear application cache
     * 
     * @return \yii\web\Response
     */
    public function actionClearCache()
    {
        Yii::$app->cache->flush();
        Yii::$app->session->setFlash('success', 'Cache sikeresen törölve.');
        return $this->redirect(['index']);
    }
}
