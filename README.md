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
| `inputType` | `text`, `textarea`, `checkbox`, `number`, `select`, `password`, `email`, `url`, `delimiter` |
| `defaultValue` | Default value |
| `options` | Array or closure for select dropdowns |
| `hint` | Help text |
| `emptyMeansDefault` | Treat empty as default |

## License

MIT
