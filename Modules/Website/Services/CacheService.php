<?php

namespace Modules\Website\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class CacheService
{
    protected $defaultTTL = 3600; // 1 hour
    protected $prefix = 'website_';

    /**
     * Store data in cache with TTL
     */
    public function put($key, $value, $ttl = null)
    {
        $ttl = $ttl ?: $this->defaultTTL;
        $fullKey = $this->prefix . $key;
        
        return Cache::put($fullKey, $value, $ttl);
    }

    /**
     * Retrieve data from cache
     */
    public function get($key, $default = null)
    {
        $fullKey = $this->prefix . $key;
        
        return Cache::get($fullKey, $default);
    }

    /**
     * Remember data in cache
     */
    public function remember($key, $ttl, $callback)
    {
        $fullKey = $this->prefix . $key;
        
        return Cache::remember($fullKey, $ttl, $callback);
    }

    /**
     * Forget cached data
     */
    public function forget($key)
    {
        $fullKey = $this->prefix . $key;
        
        return Cache::forget($fullKey);
    }

    /**
     * Clear all website cache
     */
    public function flush()
    {
        if (config('cache.default') === 'redis') {
            Redis::flushdb();
        } else {
            Cache::flush();
        }
    }

    /**
     * Cache homepage data
     */
    public function cacheHomepage($data)
    {
        return $this->put('homepage', $data, 900); // 15 minutes
    }

    /**
     * Cache blog posts
     */
    public function cacheBlogPosts($categorySlug, $data)
    {
        $key = $categorySlug ? "blog_posts_{$categorySlug}" : 'blog_posts_all';
        return $this->put($key, $data, 1800); // 30 minutes
    }

    /**
     * Cache events
     */
    public function cacheEvents($type, $data)
    {
        $key = "events_{$type}";
        return $this->put($key, $data, 1800); // 30 minutes
    }

    /**
     * Cache page data
     */
    public function cachePage($slug, $data)
    {
        return $this->put("page_{$slug}", $data, 3600); // 1 hour
    }

    /**
     * Cache search results
     */
    public function cacheSearch($query, $type, $data)
    {
        $key = "search_" . md5($query . $type);
        return $this->put($key, $data, 300); // 5 minutes
    }

    /**
     * Cache navigation menu
     */
    public function cacheNavigation($data)
    {
        return $this->put('navigation', $data, 7200); // 2 hours
    }

    /**
     * Cache footer data
     */
    public function cacheFooter($data)
    {
        return $this->put('footer', $data, 7200); // 2 hours
    }

    /**
     * Cache SEO sitemap
     */
    public function cacheSitemap($data)
    {
        return $this->put('sitemap', $data, 86400); // 24 hours
    }

    /**
     * Cache analytics data
     */
    public function cacheAnalytics($key, $data)
    {
        return $this->put("analytics_{$key}", $data, 1800); // 30 minutes
    }

    /**
     * Cache gallery data
     */
    public function cacheGallery($albumSlug, $data)
    {
        $key = $albumSlug ? "gallery_{$albumSlug}" : 'gallery_all';
        return $this->put($key, $data, 3600); // 1 hour
    }

    /**
     * Cache staff data
     */
    public function cacheStaff($department, $data)
    {
        $key = $department ? "staff_{$department}" : 'staff_all';
        return $this->put($key, $data, 7200); // 2 hours
    }

    /**
     * Invalidate related cache keys
     */
    public function invalidateRelated($pattern)
    {
        $keys = $this->getKeysByPattern($pattern);
        
        foreach ($keys as $key) {
            $this->forget(str_replace($this->prefix, '', $key));
        }
    }

    /**
     * Invalidate blog-related cache
     */
    public function invalidateBlogCache()
    {
        $this->invalidateRelated('blog_*');
        $this->forget('homepage');
        $this->forget('sitemap');
    }

    /**
     * Invalidate event-related cache
     */
    public function invalidateEventCache()
    {
        $this->invalidateRelated('events_*');
        $this->forget('homepage');
        $this->forget('sitemap');
    }

    /**
     * Invalidate page-related cache
     */
    public function invalidatePageCache($slug = null)
    {
        if ($slug) {
            $this->forget("page_{$slug}");
        } else {
            $this->invalidateRelated('page_*');
        }
        
        $this->forget('sitemap');
        $this->forget('navigation');
    }

    /**
     * Get cache statistics
     */
    public function getStats()
    {
        if (config('cache.default') === 'redis') {
            try {
                $info = Redis::info();
                return [
                    'driver' => 'redis',
                    'memory_usage' => $info['used_memory_human'] ?? 'N/A',
                    'keys' => $info['db0']['keys'] ?? 0,
                    'hits' => $info['keyspace_hits'] ?? 0,
                    'misses' => $info['keyspace_misses'] ?? 0
                ];
            } catch (\Exception $e) {
                return ['error' => 'Could not retrieve Redis stats'];
            }
        }

        return [
            'driver' => config('cache.default'),
            'message' => 'Stats only available for Redis driver'
        ];
    }

    /**
     * Warm up cache with commonly accessed data
     */
    public function warmUp()
    {
        // This would be called by a scheduled job
        $cacheKeys = [
            'homepage' => function () {
                return app(WebsiteService::class)->getHomepageData();
            },
            'navigation' => function () {
                return app(WebsiteService::class)->getNavigationData();
            },
            'footer' => function () {
                return app(WebsiteService::class)->getFooterData();
            }
        ];

        foreach ($cacheKeys as $key => $callback) {
            if (!$this->get($key)) {
                $this->put($key, $callback());
            }
        }
    }

    /**
     * Get cache keys by pattern (Redis only)
     */
    private function getKeysByPattern($pattern)
    {
        if (config('cache.default') === 'redis') {
            try {
                return Redis::keys($this->prefix . $pattern);
            } catch (\Exception $e) {
                return [];
            }
        }

        return [];
    }

    /**
     * Set default TTL
     */
    public function setDefaultTTL($seconds)
    {
        $this->defaultTTL = $seconds;
    }

    /**
     * Get default TTL
     */
    public function getDefaultTTL()
    {
        return $this->defaultTTL;
    }

    /**
     * Set cache prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get cache prefix
     */
    public function getPrefix()
    {
        return $this->prefix;
    }
}