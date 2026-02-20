# Yii2 Settings Handler

Database-backed settings manager with admin UI for Yii2 applications.

## Installation

```bash
composer require wazemaki/yii2-settings-handler
```

## Setup

### 1. Run Migration

```bash
cp vendor/wazemaki/yii2-settings-handler/migrations/*.php migrations/
php yii migrate
```

### 2. Copy Config Example

```bash
cp vendor/wazemaki/yii2-settings-handler/settings-config.php config/settings-config.php
```

### 3. Configure Component

Add to `config/common.php`:

```php
'components' => [
    'settings' => [
        'class' => 'wazemaki\settings\SettingsHandler',
        'definitions' => require(__DIR__ . '/settings-config.php'),
    ],
],

'controllerMap' => [
    'settings' => 'wazemaki\settings\controllers\SettingsController',
],
```

## Usage

### Admin UI

Navigate to: `/settings`

### In Code

```php
// Get value
$value = Yii::$app->settings->get('site_name');

// Set value
Yii::$app->settings->set('site_name', 'My Site');

// Reset to default
Yii::$app->settings->delete('site_name');

// Clear settings cache
Yii::$app->settings->deleteCache();
```

## Definition Options

See `settings-config.php` for examples.

| Option | Description |
|--------|-------------|
| `label` | Display label |
| `dataType` | `string`, `integer`, `boolean`, `float` |
| `inputType` | `text`, `textarea`, `checkbox`, `number`, `select`, `password`, `email`, `url`, `delimiter`, `custom_view` |
| `defaultValue` | Default value |
| `options` | Array or closure for select dropdowns |
| `hint` | Help text |
| `emptyMeansDefault` | Treat empty as default |

### Input Type: `custom_view`

The `custom_view` input type allows you to embed custom view files within the settings UI. This is useful for complex UI elements, admin panels, or custom content.

**Usage:**

```php
'custom_admin_panel' => [
    'inputType' => 'custom_view',
    'viewPath' => '@custom/view/path.php'
],
```

**View File Example (`@app/views/custom-admin-panel`):**

```php
<?php
/* @var $this yii\web\View */
/* @var $model yii\base\DynamicModel */
/* @var $def array */

use yii\helpers\Html;
?>

<div class="admin-panel">
    <h4>Custom Admin Panel</h4>
    <p>This is a custom view rendered within the settings UI.</p>
    
    <!-- You can access model values using getAttribute() -->
    <?= Html::tag('p', 'Current value: ' . Html::encode($model->getAttribute('custom_admin_panel'))) ?>
</div>
```

The view receives these variables:
- `$model`: The DynamicModel containing all setting values
- `$def`: The definition array for this setting
- `$this`: The view context (you can use `$this->render()` for nested views)

**Note:** The `view` input type stores the view path in the setting, so you can configure different views per environment or use aliases like `@app`, `@common`, etc.

## License

MIT
