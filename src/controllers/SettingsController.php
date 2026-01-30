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
     * @var string the view path for this controller
     */
    public $viewPath = '@vendor/wazemaki/yii2-settings-handler/src/views';
    
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
            if (($def['inputType'] ?? '') !== 'delimiter') {
                $keys[] = $key;
            }
        }
        
        $model = new DynamicModel($keys);
        
        // Build dynamic model with validation rules
        foreach ($definitions as $key => $def) {
            if ($def['inputType'] === 'delimiter') {
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
            
            $successCount = 0;
            $resetCount = 0;
            
            foreach ($definitions as $key => $def) {
                if ($def['inputType'] === 'delimiter') {
                    continue;
                }
                
                // If reset checkbox is checked, delete from database
                if (isset($resetData[$key]) && $resetData[$key]) {
                    if ($settings->delete($key)) {
                        $resetCount++;
                    }
                    continue;
                }
                
                if (isset($settingsData[$key])) {
                    $value = $settingsData[$key];
                    
                    // If empty and emptyMeansDefault = true, delete (use default)
                    if ($value === '' && ($def['emptyMeansDefault'] ?? false)) {
                        if ($settings->delete($key)) {
                            $resetCount++;
                        }
                        continue;
                    }
                    
                    // Convert boolean type
                    if (($def['dataType'] ?? '') === 'boolean') {
                        $value = (bool)$value;
                    }
                    
                    if ($settings->set($key, $value)) {
                        $successCount++;
                    }
                }
            }
            
            $message = [];
            if ($successCount > 0) {
                $message[] = "$successCount setting(s) saved";
            }
            if ($resetCount > 0) {
                $message[] = "$resetCount reset to default";
            }
            
            Yii::$app->session->setFlash('success', implode(', ', $message) ?: 'No changes.');
            return $this->refresh();
        }

        // Load current values
        $model->load($settings->getAllFromDb(), '');

        return $this->render('index', [
            'model' => $model,
            'definitions' => $definitions,
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
        Yii::$app->session->setFlash('success', 'Cache cleared successfully.');
        return $this->redirect(['index']);
    }
}
