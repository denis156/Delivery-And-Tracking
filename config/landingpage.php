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
    'company_tagline' => env('COMPANY_TAGLINE', 'Solusi Transportasi Terpadu Sulawesi Tenggara'),

    /*
    |--------------------------------------------------------------------------
    | Legal Information
    |--------------------------------------------------------------------------
    */

    'legal' => [
        'registered_name' => env('COMPANY_REGISTERED_NAME', 'PT. Barakka Karya Mandiri'),
        'entity_type' => env('COMPANY_ENTITY_TYPE', 'Limited Liability Company'),
        'business_number' => env('COMPANY_BUSINESS_NUMBER', '1491777'),
        'registration_authority' => env('COMPANY_REG_AUTHORITY', 'Ministry of Law and Human Rights of the Republic of Indonesia'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    'contact' => [
        'address' => [
            'registered' => [
                'street' => env('COMPANY_REG_ADDRESS_STREET', 'Kantor Pelindo, 2nd Floor, Jl. Konggoasa No. 2'),
                'city' => env('COMPANY_REG_ADDRESS_CITY', 'Kendari'),
                'province' => env('COMPANY_REG_ADDRESS_PROVINCE', 'Sulawesi Tenggara'),
                'postal_code' => env('COMPANY_REG_ADDRESS_POSTAL', '93111'),
                'country' => env('COMPANY_REG_ADDRESS_COUNTRY', 'Indonesia'),
            ],
            'operational' => [
                'street' => env('COMPANY_OP_ADDRESS_STREET', 'Pelabuhan Nusantara, Kandai'),
                'district' => env('COMPANY_OP_ADDRESS_DISTRICT', 'Kec. Kendari'),
                'city' => env('COMPANY_OP_ADDRESS_CITY', 'Kota Kendari'),
                'province' => env('COMPANY_OP_ADDRESS_PROVINCE', 'Sulawesi Tenggara'),
                'postal_code' => env('COMPANY_OP_ADDRESS_POSTAL', '93232'),
                'country' => env('COMPANY_OP_ADDRESS_COUNTRY', 'Indonesia'),
            ],
        ],
        'phone' => [
            'primary' => env('COMPANY_PHONE_PRIMARY', '+62 401 123 456'),
            'secondary' => env('COMPANY_PHONE_SECONDARY', '+62 812 3456 7890'),
            'whatsapp' => env('COMPANY_WHATSAPP', '+62 812 3456 7890'),
        ],
        'email' => [
            'info' => env('COMPANY_EMAIL_INFO', 'info@bkm-transport.co.id'),
            'support' => env('COMPANY_EMAIL_SUPPORT', 'support@bkm-transport.co.id'),
            'operations' => env('COMPANY_EMAIL_OPERATIONS', 'ops@bkm-transport.co.id'),
        ],
        'working_hours' => [
            'weekdays' => env('COMPANY_HOURS_WEEKDAYS', '08:00 - 17:00 WITA'),
            'saturday' => env('COMPANY_HOURS_SATURDAY', '08:00 - 12:00 WITA'),
            'sunday' => env('COMPANY_HOURS_SUNDAY', 'Tutup'),
            'timezone' => env('COMPANY_TIMEZONE', 'Asia/Makassar'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Areas & Services
    |--------------------------------------------------------------------------
    */

    'business' => [
        'primary_location' => env('BUSINESS_PRIMARY_LOCATION', 'Sulawesi Tenggara'),
        'service_areas' => [
            'kendari' => 'Kota Kendari',
            'konawe' => 'Kabupaten Konawe',
            'konawe_selatan' => 'Kabupaten Konawe Selatan',
            'konawe_utara' => 'Kabupaten Konawe Utara',
            'kolaka' => 'Kabupaten Kolaka',
            'kolaka_utara' => 'Kabupaten Kolaka Utara',
            'bombana' => 'Kabupaten Bombana',
            'wakatobi' => 'Kabupaten Wakatobi',
            'kolaka_timur' => 'Kabupaten Kolaka Timur',
            'konawe_kepulauan' => 'Kabupaten Konawe Kepulauan',
            'muna' => 'Kabupaten Muna',
            'muna_barat' => 'Kabupaten Muna Barat',
            'buton' => 'Kabupaten Buton',
            'buton_tengah' => 'Kabupaten Buton Tengah',
            'buton_utara' => 'Kabupaten Buton Utara',
            'buton_selatan' => 'Kabupaten Buton Selatan',
            'baubau' => 'Kota Baubau',
        ],
        'services' => [
            'delivery' => 'Layanan Pengiriman',
            'receiving' => 'Layanan Penerimaan',
            'stripping' => 'Layanan Bongkar Muat',
            'stuffing' => 'Layanan Muat Barang',
            'warehousing' => 'Layanan Pergudangan',
            'distribution' => 'Layanan Distribusi',
        ],
        'specializations' => [
            'port_services' => 'Jasa Kepelabuhanan',
            'freight_forwarding' => 'Freight Forwarding',
            'logistics_management' => 'Manajemen Logistik',
            'cargo_handling' => 'Penanganan Kargo',
            'documentation' => 'Pengurusan Dokumen',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Developer Information
    |--------------------------------------------------------------------------
    */

    'developer' => [
        'name' => env('DEVELOPER_NAME', 'Denis Djodian Ardika'),
        'company' => env('DEVELOPER_COMPANY', 'ArteliaDev'),
        'website' => env('DEVELOPER_WEBSITE', 'https://arteliadev.cloud'),
        'github' => env('DEVELOPER_GITHUB', 'https://github.com/denis156'),
        'role' => env('DEVELOPER_ROLE', 'Lead Developer & Team Leader'),
        'linkedin' => env('DEVELOPER_LINKEDIN', ''),
        'email' => env('DEVELOPER_EMAIL', 'denis@arteliadev.cloud'),
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
        'gps_tracking' => env('FEATURE_GPS_TRACKING', true),
        'multi_language' => env('FEATURE_MULTI_LANGUAGE', false),
        'offline_capability' => env('FEATURE_OFFLINE_CAPABILITY', true),
        'audit_trail' => env('FEATURE_AUDIT_TRAIL', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    'stats' => [
        'efficiency_improvement' => env('STAT_EFFICIENCY_IMPROVEMENT', '75%'),
        'data_accuracy' => env('STAT_DATA_ACCURACY', '99%'),
        'uptime' => env('STAT_UPTIME', '24/7'),
        'paperless_percentage' => env('STAT_PAPERLESS_PERCENTAGE', '85%'),
        'clients_served' => env('STAT_CLIENTS_SERVED', '25+'),
        'documents_processed' => env('STAT_DOCUMENTS_PROCESSED', '500+'),
        'years_experience' => env('STAT_YEARS_EXPERIENCE', '8+'),
        'fleet_managed' => env('STAT_FLEET_MANAGED', '15+'),
        'routes_covered' => env('STAT_ROUTES_COVERED', '50+'),
        'sultra_coverage' => env('STAT_SULTRA_COVERAGE', '17'),
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
        'whatsapp_business' => env('SOCIAL_WHATSAPP_BUSINESS', ''),
        'telegram' => env('SOCIAL_TELEGRAM', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | SEO Settings
    |--------------------------------------------------------------------------
    */

    'seo' => [
        'keywords' => env('SEO_KEYWORDS', 'delivery tracking sulawesi tenggara, sistem transportasi kendari, manajemen logistik sultra, real-time tracking, digital surat jalan, jasa transportasi kendari, freight forwarding sulawesi tenggara'),
        'author' => env('SEO_AUTHOR', 'Denis Djodian Ardika - ArteliaDev'),
        'og_image' => env('SEO_OG_IMAGE', '/images/og-delivtrack-sultra.jpg'),
        'local_business_schema' => env('SEO_LOCAL_BUSINESS_SCHEMA', true),
        'geo_region' => env('SEO_GEO_REGION', 'ID-ST'),
        'geo_placename' => env('SEO_GEO_PLACENAME', 'Kendari, Sulawesi Tenggara'),
        'geo_position' => env('SEO_GEO_POSITION', '-3.945394,122.628893'),
        'icbm' => env('SEO_ICBM', '-3.945394,122.628893'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Theme Settings
    |--------------------------------------------------------------------------
    */

    'theme' => [
        'default' => env('THEME_DEFAULT', 'corporate'),
        'dark' => env('THEME_DARK', 'business'),
        'brand_colors' => [
            'primary' => env('BRAND_PRIMARY_COLOR', '#1e40af'),
            'secondary' => env('BRAND_SECONDARY_COLOR', '#059669'),
            'accent' => env('BRAND_ACCENT_COLOR', '#dc2626'),
        ],
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
        'title' => env('PAGE_TITLE', 'DelivTrack - Sistem Delivery & Tracking Sulawesi Tenggara'),
        'description' => env('PAGE_DESCRIPTION', 'Solusi manajemen transportasi terpadu untuk Sulawesi Tenggara dengan real-time tracking dan workflow otomatis. Digitalisasi surat jalan dan monitoring armada truck.'),
        'favicon' => env('PAGE_FAVICON', '/favicon.ico'),
        'logo' => env('PAGE_LOGO', '/images/logo-delivtrack.png'),
        'meta_image' => env('PAGE_META_IMAGE', '/images/meta-delivtrack.jpg'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Analytics & Tracking
    |--------------------------------------------------------------------------
    */

    'analytics' => [
        'google_tag_id' => env('GOOGLE_TAG_ID', ''),
        'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', ''),
        'facebook_pixel_id' => env('FACEBOOK_PIXEL_ID', ''),
        'hotjar_id' => env('HOTJAR_ID', ''),
        'microsoft_clarity_id' => env('MICROSOFT_CLARITY_ID', ''),
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
            'here_api_key' => env('HERE_API_KEY', ''),
        ],
        'email' => [
            'mailgun_domain' => env('MAILGUN_DOMAIN', ''),
            'mailgun_secret' => env('MAILGUN_SECRET', ''),
            'ses_key' => env('AWS_SES_KEY', ''),
            'ses_secret' => env('AWS_SES_SECRET', ''),
            'smtp_driver' => env('MAIL_MAILER', 'smtp'),
        ],
        'storage' => [
            's3_bucket' => env('AWS_S3_BUCKET', ''),
            'cloudinary_cloud_name' => env('CLOUDINARY_CLOUD_NAME', ''),
            'digital_ocean_spaces' => env('DO_SPACES_BUCKET', ''),
        ],
        'sms' => [
            'twilio_sid' => env('TWILIO_SID', ''),
            'twilio_token' => env('TWILIO_TOKEN', ''),
            'nexmo_key' => env('NEXMO_KEY', ''),
            'nexmo_secret' => env('NEXMO_SECRET', ''),
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
        'notification_emails' => env('CONTACT_FORM_NOTIFICATION_EMAILS', 'info@bkm-transport.co.id,ops@bkm-transport.co.id'),
        'rate_limit' => env('CONTACT_FORM_RATE_LIMIT', 5), // per hour
        'honeypot_enabled' => env('CONTACT_FORM_HONEYPOT', true),
        'recaptcha_enabled' => env('CONTACT_FORM_RECAPTCHA', false),
        'required_fields' => ['name', 'email', 'phone', 'service_type', 'message'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Types for Contact Form
    |--------------------------------------------------------------------------
    */

    'service_types' => [
        'implementation' => 'Implementasi Sistem DelivTrack',
        'consultation' => 'Konsultasi & Demo Produk',
        'integration' => 'Integrasi dengan Sistem Existing',
        'support' => 'Support & Maintenance',
        'training' => 'Pelatihan & Workshop Tim',
        'customization' => 'Kustomisasi Fitur',
        'freight_forwarding' => 'Jasa Freight Forwarding',
        'port_services' => 'Jasa Kepelabuhanan',
        'warehousing' => 'Layanan Pergudangan',
        'other' => 'Layanan Lainnya',
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
        'show_service_areas_map' => env('SHOW_SERVICE_AREAS_MAP', true),
        'enable_whatsapp_chat' => env('ENABLE_WHATSAPP_CHAT', true),
        'show_company_registration' => env('SHOW_COMPANY_REGISTRATION', true),
        'enable_dark_mode_toggle' => env('ENABLE_DARK_MODE_TOGGLE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    */

    'cache' => [
        'ttl' => env('LANDING_PAGE_CACHE_TTL', 3600), // 1 hour
        'enabled' => env('LANDING_PAGE_CACHE_ENABLED', true),
        'tags' => ['landing-page', 'company-info', 'contact-info'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Localization Settings
    |--------------------------------------------------------------------------
    */

    'localization' => [
        'default_locale' => env('APP_LOCALE', 'id'),
        'available_locales' => ['id', 'en'],
        'timezone' => env('APP_TIMEZONE', 'Asia/Makassar'),
        'currency' => env('CURRENCY', 'IDR'),
        'number_format' => [
            'decimal_separator' => ',',
            'thousands_separator' => '.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Emergency Contact & Support
    |--------------------------------------------------------------------------
    */

    'emergency' => [
        'hotline' => env('EMERGENCY_HOTLINE', '+62 812 3456 7890'),
        'support_hours' => env('EMERGENCY_SUPPORT_HOURS', '24/7'),
        'response_time' => env('EMERGENCY_RESPONSE_TIME', '< 30 menit'),
    ],
];
