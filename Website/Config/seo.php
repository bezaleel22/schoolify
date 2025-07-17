<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SEO Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SEO optimization including meta tags, Open Graph,
    | Twitter Cards, and JSON-LD structured data.
    |
    */

    'defaults' => [
        'title' => env('SEO_DEFAULT_TITLE', 'Modern School Website'),
        'description' => env('SEO_DEFAULT_DESCRIPTION', 'Welcome to our modern educational institution providing quality education and holistic development.'),
        'keywords' => env('SEO_DEFAULT_KEYWORDS', 'school,education,learning,students,academics,teachers,curriculum'),
        'author' => env('SEO_AUTHOR', 'School Administration'),
        'robots' => env('SEO_ROBOTS', 'index,follow'),
        'canonical' => env('APP_URL'),
    ],

    'site' => [
        'name' => env('SEO_SITE_NAME', env('APP_NAME')),
        'url' => env('APP_URL'),
        'logo' => env('SEO_SITE_LOGO', '/images/logo.png'),
        'favicon' => env('SEO_FAVICON', '/favicon.ico'),
        'language' => env('APP_LOCALE', 'en'),
        'locale' => env('SEO_LOCALE', 'en_US'),
        'timezone' => env('APP_TIMEZONE', 'UTC'),
    ],

    'open_graph' => [
        'enabled' => true,
        'type' => 'website',
        'site_name' => env('SEO_SITE_NAME', env('APP_NAME')),
        'locale' => env('SEO_OG_LOCALE', 'en_US'),
        'image' => [
            'default' => env('SEO_OG_IMAGE', '/images/og-default.jpg'),
            'width' => 1200,
            'height' => 630,
            'type' => 'image/jpeg',
        ],
        'article' => [
            'author' => env('SEO_ARTICLE_AUTHOR', 'School Administration'),
            'publisher' => env('SEO_SITE_NAME', env('APP_NAME')),
            'section' => 'Education',
        ],
    ],

    'twitter' => [
        'enabled' => true,
        'card' => 'summary_large_image',
        'site' => env('SEO_TWITTER_SITE', '@yourschool'),
        'creator' => env('SEO_TWITTER_CREATOR', '@yourschool'),
        'image' => env('SEO_TWITTER_IMAGE', '/images/twitter-card.jpg'),
    ],

    'json_ld' => [
        'enabled' => true,
        'organization' => [
            '@type' => 'EducationalOrganization',
            'name' => env('SEO_SITE_NAME', env('APP_NAME')),
            'url' => env('APP_URL'),
            'logo' => env('APP_URL') . env('SEO_SITE_LOGO', '/images/logo.png'),
            'description' => env('SEO_DEFAULT_DESCRIPTION'),
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => env('WEBSITE_ADDRESS', '123 School Street'),
                'addressLocality' => env('WEBSITE_CITY', 'Education City'),
                'addressRegion' => env('WEBSITE_STATE', 'EC'),
                'postalCode' => env('WEBSITE_ZIP', '12345'),
                'addressCountry' => env('WEBSITE_COUNTRY', 'US'),
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => env('WEBSITE_CONTACT_PHONE', '+1-555-123-4567'),
                'contactType' => 'customer service',
                'email' => env('WEBSITE_CONTACT_EMAIL', 'contact@example.com'),
            ],
            'sameAs' => [
                env('FACEBOOK_URL', ''),
                env('TWITTER_URL', ''),
                env('INSTAGRAM_URL', ''),
                env('YOUTUBE_URL', ''),
            ],
        ],
        'website' => [
            '@type' => 'WebSite',
            'name' => env('SEO_SITE_NAME', env('APP_NAME')),
            'url' => env('APP_URL'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => env('APP_URL') . '/search?q={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ],

    'meta_tags' => [
        'viewport' => 'width=device-width, initial-scale=1.0',
        'charset' => 'UTF-8',
        'http_equiv' => [
            'X-UA-Compatible' => 'IE=edge',
            'Content-Language' => env('APP_LOCALE', 'en'),
        ],
        'generator' => 'Laravel ' . app()->version(),
        'theme-color' => env('SEO_THEME_COLOR', '#1976d2'),
        'msapplication-TileColor' => env('SEO_TILE_COLOR', '#1976d2'),
    ],

    'structured_data' => [
        'breadcrumbs' => true,
        'article' => true,
        'event' => true,
        'person' => true,
        'local_business' => true,
    ],

    'analytics' => [
        'google' => [
            'tracking_id' => env('GOOGLE_ANALYTICS_ID'),
            'enhanced_ecommerce' => false,
            'anonymize_ip' => true,
        ],
        'google_tag_manager' => [
            'container_id' => env('GOOGLE_TAG_MANAGER_ID'),
        ],
        'facebook_pixel' => [
            'pixel_id' => env('FACEBOOK_PIXEL_ID'),
        ],
    ],

    'verification' => [
        'google' => env('GOOGLE_SITE_VERIFICATION'),
        'bing' => env('BING_SITE_VERIFICATION'),
        'yandex' => env('YANDEX_SITE_VERIFICATION'),
        'pinterest' => env('PINTEREST_SITE_VERIFICATION'),
    ],

    'robots_txt' => [
        'user_agent' => '*',
        'disallow' => [
            '/admin',
            '/api',
            '/storage',
            '/vendor',
            '/*.json',
            '/*.xml',
            '/search',
        ],
        'allow' => [
            '/storage/uploads',
        ],
        'sitemap' => env('APP_URL') . '/sitemap.xml',
        'crawl_delay' => 1,
    ],

    'sitemap' => [
        'enabled' => true,
        'cache_duration' => 86400, // 24 hours
        'include_images' => true,
        'include_videos' => false,
        'max_urls' => 50000,
        'changefreq' => [
            'homepage' => 'daily',
            'pages' => 'weekly',
            'blog' => 'weekly',
            'events' => 'weekly',
            'gallery' => 'monthly',
        ],
        'priority' => [
            'homepage' => 1.0,
            'pages' => 0.8,
            'blog' => 0.6,
            'events' => 0.7,
            'gallery' => 0.5,
        ],
    ],

    'images' => [
        'optimize' => true,
        'lazy_load' => true,
        'alt_required' => true,
        'webp_fallback' => true,
        'sizes' => [
            'thumbnail' => [150, 150],
            'medium' => [300, 300],
            'large' => [800, 600],
            'og_image' => [1200, 630],
            'twitter_card' => [1024, 512],
        ],
    ],

    'performance' => [
        'preload_critical_css' => true,
        'defer_non_critical_css' => true,
        'minify_html' => env('SEO_MINIFY_HTML', false),
        'compress_images' => true,
        'enable_gzip' => true,
    ],
];