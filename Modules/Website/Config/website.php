<?php

return [
    'name' => 'Website',
    
    /*
    |--------------------------------------------------------------------------
    | Website Module Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file contains all the settings for the Website module
    | including theming, caching, SEO, and feature toggles.
    |
    */

    'enabled' => env('WEBSITE_ENABLED', true),
    
    'theme' => [
        'default' => env('WEBSITE_THEME', 'default'),
        'path' => 'website::layouts.app',
        'assets_path' => 'website/assets',
        'views_path' => 'website::',
    ],
    
    'cache' => [
        'enabled' => env('WEBSITE_CACHE_ENABLED', true),
        'ttl' => env('WEBSITE_CACHE_TTL', 3600),
        'tags' => [
            'website_pages',
            'blog_posts',
            'events',
            'gallery',
            'staff',
        ],
        'keys' => [
            'menu' => 'website:menu',
            'pages' => 'website:pages:',
            'blog' => 'website:blog:',
            'events' => 'website:events:',
            'gallery' => 'website:gallery:',
            'sitemap' => 'website:sitemap',
        ],
    ],
    
    'features' => [
        'blog' => env('BLOG_ENABLED', true),
        'events' => env('EVENTS_ENABLED', true),
        'gallery' => env('GALLERY_ENABLED', true),
        'newsletter' => env('NEWSLETTER_ENABLED', true),
        'comments' => env('BLOG_COMMENTS_ENABLED', true),
        'social_auth' => env('GOOGLE_AUTH_ENABLED', true),
        'analytics' => env('ANALYTICS_ENABLED', true),
        'search' => env('SEARCH_ENABLED', true),
    ],
    
    'pagination' => [
        'blog_posts' => env('BLOG_POSTS_PER_PAGE', 10),
        'events' => env('EVENTS_PER_PAGE', 12),
        'gallery_images' => env('GALLERY_IMAGES_PER_PAGE', 12),
        'staff_members' => env('STAFF_PER_PAGE', 8),
        'search_results' => env('SEARCH_RESULTS_PER_PAGE', 15),
    ],
    
    'uploads' => [
        'disk' => env('FILESYSTEM_DISK', 'public'),
        'paths' => [
            'blog' => 'uploads/website/blog',
            'events' => 'uploads/website/events',
            'gallery' => 'uploads/website/gallery',
            'staff' => 'uploads/website/staff',
            'pages' => 'uploads/website/pages',
        ],
        'max_size' => env('UPLOAD_MAX_SIZE', 10240), // KB
        'allowed_types' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'pdf', 'doc', 'docx'],
    ],
    
    'contact' => [
        'email' => env('WEBSITE_CONTACT_EMAIL', 'contact@example.com'),
        'phone' => env('WEBSITE_CONTACT_PHONE', '+1-555-123-4567'),
        'address' => env('WEBSITE_ADDRESS', '123 Main Street, City, State 12345'),
        'office_hours' => env('WEBSITE_OFFICE_HOURS', 'Monday-Friday: 9:00 AM - 5:00 PM'),
        'coordinates' => [
            'lat' => env('WEBSITE_LATITUDE', 40.7128),
            'lng' => env('WEBSITE_LONGITUDE', -74.0060),
        ],
    ],
    
    'social' => [
        'facebook' => env('FACEBOOK_URL', ''),
        'twitter' => env('TWITTER_URL', ''),
        'instagram' => env('INSTAGRAM_URL', ''),
        'youtube' => env('YOUTUBE_URL', ''),
        'linkedin' => env('LINKEDIN_URL', ''),
    ],
    
    'meta' => [
        'author' => env('WEBSITE_AUTHOR', 'School Administration'),
        'robots' => env('WEBSITE_ROBOTS', 'index,follow'),
        'viewport' => 'width=device-width, initial-scale=1.0',
        'charset' => 'UTF-8',
    ],
    
    'security' => [
        'csrf_enabled' => true,
        'rate_limiting' => [
            'contact_form' => '5,1', // 5 requests per minute
            'newsletter' => '3,1', // 3 requests per minute
            'search' => '30,1', // 30 requests per minute
        ],
        'spam_protection' => [
            'honeypot' => true,
            'recaptcha' => env('RECAPTCHA_ENABLED', false),
        ],
    ],
    
    'performance' => [
        'image_optimization' => env('IMAGE_OPTIMIZATION_ENABLED', true),
        'lazy_loading' => true,
        'minify_html' => env('MINIFY_HTML', false),
        'critical_css' => env('CRITICAL_CSS_ENABLED', false),
    ],
    
    'menu' => [
        'cache_ttl' => 86400, // 24 hours
        'max_depth' => 3,
        'locations' => [
            'primary' => 'Primary Navigation',
            'footer' => 'Footer Navigation',
            'sidebar' => 'Sidebar Navigation',
        ],
    ],
    
    'feeds' => [
        'enabled' => true,
        'formats' => ['rss', 'atom'],
        'items_limit' => 20,
        'cache_ttl' => 3600,
    ],
    
    'sitemap' => [
        'enabled' => true,
        'cache_ttl' => 86400,
        'include' => [
            'pages' => true,
            'blog_posts' => true,
            'events' => true,
            'gallery_albums' => true,
            'staff_profiles' => false,
        ],
        'changefreq' => [
            'pages' => 'weekly',
            'blog_posts' => 'monthly',
            'events' => 'weekly',
        ],
        'priority' => [
            'homepage' => 1.0,
            'pages' => 0.8,
            'blog_posts' => 0.6,
            'events' => 0.7,
        ],
    ],
];