<?php

namespace wazemaki\settings\assets;

use yii\web\AssetBundle;

class SettingsAsset extends AssetBundle
{
    public $basePath = '@vendor/wazemaki/yii2-settings-handler/web';
    public $baseUrl = '@web';
    
    public $js = [
        'js/settings.js',
    ];
    
    public $css = [];
    
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
    ];
}
