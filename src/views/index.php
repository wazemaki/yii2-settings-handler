<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use wazemaki\settings\assets\SettingsAsset;

/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $definitions array */

SettingsAsset::register($this);

$this->title = 'Rendszerbeállítások';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="settings-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <?php foreach ($definitions as $key => $def): ?>
            <div class="col-12 mb-4">
                <?php
                // Skip delimiter rendering in non-tab mode (will be shown as HR)
                if (($def['inputType'] ?? '') === 'delimiter') {
                    echo '<hr><h5>' . Html::encode($def['label'] ?? '') . '</h5>';
                    continue;
                }
                ?>
                
                <?= $this->render('_input', [
                    'form' => $form,
                    'model' => $model,
                    'key' => $key,
                    'def' => $def,
                ]) ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="form-group mt-4">
        <?= Html::submitButton('Beállítások mentése', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Cache törlése', ['clear-cache'], ['class' => 'btn btn-warning ms-3']) ?>
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

    .password-wrapper {
        position: relative;
    }

    .password-toggle-icon {
        z-index: 10;
        user-select: none;
    }

    .password-toggle-icon:hover {
        opacity: 0.7;
    }
</style>

                        
                    case 'checkbox':
