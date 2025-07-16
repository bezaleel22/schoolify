<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Analytics Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for web analytics, tracking, and reporting services
    | including Google Analytics, Tag Manager, and custom tracking.
    |
    */

    'enabled' => env('ANALYTICS_ENABLED', true),
    
    'providers' => [
        'google_analytics' => [
            'enabled' => env('GOOGLE_ANALYTICS_ENABLED', true),
            'tracking_id' => env('GOOGLE_ANALYTICS_ID'),
            'measurement_id' => env('GOOGLE_MEASUREMENT_ID'), // GA4
            'api_secret' => env('GOOGLE_ANALYTICS_API_SECRET'), // GA4 Measurement Protocol
            'config' => [
                'anonymize_ip' => true,
                'cookie_expires' => 63072000, // 2 years
                'enhanced_ecommerce' => false,
                'link_attribution' => true,
                'page_title' => true,
                'send_page_view' => true,
                'custom_map' => [
                    'custom_dimension_1' => 'user_type',
                    'custom_dimension_2' => 'page_category',
                    'custom_dimension_3' => 'content_group',
                ],
            ],
        ],

        'google_tag_manager' => [
            'enabled' => env('GOOGLE_TAG_MANAGER_ENABLED', false),
            'container_id' => env('GOOGLE_TAG_MANAGER_ID'),
            'data_layer_name' => 'dataLayer',
            'preview_mode' => env('GTM_PREVIEW_MODE', false),
        ],

        'facebook_pixel' => [
            'enabled' => env('FACEBOOK_PIXEL_ENABLED', false),
            'pixel_id' => env('FACEBOOK_PIXEL_ID'),
            'advanced_matching' => true,
        ],

        'hotjar' => [
            'enabled' => env('HOTJAR_ENABLED', false),
            'site_id' => env('HOTJAR_SITE_ID'),
            'version' => 6,
        ],

        'mixpanel' => [
            'enabled' => env('MIXPANEL_ENABLED', false),
            'token' => env('MIXPANEL_TOKEN'),
            'api_secret' => env('MIXPANEL_API_SECRET'),
        ],
    ],

    'tracking' => [
        'page_views' => true,
        'events' => true,
        'user_interactions' => true,
        'form_submissions' => true,
        'download_tracking' => true,
        'external_link_tracking' => true,
        'scroll_tracking' => true,
        'time_on_page' => true,
    ],

    'events' => [
        'contact_form_submission' => [
            'event_name' => 'contact_form_submit',
            'parameters' => ['form_type', 'page_location'],
        ],
        'newsletter_signup' => [
            'event_name' => 'newsletter_signup',
            'parameters' => ['signup_location', 'user_type'],
        ],
        'blog_post_view' => [
            'event_name' => 'blog_post_view',
            'parameters' => ['post_title', 'post_category', 'author'],
        ],
        'event_view' => [
            'event_name' => 'event_view',
            'parameters' => ['event_title', 'event_type', 'event_date'],
        ],
        'gallery_image_view' => [
            'event_name' => 'gallery_image_view',
            'parameters' => ['album_name', 'image_title'],
        ],
        'file_download' => [
            'event_name' => 'file_download',
            'parameters' => ['file_name', 'file_type', 'download_location'],
        ],
        'search_query' => [
            'event_name' => 'search',
            'parameters' => ['search_term', 'results_count'],
        ],
        'external_link_click' => [
            'event_name' => 'external_link_click',
            'parameters' => ['link_url', 'link_text', 'page_location'],
        ],
        'social_share' => [
            'event_name' => 'social_share',
            'parameters' => ['platform', 'content_type', 'content_title'],
        ],
        'video_play' => [
            'event_name' => 'video_play',
            'parameters' => ['video_title', 'video_duration'],
        ],
    ],

    'goals' => [
        'contact_form_completion' => [
            'type' => 'destination',
            'value' => 10,
            'url' => '/contact/thank-you',
        ],
        'newsletter_signup' => [
            'type' => 'event',
            'value' => 5,
            'event_name' => 'newsletter_signup',
        ],
        'blog_engagement' => [
            'type' => 'duration',
            'value' => 2, // 2 minutes
            'pages' => ['/blog/*'],
        ],
        'file_download' => [
            'type' => 'event',
            'value' => 3,
            'event_name' => 'file_download',
        ],
    ],

    'audiences' => [
        'blog_readers' => [
            'conditions' => [
                'page_path' => '/blog/*',
                'session_duration' => '> 120', // seconds
            ],
        ],
        'event_attendees' => [
            'conditions' => [
                'page_path' => '/events/*',
                'event_name' => 'event_view',
            ],
        ],
        'newsletter_subscribers' => [
            'conditions' => [
                'event_name' => 'newsletter_signup',
            ],
        ],
        'engaged_users' => [
            'conditions' => [
                'session_duration' => '> 300', // 5 minutes
                'page_views' => '> 3',
            ],
        ],
    ],

    'exclusions' => [
        'ip_addresses' => [
            // Admin IP addresses to exclude from tracking
        ],
        'user_agents' => [
            'bot',
            'crawler',
            'spider',
            'scraper',
        ],
        'query_parameters' => [
            'utm_debug',
            'debug',
            'preview',
        ],
        'paths' => [
            '/admin/*',
            '/api/*',
            '/health-check',
            '/robots.txt',
            '/sitemap.xml',
        ],
    ],

    'privacy' => [
        'cookie_consent_required' => env('COOKIE_CONSENT_REQUIRED', true),
        'anonymize_ip' => true,
        'respect_do_not_track' => true,
        'data_retention_days' => 365,
        'demographics_reporting' => false,
        'advertising_features' => false,
    ],

    'reporting' => [
        'enabled' => true,
        'daily_reports' => env('ANALYTICS_DAILY_REPORTS', false),
        'weekly_reports' => env('ANALYTICS_WEEKLY_REPORTS', true),
        'monthly_reports' => env('ANALYTICS_MONTHLY_REPORTS', true),
        'email_recipients' => [
            env('ANALYTICS_REPORT_EMAIL', 'admin@example.com'),
        ],
        'dashboard_url' => env('ANALYTICS_DASHBOARD_URL'),
    ],

    'custom_dimensions' => [
        1 => [
            'name' => 'User Type',
            'scope' => 'user',
            'values' => ['student', 'parent', 'teacher', 'admin', 'visitor'],
        ],
        2 => [
            'name' => 'Page Category',
            'scope' => 'hit',
            'values' => ['homepage', 'about', 'blog', 'events', 'gallery', 'contact'],
        ],
        3 => [
            'name' => 'Content Group',
            'scope' => 'hit',
            'values' => ['academics', 'admissions', 'news', 'resources'],
        ],
        4 => [
            'name' => 'Device Category',
            'scope' => 'session',
            'values' => ['desktop', 'mobile', 'tablet'],
        ],
    ],

    'custom_metrics' => [
        1 => [
            'name' => 'Form Completion Rate',
            'scope' => 'hit',
            'type' => 'percentage',
        ],
        2 => [
            'name' => 'Download Count',
            'scope' => 'hit',
            'type' => 'integer',
        ],
        3 => [
            'name' => 'Social Shares',
            'scope' => 'hit',
            'type' => 'integer',
        ],
    ],

    'real_time' => [
        'enabled' => env('ANALYTICS_REAL_TIME', true),
        'refresh_interval' => 30, // seconds
        'metrics' => [
            'active_users',
            'page_views',
            'top_pages',
            'traffic_sources',
            'geographic_data',
        ],
    ],

    'api' => [
        'google_analytics' => [
            'view_id' => env('GOOGLE_ANALYTICS_VIEW_ID'),
            'service_account_path' => env('GOOGLE_ANALYTICS_SERVICE_ACCOUNT_PATH'),
            'cache_lifetime' => 3600, // 1 hour
        ],
    ],

    'debugging' => [
        'enabled' => env('ANALYTICS_DEBUG', false),
        'log_events' => env('ANALYTICS_LOG_EVENTS', false),
        'console_output' => env('ANALYTICS_CONSOLE_OUTPUT', false),
    ],
];