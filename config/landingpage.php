<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Company Information
    |--------------------------------------------------------------------------
    |
    | These values are used throughout the landing page to display
    | company information dynamically.
    |
    */

    'company_name' => env('COMPANY_NAME', 'PT. Barakka Karya Mandiri'),
    'company_short' => env('COMPANY_SHORT', 'BKM'),
    'company_description' => env('COMPANY_DESCRIPTION', 'Jasa Pengurusan Transportasi'),
    'company_tagline' => env('COMPANY_TAGLINE', 'Solusi Transportasi Terpadu'),

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'address' => [
            'street' => env('COMPANY_ADDRESS_STREET', 'Jl. Raya No. 123'),
            'city' => env('COMPANY_ADDRESS_CITY', 'Balikpapan'),
            'province' => env('COMPANY_ADDRESS_PROVINCE', 'Kalimantan Timur'),
            'postal_code' => env('COMPANY_ADDRESS_POSTAL', '76112'),
            'country' => env('COMPANY_ADDRESS_COUNTRY', 'Indonesia'),
        ],
        'phone' => [
            'primary' => env('COMPANY_PHONE_PRIMARY', '+62 542 123 4567'),
            'secondary' => env('COMPANY_PHONE_SECONDARY', '+62 812 3456 7890'),
        ],
        'email' => [
            'info' => env('COMPANY_EMAIL_INFO', 'info@bkm-transport.co.id'),
            'support' => env('COMPANY_EMAIL_SUPPORT', 'support@bkm-transport.co.id'),
        ],
        'working_hours' => [
            'weekdays' => env('COMPANY_HOURS_WEEKDAYS', '08:00 - 17:00'),
            'saturday' => env('COMPANY_HOURS_SATURDAY', '08:00 - 12:00'),
            'sunday' => env('COMPANY_HOURS_SUNDAY', 'Tutup'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Developer Information
    |--------------------------------------------------------------------------
    */

    'developer' => [
        'name' => env('DEVELOPER_NAME', 'Denis Djodian Ardika'),
        'company' => env('DEVELOPER_COMPANY', 'Artelia.dev'),
        'website' => env('DEVELOPER_WEBSITE', 'https://arteliadev.cloud'),
        'github' => env('DEVELOPER_GITHUB', 'https://github.com/denis156'),
        'role' => env('DEVELOPER_ROLE', 'Lead Developer & Team Leader'),
    ],

    /*
    |--------------------------------------------------------------------------
    | System Features
    |--------------------------------------------------------------------------
    */

    'features' => [
        'multi_role_count' => env('SYSTEM_ROLES_COUNT', 7),
        'real_time_tracking' => env('FEATURE_REAL_TIME_TRACKING', true),
        'digital_documents' => env('FEATURE_DIGITAL_DOCUMENTS', true),
        'mobile_support' => env('FEATURE_MOBILE_SUPPORT', true),
        'reporting_analytics' => env('FEATURE_REPORTING_ANALYTICS', true),
        'automated_workflow' => env('FEATURE_AUTOMATED_WORKFLOW', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    'stats' => [
        'efficiency_improvement' => env('STAT_EFFICIENCY_IMPROVEMENT', '70%'),
        'data_accuracy' => env('STAT_DATA_ACCURACY', '99%'),
        'uptime' => env('STAT_UPTIME', '24/7'),
        'paperless_percentage' => env('STAT_PAPERLESS_PERCENTAGE', '80%'),
        'clients_count' => env('STAT_CLIENTS_COUNT', '50+'),
        'documents_processed' => env('STAT_DOCUMENTS_PROCESSED', '1000+'),
        'years_experience' => env('STAT_YEARS_EXPERIENCE', '10+'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Social Media Links
    |--------------------------------------------------------------------------
    */

    'social' => [
        'facebook' => env('SOCIAL_FACEBOOK', ''),
        'twitter' => env('SOCIAL_TWITTER', ''),
        'linkedin' => env('SOCIAL_LINKEDIN', ''),
        'instagram' => env('SOCIAL_INSTAGRAM', ''),
        'youtube' => env('SOCIAL_YOUTUBE', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    */

    'seo' => [
        'keywords' => env('SEO_KEYWORDS', 'delivery tracking, sistem transportasi, manajemen, real-time tracking, digital surat jalan'),
        'author' => env('SEO_AUTHOR', 'Denis Djodian Ardika - Artelia.dev'),
        'og_image' => env('SEO_OG_IMAGE', '/images/og-delivery-tracking.jpg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    */

    'theme' => [
        'default' => env('THEME_DEFAULT', 'corporate'),
        'dark' => env('THEME_DARK', 'business'),
        'available_themes' => [
            'corporate',
            'business',
            'cupcake',
            'bumblebee',
            'emerald',
            'synthwave',
            'retro',
            'cyberpunk',
            'valentine',
            'halloween',
            'garden',
            'forest',
            'aqua',
            'lofi',
            'pastel',
            'fantasy',
            'wireframe',
            'black',
            'luxury',
            'dracula',
            'cmyk',
            'autumn',
            'acid',
            'lemonade',
            'night',
            'coffee',
            'winter',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Page Settings
    |--------------------------------------------------------------------------
    */

    'page' => [
        'title' => env('PAGE_TITLE', 'Delivery & Tracking Truck System'),
        'description' => env('PAGE_DESCRIPTION', 'Solusi manajemen transportasi terpadu dengan real-time tracking dan workflow otomatis'),
        'favicon' => env('PAGE_FAVICON', '/favicon.ico'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics & Tracking
    |--------------------------------------------------------------------------
    */

    'analytics' => [
        'google_tag_id' => env('GOOGLE_TAG_ID', ''),
        'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID', ''),
        'hotjar_id' => env('HOTJAR_ID', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | API Keys & External Services
    |--------------------------------------------------------------------------
    */

    'services' => [
        'maps' => [
            'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
            'mapbox_access_token' => env('MAPBOX_ACCESS_TOKEN', ''),
        ],
        'email' => [
            'mailgun_domain' => env('MAILGUN_DOMAIN', ''),
            'mailgun_secret' => env('MAILGUN_SECRET', ''),
            'ses_key' => env('AWS_SES_KEY', ''),
            'ses_secret' => env('AWS_SES_SECRET', ''),
        ],
        'storage' => [
            's3_bucket' => env('AWS_S3_BUCKET', ''),
            'cloudinary_cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Form Settings
    |--------------------------------------------------------------------------
    */

    'contact_form' => [
        'enabled' => env('CONTACT_FORM_ENABLED', true),
        'send_email' => env('CONTACT_FORM_SEND_EMAIL', true),
        'save_to_database' => env('CONTACT_FORM_SAVE_TO_DB', true),
        'auto_reply' => env('CONTACT_FORM_AUTO_REPLY', true),
        'notification_emails' => env('CONTACT_FORM_NOTIFICATION_EMAILS', 'info@bkm-transport.co.id'),
        'rate_limit' => env('CONTACT_FORM_RATE_LIMIT', 5), // per hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Types for Contact Form
    |--------------------------------------------------------------------------
    */

    'service_types' => [
        'implementation' => 'Implementasi Sistem Baru',
        'consultation' => 'Konsultasi & Demo',
        'integration' => 'Integrasi Sistem Existing',
        'support' => 'Support & Maintenance',
        'training' => 'Pelatihan & Workshop',
        'other' => 'Lainnya',
    ],

    /*
    |--------------------------------------------------------------------------
    | Feature Flags
    |--------------------------------------------------------------------------
    */

    'features_flags' => [
        'show_developer_credits' => env('SHOW_DEVELOPER_CREDITS', true),
        'enable_contact_form' => env('ENABLE_CONTACT_FORM', true),
        'enable_scroll_animations' => env('ENABLE_SCROLL_ANIMATIONS', true),
        'enable_floating_action_button' => env('ENABLE_FLOATING_ACTION_BUTTON', true),
        'enable_navbar_auto_hide' => env('ENABLE_NAVBAR_AUTO_HIDE', true),
        'show_statistics' => env('SHOW_STATISTICS', true),
        'show_testimonials' => env('SHOW_TESTIMONIALS', false),
        'show_pricing' => env('SHOW_PRICING', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'ttl' => env('LANDING_PAGE_CACHE_TTL', 3600), // 1 hour
        'enabled' => env('LANDING_PAGE_CACHE_ENABLED', true),
    ],
];
