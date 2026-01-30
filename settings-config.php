<?php
/**
 * Settings Handler - Configuration Example
 * 
 * This file demonstrates all available input types and configuration options.
 * Include this in your component configuration:
 * 
 * 'components' => [
 *     'settings' => [
 *         'class' => 'wazemaki\settings\SettingsHandler',
 *         'definitions' => require(__DIR__ . '/settings-config.php'),
 *     ],
 * ],
 */

return [
    // =========================================================================
    // DELIMITERS (Section Headers)
    // =========================================================================
    
    'section_general' => [
        'label' => 'General Settings',
        'inputType' => 'delimiter',
    ],
    
    // =========================================================================
    // TEXT INPUT
    // =========================================================================
    
    'site_name' => [
        'label' => 'Site Name',
        'dataType' => 'string',
        'inputType' => 'text',
        'defaultValue' => 'My Website',
        'hint' => 'The name of your website displayed in the browser title',
    ],
    
    'company_name' => [
        'label' => 'Company Name',
        'dataType' => 'string',
        'inputType' => 'text',
        'defaultValue' => 'ACME Corporation',
        'placeholder' => 'Enter your company name',
    ],
    
    // =========================================================================
    // TEXTAREA
    // =========================================================================
    
    'welcome_message' => [
        'label' => 'Welcome Message',
        'dataType' => 'string',
        'inputType' => 'textarea',
        'defaultValue' => "Welcome to our platform!\nWe're glad you're here.",
        'hint' => 'Displayed on the home page. Supports multiple lines.',
    ],
    
    'terms_of_service' => [
        'label' => 'Terms of Service (excerpt)',
        'dataType' => 'string',
        'inputType' => 'textarea',
        'hint' => 'Brief terms displayed during registration',
    ],
    
    // =========================================================================
    // NUMBER INPUT
    // =========================================================================
    
    'items_per_page' => [
        'label' => 'Items Per Page',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 25,
        'emptyMeansDefault' => true,
        'hint' => 'Number of items to display per page in lists',
    ],
    
    'session_timeout' => [
        'label' => 'Session Timeout (minutes)',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 30,
        'emptyMeansDefault' => true,
    ],
    
    'max_upload_size' => [
        'label' => 'Max Upload Size (MB)',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 10,
    ],
    
    // =========================================================================
    // FLOAT/DECIMAL
    // =========================================================================
    
    'tax_rate' => [
        'label' => 'Tax Rate (%)',
        'dataType' => 'float',
        'inputType' => 'number',
        'defaultValue' => 0.0,
        'emptyMeansDefault' => true,
        'hint' => 'Applied to all transactions. Use 0 for tax-exempt.',
    ],
    
    'service_fee_percentage' => [
        'label' => 'Service Fee (%)',
        'dataType' => 'float',
        'inputType' => 'number',
        'defaultValue' => 2.5,
    ],
    
    // =========================================================================
    // CHECKBOX (Boolean with Toggle Switch)
    // =========================================================================
    
    'maintenance_mode' => [
        'label' => 'Maintenance Mode',
        'dataType' => 'boolean',
        'inputType' => 'checkbox',
        'defaultValue' => false,
        'hint' => 'When enabled, only administrators can access the site',
    ],
    
    'enable_registration' => [
        'label' => 'Enable User Registration',
        'dataType' => 'boolean',
        'inputType' => 'checkbox',
        'defaultValue' => true,
        'hint' => 'Allow new users to register',
    ],
    
    'enable_notifications' => [
        'label' => 'Enable Email Notifications',
        'dataType' => 'boolean',
        'inputType' => 'checkbox',
        'defaultValue' => true,
    ],
    
    'debug_mode' => [
        'label' => 'Debug Mode',
        'dataType' => 'boolean',
        'inputType' => 'checkbox',
        'defaultValue' => false,
        'hint' => '⚠️ Only enable in development environments',
    ],
    
    // =========================================================================
    // SELECT DROPDOWN (Static Options)
    // =========================================================================
    
    'theme' => [
        'label' => 'Site Theme',
        'dataType' => 'string',
        'inputType' => 'select',
        'options' => [
            'light' => 'Light Theme',
            'dark' => 'Dark Theme',
            'auto' => 'Auto (System Preference)',
        ],
        'defaultValue' => 'light',
        'hint' => 'Default theme for all users',
    ],
    
    'default_language' => [
        'label' => 'Default Language',
        'dataType' => 'string',
        'inputType' => 'select',
        'options' => [
            'en' => 'English',
            'de' => 'Deutsch',
            'fr' => 'Français',
            'es' => 'Español',
            'hu' => 'Magyar',
        ],
        'defaultValue' => 'en',
    ],
    
    'timezone' => [
        'label' => 'Server Timezone',
        'dataType' => 'string',
        'inputType' => 'select',
        'options' => [
            'UTC' => 'UTC',
            'Europe/London' => 'Europe/London (GMT)',
            'Europe/Budapest' => 'Europe/Budapest (CET)',
            'America/New_York' => 'America/New York (EST)',
            'America/Los_Angeles' => 'America/Los Angeles (PST)',
            'Asia/Tokyo' => 'Asia/Tokyo (JST)',
        ],
        'defaultValue' => 'UTC',
    ],
    
    // =========================================================================
    // SELECT DROPDOWN (Dynamic Options via Closure)
    // =========================================================================
    
    'default_role' => [
        'label' => 'Default User Role',
        'dataType' => 'string',
        'inputType' => 'select',
        'options' => function() {
            // This closure runs every time the settings page loads
            // You can fetch from database, API, etc.
            return [
                'user' => 'Regular User',
                'premium' => 'Premium User',
                'guest' => 'Guest',
            ];
        },
        'defaultValue' => 'user',
        'hint' => 'Role automatically assigned to new users',
    ],
    
    // =========================================================================
    // PASSWORD INPUT
    // =========================================================================
    
    'section_api' => [
        'label' => 'API & Integration Settings',
        'inputType' => 'delimiter',
    ],
    
    'api_key' => [
        'label' => 'API Key',
        'dataType' => 'string',
        'inputType' => 'password',
        'hint' => 'Secret API key for external services',
    ],
    
    'webhook_secret' => [
        'label' => 'Webhook Secret',
        'dataType' => 'string',
        'inputType' => 'password',
        'hint' => 'Used to validate incoming webhook requests',
    ],
    
    // =========================================================================
    // EMAIL INPUT
    // =========================================================================
    
    'section_email' => [
        'label' => 'Email Configuration',
        'inputType' => 'delimiter',
    ],
    
    'admin_email' => [
        'label' => 'Admin Email Address',
        'dataType' => 'string',
        'inputType' => 'email',
        'defaultValue' => 'admin@example.com',
        'hint' => 'Primary contact email for system notifications',
    ],
    
    'support_email' => [
        'label' => 'Support Email',
        'dataType' => 'string',
        'inputType' => 'email',
        'defaultValue' => 'support@example.com',
        'hint' => 'Email displayed to users for support inquiries',
    ],
    
    'smtp_host' => [
        'label' => 'SMTP Host',
        'dataType' => 'string',
        'inputType' => 'text',
        'defaultValue' => 'smtp.gmail.com',
        'emptyMeansDefault' => true,
    ],
    
    'smtp_port' => [
        'label' => 'SMTP Port',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 587,
        'emptyMeansDefault' => true,
    ],
    
    'smtp_username' => [
        'label' => 'SMTP Username',
        'dataType' => 'string',
        'inputType' => 'text',
        'hint' => 'Usually your email address',
    ],
    
    'smtp_password' => [
        'label' => 'SMTP Password',
        'dataType' => 'string',
        'inputType' => 'password',
        'hint' => 'Your email password or app-specific password',
    ],
    
    'smtp_encryption' => [
        'label' => 'SMTP Encryption',
        'dataType' => 'string',
        'inputType' => 'select',
        'options' => [
            'tls' => 'TLS',
            'ssl' => 'SSL',
            'none' => 'None',
        ],
        'defaultValue' => 'tls',
    ],
    
    // =========================================================================
    // ADVANCED OPTIONS
    // =========================================================================
    
    'section_advanced' => [
        'label' => 'Advanced Settings',
        'inputType' => 'delimiter',
    ],
    
    'cache_duration' => [
        'label' => 'Cache Duration (seconds)',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 3600,
        'emptyMeansDefault' => true,
        'hint' => '3600 = 1 hour, 86400 = 1 day',
    ],
    
    'enable_caching' => [
        'label' => 'Enable Application Cache',
        'dataType' => 'boolean',
        'inputType' => 'checkbox',
        'defaultValue' => true,
        'hint' => 'Improves performance but may require manual cache clearing',
    ],
    
    'log_level' => [
        'label' => 'Application Log Level',
        'dataType' => 'string',
        'inputType' => 'select',
        'options' => [
            'error' => 'Error Only',
            'warning' => 'Warning & Error',
            'info' => 'Info, Warning & Error',
            'debug' => 'All Messages (Debug)',
        ],
        'defaultValue' => 'warning',
    ],
    
    'api_rate_limit' => [
        'label' => 'API Rate Limit (requests/minute)',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 60,
        'emptyMeansDefault' => true,
        'hint' => 'Maximum API requests per minute per user',
    ],
    
    // =========================================================================
    // FEATURES WITH emptyMeansDefault
    // =========================================================================
    
    'section_features' => [
        'label' => 'Feature Flags',
        'inputType' => 'delimiter',
    ],
    
    'max_login_attempts' => [
        'label' => 'Max Login Attempts',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 5,
        'emptyMeansDefault' => true,
        'hint' => 'Empty = default (5). User locked after this many failed attempts.',
    ],
    
    'password_min_length' => [
        'label' => 'Minimum Password Length',
        'dataType' => 'integer',
        'inputType' => 'number',
        'defaultValue' => 8,
        'emptyMeansDefault' => true,
    ],
    
    // =========================================================================
    // TEXTAREA FOR JSON/CODE
    // =========================================================================
    
    'custom_css' => [
        'label' => 'Custom CSS',
        'dataType' => 'string',
        'inputType' => 'textarea',
        'hint' => 'Custom CSS injected into all pages',
    ],
    
    'custom_javascript' => [
        'label' => 'Custom JavaScript',
        'dataType' => 'string',
        'inputType' => 'textarea',
        'hint' => 'Custom JS injected before closing </body> tag',
    ],
    
    // =========================================================================
    // URL INPUT
    // =========================================================================
    
    'section_social' => [
        'label' => 'Social Media & External Links',
        'inputType' => 'delimiter',
    ],
    
    'facebook_url' => [
        'label' => 'Facebook Page URL',
        'dataType' => 'string',
        'inputType' => 'url',
        'placeholder' => 'https://facebook.com/yourpage',
    ],
    
    'twitter_url' => [
        'label' => 'Twitter/X Profile URL',
        'dataType' => 'string',
        'inputType' => 'url',
        'placeholder' => 'https://twitter.com/yourhandle',
    ],
    
    'linkedin_url' => [
        'label' => 'LinkedIn Company URL',
        'dataType' => 'string',
        'inputType' => 'url',
    ],
    
    // =========================================================================
    // CUSTOM VALIDATION RULES
    // =========================================================================
    
    'contact_phone' => [
        'label' => 'Contact Phone Number',
        'dataType' => 'string',
        'inputType' => 'text',
        'rules' => [
            ['string', 'max' => 20],
        ],
        'hint' => 'International format recommended: +1-234-567-8900',
    ],
    
    // =========================================================================
    // PLACEHOLDER DEMONSTRATION
    // =========================================================================
    
    'analytics_id' => [
        'label' => 'Google Analytics Tracking ID',
        'dataType' => 'string',
        'inputType' => 'text',
        'placeholder' => 'G-XXXXXXXXXX',
        'hint' => 'Leave empty to disable tracking',
    ],
];
