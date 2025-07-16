<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Social Media Integration Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for social media platforms, sharing, authentication,
    | and content integration.
    |
    */

    'platforms' => [
        'facebook' => [
            'enabled' => env('FACEBOOK_ENABLED', true),
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'page_url' => env('FACEBOOK_URL'),
            'page_id' => env('FACEBOOK_PAGE_ID'),
            'pixel_id' => env('FACEBOOK_PIXEL_ID'),
            'features' => [
                'login' => false,
                'sharing' => true,
                'comments' => false,
                'like_button' => true,
                'page_plugin' => true,
            ],
        ],

        'twitter' => [
            'enabled' => env('TWITTER_ENABLED', true),
            'username' => env('TWITTER_USERNAME'),
            'url' => env('TWITTER_URL'),
            'api_key' => env('TWITTER_API_KEY'),
            'api_secret' => env('TWITTER_API_SECRET'),
            'access_token' => env('TWITTER_ACCESS_TOKEN'),
            'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET'),
            'features' => [
                'sharing' => true,
                'follow_button' => true,
                'timeline_embed' => true,
                'auto_post' => false,
            ],
        ],

        'instagram' => [
            'enabled' => env('INSTAGRAM_ENABLED', true),
            'username' => env('INSTAGRAM_USERNAME'),
            'url' => env('INSTAGRAM_URL'),
            'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
            'business_account_id' => env('INSTAGRAM_BUSINESS_ACCOUNT_ID'),
            'features' => [
                'feed_display' => true,
                'stories_display' => false,
                'auto_post' => false,
            ],
        ],

        'youtube' => [
            'enabled' => env('YOUTUBE_ENABLED', true),
            'channel_id' => env('YOUTUBE_CHANNEL_ID'),
            'channel_url' => env('YOUTUBE_URL'),
            'api_key' => env('YOUTUBE_API_KEY'),
            'features' => [
                'video_embed' => true,
                'playlist_embed' => true,
                'subscribe_button' => true,
                'latest_videos' => true,
            ],
        ],

        'linkedin' => [
            'enabled' => env('LINKEDIN_ENABLED', false),
            'company_id' => env('LINKEDIN_COMPANY_ID'),
            'page_url' => env('LINKEDIN_URL'),
            'client_id' => env('LINKEDIN_CLIENT_ID'),
            'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
            'features' => [
                'sharing' => true,
                'follow_button' => true,
                'company_updates' => false,
            ],
        ],

        'tiktok' => [
            'enabled' => env('TIKTOK_ENABLED', false),
            'username' => env('TIKTOK_USERNAME'),
            'url' => env('TIKTOK_URL'),
            'features' => [
                'embed_videos' => false,
                'follow_button' => true,
            ],
        ],
    ],

    'sharing' => [
        'enabled' => true,
        'default_platforms' => ['facebook', 'twitter', 'linkedin', 'whatsapp', 'email'],
        'custom_messages' => [
            'blog_post' => 'Check out this article from {site_name}: {title}',
            'event' => 'Join us for this upcoming event: {title} - {date}',
            'gallery' => 'View our latest photos: {title}',
            'page' => 'Learn more about {title} at {site_name}',
        ],
        'hashtags' => [
            'default' => ['#education', '#school', '#learning'],
            'blog' => ['#blog', '#news', '#education'],
            'events' => ['#events', '#school', '#community'],
            'gallery' => ['#gallery', '#memories', '#school'],
        ],
        'buttons' => [
            'style' => 'modern', // modern, classic, minimal
            'size' => 'medium', // small, medium, large
            'show_count' => false,
            'show_labels' => true,
        ],
    ],

    'authentication' => [
        'google' => [
            'enabled' => env('GOOGLE_AUTH_ENABLED', true),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect_uri' => env('GOOGLE_REDIRECT_URI'),
            'scopes' => ['openid', 'profile', 'email'],
        ],
        'facebook' => [
            'enabled' => false,
            'app_id' => env('FACEBOOK_APP_ID'),
            'app_secret' => env('FACEBOOK_APP_SECRET'),
            'redirect_uri' => env('FACEBOOK_REDIRECT_URI'),
            'scopes' => ['email', 'public_profile'],
        ],
    ],

    'content_integration' => [
        'auto_share' => [
            'enabled' => env('SOCIAL_AUTO_SHARE', false),
            'platforms' => ['twitter', 'facebook'],
            'content_types' => ['blog_posts', 'events'],
            'delay' => 5, // minutes after publishing
        ],
        'cross_posting' => [
            'enabled' => false,
            'duplicate_detection' => true,
            'content_adaptation' => true,
        ],
        'feed_aggregation' => [
            'enabled' => true,
            'cache_duration' => 3600, // 1 hour
            'max_items_per_platform' => 5,
            'filter_school_content' => true,
        ],
    ],

    'widgets' => [
        'facebook_page' => [
            'enabled' => true,
            'width' => 340,
            'height' => 500,
            'hide_cover' => false,
            'show_facepile' => true,
            'show_posts' => true,
            'small_header' => false,
            'adapt_container_width' => true,
        ],
        'twitter_timeline' => [
            'enabled' => true,
            'width' => 400,
            'height' => 600,
            'theme' => 'light', // light, dark
            'link_color' => '#1976d2',
            'border_color' => '#e1e8ed',
            'show_replies' => false,
            'show_border' => true,
        ],
        'instagram_feed' => [
            'enabled' => true,
            'photos_count' => 6,
            'photo_size' => 'medium', // thumbnail, medium, large
            'target' => '_blank',
            'show_caption' => false,
        ],
        'youtube_channel' => [
            'enabled' => true,
            'layout' => 'default', // default, full
            'theme' => 'default', // default, dark
            'show_subscriber_count' => true,
            'show_channel_art' => true,
        ],
    ],

    'meta_tags' => [
        'open_graph' => [
            'enabled' => true,
            'default_image' => '/images/social/og-default.jpg',
            'image_dimensions' => [
                'width' => 1200,
                'height' => 630,
            ],
            'article_author' => env('FACEBOOK_URL'),
            'article_publisher' => env('FACEBOOK_URL'),
        ],
        'twitter_card' => [
            'enabled' => true,
            'card_type' => 'summary_large_image',
            'site' => env('TWITTER_USERNAME'),
            'creator' => env('TWITTER_USERNAME'),
            'default_image' => '/images/social/twitter-card.jpg',
        ],
    ],

    'analytics' => [
        'track_social_interactions' => true,
        'track_share_events' => true,
        'track_follow_events' => true,
        'track_login_events' => true,
        'custom_events' => [
            'social_share' => 'Social Share',
            'social_follow' => 'Social Follow',
            'social_login' => 'Social Login',
            'social_widget_interaction' => 'Social Widget Interaction',
        ],
    ],

    'privacy' => [
        'respect_do_not_track' => true,
        'cookie_consent_required' => env('COOKIE_CONSENT_REQUIRED', true),
        'data_processing_consent' => true,
        'privacy_policy_url' => '/privacy',
        'terms_of_service_url' => '/terms',
    ],

    'moderation' => [
        'enabled' => true,
        'auto_approve' => false,
        'filter_inappropriate' => true,
        'blocked_domains' => [
            'spam-domain.com',
            'inappropriate-site.com',
        ],
        'blocked_keywords' => [
            'spam',
            'inappropriate',
        ],
    ],

    'cache' => [
        'enabled' => true,
        'duration' => [
            'feeds' => 3600, // 1 hour
            'user_profiles' => 7200, // 2 hours
            'share_counts' => 1800, // 30 minutes
            'follower_counts' => 21600, // 6 hours
        ],
        'tags' => [
            'social_feeds',
            'social_profiles',
            'social_counts',
        ],
    ],

    'rate_limiting' => [
        'enabled' => true,
        'limits' => [
            'share_actions' => '10,1', // 10 per minute
            'follow_actions' => '5,1', // 5 per minute
            'api_calls' => '100,60', // 100 per hour
        ],
    ],

    'emergency' => [
        'disable_all' => env('SOCIAL_EMERGENCY_DISABLE', false),
        'disable_auto_posting' => env('SOCIAL_DISABLE_AUTO_POST', false),
        'maintenance_message' => 'Social features are temporarily unavailable.',
    ],
];