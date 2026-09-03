<?php

use yii\helpers\Html;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $form yii\bootstrap5\ActiveForm */
/* @var $model yii\base\DynamicModel */
/* @var $key string */
/* @var $def array */
/* @var $model yii\base\DynamicModel */
/* @var $form yii\bootstrap5\ActiveForm */

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
} elseif (is_array($defaultValueShow)) {
    $defaultValueShow = implode(', ', $defaultValueShow);
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

        $viewPath = $def['customViewPath'] ?? null;
        
        if (!empty($viewPath)) {
            try {
                echo '<div class="custom-view-container">';
                echo $this->render($viewPath, [
                    'model' => $model,
                    'def' => $def,
                ]);
                echo '</div>';
            } catch (\Throwable $e) {
                echo '<div class="alert alert-danger mt-3" role="alert">';
                echo '<strong>View hiba:</strong> ' . Html::encode($e->getMessage());
                echo '</div>';
            }
        }
        break;
        
    case 'checkbox':
        echo '<label class="form-label">' . Html::encode($def['label'] ?? '') . '</label>';
        echo Html::tag(
            'label',
            Html::activeCheckbox($model, $key, [
                'class' => 'toggle-switch-input setting-input',
                'label' => false,
                'autocomplete' => 'off',
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
            'autocomplete' => 'off',
        ])->hint($hint);
        break;
        
    case 'number':
        echo $field->input('number', [
            'placeholder' => $placeholder,
            'class' => 'form-control setting-input',
            'autocomplete' => 'off',
        ])->hint($hint);
        break;
        
    case 'password':
        echo '<div class="password-wrapper position-relative">';
        echo $field->passwordInput([
            'class' => 'form-control setting-input password-input',
            'autocomplete' => 'new-password',
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

        $multiple = $def['multiple'] ?? false;

        if ($multiple) {
            // Stored value comes from the DB as a raw JSON string; decode it
            // to an array so Select2 can pre-select the current values.
            $currentValue = $model->$key;
            if (is_string($currentValue) && $currentValue !== '') {
                $decoded = json_decode($currentValue, true);
                $model->$key = is_array($decoded) ? $decoded : [];
            } elseif (!is_array($currentValue)) {
                $model->$key = [];
            }
        }

        echo $field->widget(Select2::class, [
            'data' => $options,
            'theme' => Select2::THEME_BOOTSTRAP,
            'options' => [
                'placeholder' => 'Válassz...',
                'class' => 'setting-input',
                'autocomplete' => 'off',
                'multiple' => $multiple,
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
            'autocomplete' => 'off',
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
