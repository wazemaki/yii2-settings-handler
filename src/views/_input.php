<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model yii\base\DynamicModel */
/* @var $key string */
/* @var $def array */

$inputType = $def['inputType'] ?? 'text';
$isDefault = \Yii::$app->settings->isDefault($key);
$defaultValue = $defaultValueShow = $def['defaultValue'] ?? null;
$hasDefault = isset($def['defaultValue']);
$emptyMeansDefault = $def['emptyMeansDefault'] ?? false;

$field = $form->field($model, $key);
$placeholder = $def['placeholder'] ?? null;

// If emptyMeansDefault = true, show default in placeholder
if (!$placeholder && $emptyMeansDefault && $hasDefault) {
    $placeholder = "Alapértelmezett: $defaultValue";
}

$hint = '';
if (isset($def['hint'])) {
    $hint = '<small class="text-muted">' . $def['hint'] . '</small>';
}

if ($inputType === 'checkbox') {
    $defaultValueShow = $defaultValue ? 'YES' : 'NO';
}

if ($hasDefault) {
    if ($emptyMeansDefault) {
        $hint .= '<br><small class="text-muted">Üres érték esetén az alapértelmezett lesz használva: ' . Html::encode($defaultValueShow) . '</small>';
    } else {
        $hint .= '<br><small class="text-muted">Alapértelmezett érték: ' . Html::encode($defaultValueShow) . '</small>';
    }
}

switch ($inputType) {
    case 'delimiter':
        echo '<hr><h5>' . Html::encode($def['label'] ?? '') . '</h5>';
        break;
        
    case 'checkbox':
        echo '<label class="form-label">' . Html::encode($def['label'] ?? '') . '</label>';
        echo Html::tag(
            'label',
            Html::activeCheckbox($model, $key, [
                'class' => 'toggle-switch-input setting-input',
                'label' => false,
            ]) . Html::tag('span', '', ['class' => 'toggle-switch-slider']),
            ['class' => 'toggle-switch d-block mb-2']
        );

        if ($hint) {
            echo '<div class="form-text">' . $hint . '</div>';
        }
        break;
        
    case 'textarea':
        echo $field->textarea([
            'rows' => 4,
            'placeholder' => $placeholder,
            'class' => 'form-control setting-input',
        ])->hint($hint);
        break;
        
    case 'number':
        echo $field->input('number', [
            'placeholder' => $placeholder,
            'class' => 'form-control setting-input',
        ])->hint($hint);
        break;
        
    case 'password':
        echo '<div class="password-wrapper position-relative">';
        echo $field->passwordInput([
            'class' => 'form-control setting-input password-input',
        ])->hint($hint);
        echo '<span class="password-toggle-icon position-absolute" style="right: 10px; top: 8px; cursor: pointer;">';
        echo '<i class="fas fa-eye"></i>';
        echo '</span>';
        echo '</div>';
        break;
        
    case 'select':
        $rawOptions = $def['options'] ?? [];

        if (is_callable($rawOptions)) {
            $options = call_user_func($rawOptions);
        } else {
            $options = $rawOptions;
        }

        echo $field->widget(Select2::class, [
            'data' => $options,
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => [
                'placeholder' => 'Válassz...',
                'class' => 'setting-input',
            ],
            'pluginOptions' => [
                'allowClear' => true
            ],
        ])->hint($hint);
        break;
        
    default: // text, email, etc.
        echo $field->input($inputType, [
            'placeholder' => $placeholder,
            'class' => 'form-control setting-input',
        ])->hint($hint);
}

// Reset to default checkbox
if ($hasDefault && !$isDefault) {
    echo Html::checkbox("settings-reset[$key]", false, [
        'class' => 'form-check-input reset-checkbox',
        'id' => "settings-reset_$key",
        'data-default' => $defaultValue,
        'data-setting-type' => $def['inputType'] ?? 'text',
        'style' => 'display: none;'
    ]);
    echo '<label class="form-check-label reset-label" for="settings-reset_' . $key . '">';
    echo '<small><i class="fas fa-undo"></i> Visszaállítás alapértelmezettre (' . $defaultValueShow . ')</small>';
    echo '</label>';
}
