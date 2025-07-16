<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Website Module Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching strategies, cache tags, and performance
    | optimization for the Website module.
    |
    */

    'enabled' => env('WEBSITE_CACHE_ENABLED', true),
    
    'default_ttl' => env('WEBSITE_CACHE_TTL', 3600), // 1 hour
    
    'stores' => [
        'default' => env('CACHE_DRIVER', 'redis'),
        'pages' => env('CACHE_DRIVER', 'redis'),
        'assets' => 'file',
        'sessions' => env('SESSION_DRIVER', 'redis'),
    ],

    'tags' => [
        'website_pages' => 'website:pages',
        'blog_posts' => 'website:blog',
        'blog_categories' => 'website:blog:categories',
        'events' => 'website:events',
        'gallery' => 'website:gallery',
        'staff' => 'website:staff',
        'navigation' => 'website:navigation',
        'sitemap' => 'website:sitemap',
        'feeds' => 'website:feeds',
        'search' => 'website:search',
        'analytics' => 'website:analytics',
        'social' => 'website:social',
    ],

    'keys' => [
        'menu_primary' => 'website:menu:primary',
        'menu_footer' => 'website:menu:footer',
        'homepage_data' => 'website:homepage:data',
        'contact_info' => 'website:contact:info',
        'site_settings' => 'website:settings',
        'seo_defaults' => 'website:seo:defaults',
    ],

    'strategies' => [
        'pages' => [
            'ttl' => 7200, // 2 hours
            'tags' => ['website_pages'],
            'vary_by' => ['user_type', 'device_type'],
            'invalidate_on' => ['page_updated', 'page_created', 'page_deleted'],
        ],

        'blog_posts' => [
            'ttl' => 3600, // 1 hour
            'tags' => ['blog_posts', 'blog_categories'],
            'vary_by' => ['category', 'author', 'tags'],
            'invalidate_on' => ['post_published', 'post_updated', 'post_deleted', 'comment_added'],
        ],

        'blog_categories' => [
            'ttl' => 86400, // 24 hours
            'tags' => ['blog_categories'],
            'invalidate_on' => ['category_created', 'category_updated', 'category_deleted'],
        ],

        'events' => [
            'ttl' => 1800, // 30 minutes
            'tags' => ['events'],
            'vary_by' => ['event_type', 'date_range'],
            'invalidate_on' => ['event_created', 'event_updated', 'event_deleted'],
        ],

        'gallery' => [
            'ttl' => 7200, // 2 hours
            'tags' => ['gallery'],
            'vary_by' => ['album', 'image_size'],
            'invalidate_on' => ['image_uploaded', 'album_created', 'album_updated'],
        ],

        'staff' => [
            'ttl' => 14400, // 4 hours
            'tags' => ['staff'],
            'vary_by' => ['department', 'role'],
            'invalidate_on' => ['staff_created', 'staff_updated', 'staff_deleted'],
        ],

        'navigation' => [
            'ttl' => 86400, // 24 hours
            'tags' => ['navigation'],
            'vary_by' => ['menu_location', 'user_role'],
            'invalidate_on' => ['menu_updated', 'page_status_changed'],
        ],

        'sitemap' => [
            'ttl' => 86400, // 24 hours
            'tags' => ['sitemap'],
            'invalidate_on' => ['content_published', 'page_created', 'post_published'],
        ],

        'feeds' => [
            'ttl' => 3600, // 1 hour
            'tags' => ['feeds'],
            'vary_by' => ['feed_type', 'category'],
            'invalidate_on' => ['content_published', 'post_published', 'event_published'],
        ],

        'search_results' => [
            'ttl' => 1800, // 30 minutes
            'tags' => ['search'],
            'vary_by' => ['query', 'filters', 'sort'],
            'max_entries' => 1000,
        ],

        'analytics_data' => [
            'ttl' => 3600, // 1 hour
            'tags' => ['analytics'],
            'vary_by' => ['date_range', 'metric_type'],
        ],

        'social_feeds' => [
            'ttl' => 3600, // 1 hour
            'tags' => ['social'],
            'vary_by' => ['platform', 'feed_type'],
        ],
    ],

    'automatic_invalidation' => [
        'enabled' => true,
        'events' => [
            'Modules\Website\Events\PageUpdated' => ['website_pages'],
            'Modules\Website\Events\BlogPostPublished' => ['blog_posts', 'feeds', 'sitemap'],
            'Modules\Website\Events\EventCreated' => ['events', 'feeds', 'sitemap'],
            'Modules\Website\Events\GalleryUpdated' => ['gallery'],
            'Modules\Website\Events\StaffUpdated' => ['staff'],
            'Modules\Website\Events\MenuUpdated' => ['navigation'],
            'Modules\Website\Events\SettingsUpdated' => ['website_pages', 'navigation', 'feeds'],
        ],
    ],

    'warming' => [
        'enabled' => env('CACHE_WARMING_ENABLED', true),
        'schedule' => [
            'homepage' => '0 */6 * * *', // Every 6 hours
            'navigation' => '0 4 * * *', // Daily at 4 AM
            'sitemap' => '0 3 * * *', // Daily at 3 AM
            'feeds' => '*/30 * * * *', // Every 30 minutes
            'popular_pages' => '0 */2 * * *', // Every 2 hours
        ],
        'urls' => [
            '/',
            '/about',
            '/blog',
            '/events',
            '/gallery',
            '/contact',
        ],
        'concurrent_requests' => 3,
        'timeout' => 30,
    ],

    'compression' => [
        'enabled' => true,
        'algorithm' => 'gzip', // gzip, deflate, br (brotli)
        'level' => 6, // 1-9 for gzip
        'min_size' => 1024, // bytes
        'types' => [
            'text/html',
            'text/css',
            'text/javascript',
            'application/javascript',
            'application/json',
            'application/xml',
            'text/xml',
        ],
    ],

    'optimization' => [
        'lazy_loading' => [
            'enabled' => true,
            'threshold' => 300, // pixels
            'placeholder' => 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7',
        ],
        
        'critical_css' => [
            'enabled' => env('CRITICAL_CSS_ENABLED', false),
            'inline_threshold' => 14336, // 14KB
            'cache_ttl' => 86400, // 24 hours
        ],

        'asset_bundling' => [
            'enabled' => true,
            'css_bundle' => true,
            'js_bundle' => true,
            'versioning' => env('ASSET_VERSION', '1.0.0'),
        ],

        'image_optimization' => [
            'enabled' => env('IMAGE_OPTIMIZATION_ENABLED', true),
            'webp_conversion' => env('WEBP_ENABLED', true),
            'progressive_jpeg' => true,
            'quality' => env('IMAGE_QUALITY', 85),
            'responsive_images' => true,
            'sizes' => [
                'thumbnail' => [150, 150],
                'small' => [300, 300],
                'medium' => [600, 400],
                'large' => [1200, 800],
                'hero' => [1920, 1080],
            ],
        ],
    ],

    'cdn' => [
        'enabled' => env('CDN_ENABLED', false),
        'url' => env('CDN_URL'),
        'assets' => [
            'css' => true,
            'js' => true,
            'images' => true,
            'fonts' => true,
        ],
        'cache_control' => [
            'css' => 'public, max-age=31536000, immutable',
            'js' => 'public, max-age=31536000, immutable',
            'images' => 'public, max-age=2592000',
            'fonts' => 'public, max-age=31536000, immutable',
        ],
    ],

    'headers' => [
        'cache_control' => [
            'static_assets' => 'public, max-age=31536000, immutable',
            'dynamic_content' => 'public, max-age=3600, must-revalidate',
            'private_content' => 'private, no-cache, no-store, must-revalidate',
        ],
        'etag' => [
            'enabled' => true,
            'strong' => true,
        ],
        'last_modified' => true,
        'expires' => true,
    ],

    'purging' => [
        'manual' => [
            'enabled' => true,
            'allowed_users' => ['admin', 'super_admin'],
        ],
        'automatic' => [
            'enabled' => true,
            'max_age' => 604800, // 7 days
            'cleanup_interval' => 86400, // 24 hours
        ],
        'selective' => [
            'enabled' => true,
            'strategies' => [
                'by_tag' => true,
                'by_pattern' => true,
                'by_url' => true,
            ],
        ],
    ],

    'monitoring' => [
        'enabled' => env('CACHE_MONITORING_ENABLED', true),
        'metrics' => [
            'hit_ratio' => true,
            'memory_usage' => true,
            'key_count' => true,
            'eviction_count' => true,
            'response_time' => true,
        ],
        'alerts' => [
            'low_hit_ratio' => 0.8, // Alert if hit ratio < 80%
            'high_memory_usage' => 0.9, // Alert if memory usage > 90%
            'slow_response' => 1000, // Alert if response time > 1000ms
        ],
        'reporting' => [
            'daily' => env('CACHE_DAILY_REPORTS', false),
            'weekly' => env('CACHE_WEEKLY_REPORTS', true),
            'email' => env('CACHE_REPORT_EMAIL'),
        ],
    ],

    'debugging' => [
        'enabled' => env('CACHE_DEBUG', false),
        'log_hits' => false,
        'log_misses' => false,
        'log_invalidations' => true,
        'show_headers' => env('APP_DEBUG', false),
    ],
];