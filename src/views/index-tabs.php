<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap5\ActiveForm;
use wazemaki\settings\assets\SettingsAsset;

/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $definitions array */
/* @var $groupedByDelimiter array */
/* @var $activeTab string|null */

SettingsAsset::register($this);

$this->title = 'Rendszerbeállítások';
$this->params['breadcrumbs'][] = $this->title;

$customView = $definitions[$activeTab]['customViewPath'] ?? null;
?>

<div class="settings-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <!-- Tab Navigation -->
    <?php if (!empty($groupedByDelimiter)): ?>
        <nav class="nav nav-tabs mb-4" role="tablist">
            <?php foreach ($groupedByDelimiter as $tabKey => $tabSettings): ?>
                <?php if ($tabKey === '__default__'): ?>
                    <!-- Skip rendering tab for default group if it's empty -->
                    <?php if (!empty($tabSettings)): ?>
                        <a class="nav-link <?= $activeTab === '__default__' ? 'active' : '' ?>" 
                           href="<?= Url::current(['tab' => '__default__']) ?>"
                           role="tab">
                            Egyéb
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <?php $tabLabel = $definitions[$tabKey]['label'] ?? $tabKey; ?>
                    <a class="nav-link <?= $activeTab === $tabKey ? 'active' : '' ?>" 
                       href="<?= Url::current(['tab' => $tabKey]) ?>"
                       role="tab">
                        <?= Html::encode($tabLabel) ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    <?php endif; ?>

    <?php if ($customView): ?>
        <div class="custom-view-container mb-4">
            <?= $this->render($customView, [
                'model' => $model,
                'definitions' => $definitions,
                'groupedByDelimiter' => $groupedByDelimiter,
                'activeTab' => $activeTab,
            ]) ?>
        </div>
    <?php else: ?>

        <?php $form = ActiveForm::begin(); ?>

        <div class="row">
            <?php foreach ($definitions as $key => $def): ?>
                <?php
                $inputType = $def['inputType'] ?? 'text';
                
                // Determine which tab this field belongs to
                $fieldTabKey = '__default__';
                foreach ($groupedByDelimiter as $tabKey => $tabSettings) {
                    if (in_array($key, $tabSettings)) {
                        $fieldTabKey = $tabKey;
                        break;
                    }
                }

                // Skip rendering if this is not the active tab
                if ($fieldTabKey !== $activeTab) {
                    continue;
                }

                // Skip delimiter rendering in tab mode
                if ($inputType === 'delimiter') {
                    continue;
                }
                ?>

                <div class="col-12 mb-4">
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
    <?php endif; ?>
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

    /* Tab styles */
    .nav-tabs {
        border-bottom: 1px solid var(--bs-secondary-color);
    }

    .nav-tabs .nav-link {
        color: var(--bs-secondary-color);
        border: none;
        border-bottom: 3px solid transparent;
        padding: 0.5rem 1rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }

    .nav-tabs .nav-link.active {
        background-color: transparent;
        border-color: var(--bs-primary);
        color: var(--bs-primary);
    }
</style>
