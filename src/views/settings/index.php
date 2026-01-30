<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use kartik\select2\Select2;

/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $definitions array */

$this->title = 'System Settings';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="settings-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?php foreach ($definitions as $key => $def): ?>
            <div class="col-12 mb-4">
                <?php
                $inputType = $def['inputType'] ?? 'text';
                $isDefault = \Yii::$app->settings->isDefault($key);
                $defaultValue = $defaultValueShow = $def['defaultValue'] ?? null;
                $hasDefault = isset($def['defaultValue']);
                $emptyMeansDefault = $def['emptyMeansDefault'] ?? false;

                $field = $form->field($model, $key);
                $placeholder = $def['placeholder'] ?? null;

                // If emptyMeansDefault = true, show default in placeholder
                if (!$placeholder && $emptyMeansDefault && $hasDefault) {
                    $placeholder = "Default: $defaultValue";
                }

                $hint = '';
                if (isset($def['hint'])) {
                    $hint = '<small class="text-muted">' . Html::encode($def['hint']) . '</small>';
                }
                
                if ($inputType === 'checkbox') {
                    $defaultValueShow = $defaultValue ? 'YES' : 'NO';
                }
                
                if ($hasDefault) {
                    if ($emptyMeansDefault) {
                        $hint .= '<br><small class="text-muted">Empty value will use default: ' . Html::encode($defaultValueShow) . '</small>';
                    } else {
                        $hint .= '<br><small class="text-muted">Default value: ' . Html::encode($defaultValueShow) . '</small>';
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
                        echo $field->passwordInput([
                            'class' => 'form-control setting-input',
                        ])->hint($hint);
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
                                'placeholder' => 'Select...',
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
                    echo '<small><i class="fas fa-undo"></i> Reset to default (' . $defaultValueShow . ')</small>';
                    echo '</label>';
                }
                ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton('Save Settings', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Clear Cache', ['clear-cache'], ['class' => 'btn btn-warning ms-3']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<style>
    .reset-label {
        cursor: pointer;
        color: var(--bs-secondary-color);
        transition: color 0.2s ease;
        font-size: .8rem;
    }

    .reset-label.is-checked {
        color: var(--bs-body-color, #fff);
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Reset checkbox behavior
        document.querySelectorAll('.reset-checkbox').forEach(resetCheckbox => {
            const label = document.querySelector('label[for="' + resetCheckbox.id + '"]');

            resetCheckbox.addEventListener('change', function() {
                const key = this.getAttribute('id').replace('settings-reset_', '');
                const type = this.getAttribute('data-setting-type');
                const input = document.getElementById('dynamicmodel-' + key);
                const defaultValue = this.getAttribute('data-default');

                // Update label visual state
                if (label) {
                    label.classList.toggle('is-checked', this.checked);
                }

                if (this.checked) {
                    // Store current value before resetting
                    if (type === 'checkbox') {
                        this.setAttribute('data-previous-checked', input.checked);
                    } else {
                        this.setAttribute('data-previous-value', input.value);
                    }

                    // Reset to default
                    if (type === 'checkbox') {
                        input.checked = defaultValue === '1';
                    } else {
                        input.value = '';
                    }
                } else {
                    // Uncheck - restore previous value
                    if (type === 'checkbox') {
                        input.checked = this.getAttribute('data-previous-checked') === 'true';
                    } else {
                        input.value = this.getAttribute('data-previous-value') || '';
                    }
                }
            });
        });

        // Uncheck reset when input changes
        document.querySelectorAll('.setting-input').forEach(input => {
            input.addEventListener('change', function() {
                const key = this.getAttribute('id').replace('dynamicmodel-', '');
                const resetCheckbox = document.getElementById('settings-reset_' + key);
                let label;

                if (resetCheckbox) {
                    resetCheckbox.checked = false;
                    label = document.querySelector('label[for="' + resetCheckbox.id + '"]');
                }
                if (label) {
                    label.classList.remove('is-checked');
                }
            });

            // For text inputs, also handle 'input' event
            if (input.type === 'text' || input.type === 'number' || input.type === 'password' || input.tagName === 'TEXTAREA') {
                input.addEventListener('input', function() {
                    const key = this.getAttribute('id').replace('dynamicmodel-', '');
                    const resetCheckbox = document.getElementById('settings-reset_' + key);
                    let label;

                    if (resetCheckbox) {
                        resetCheckbox.checked = false;
                        label = document.querySelector('label[for="' + resetCheckbox.id + '"]');
                    }
                    if (label) {
                        label.classList.remove('is-checked');
                    }
                });
            }
        });
    });
</script>
