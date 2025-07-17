<?php

namespace Modules\Website\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CacheMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string|null  $strategy
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next, ?string $strategy = null): Response
    {
        // Skip caching if disabled or for non-GET requests
        if (!config('cache.enabled', true) || !$request->isMethod('GET')) {
            return $next($request);
        }

        // Skip caching for authenticated users unless specified
        if ($request->user() && !$this->shouldCacheForUser($request, $strategy)) {
            return $next($request);
        }

        $cacheKey = $this->generateCacheKey($request, $strategy);
        $cacheTags = $this->getCacheTags($request, $strategy);
        $cacheTtl = $this->getCacheTtl($request, $strategy);

        // Try to get from cache
        $cachedResponse = $this->getCachedResponse($cacheKey, $cacheTags);
        
        if ($cachedResponse) {
            $this->addCacheHeaders($cachedResponse, true);
            $this->logCacheHit($request, $cacheKey);
            return $cachedResponse;
        }

        // Generate response
        $response = $next($request);

        // Cache the response if it's cacheable
        if ($this->shouldCacheResponse($request, $response, $strategy)) {
            $this->cacheResponse($cacheKey, $cacheTags, $response, $cacheTtl);
            $this->addCacheHeaders($response, false);
            $this->logCacheMiss($request, $cacheKey);
        }

        return $response;
    }

    /**
     * Check if we should cache for authenticated users
     */
    protected function shouldCacheForUser(Request $request, ?string $strategy): bool
    {
        $strategies = config('cache.strategies', []);
        
        if ($strategy && isset($strategies[$strategy])) {
            return $strategies[$strategy]['cache_for_users'] ?? false;
        }

        return false;
    }

    /**
     * Generate cache key for the request
     */
    protected function generateCacheKey(Request $request, ?string $strategy): string
    {
        $parts = [
            'website',
            $strategy ?: 'default',
            $request->path(),
        ];

        // Add query parameters if they affect caching
        $queryParams = $this->getSignificantQueryParams($request, $strategy);
        if (!empty($queryParams)) {
            $parts[] = md5(http_build_query($queryParams));
        }

        // Add user variations if specified
        $variations = $this->getCacheVariations($request, $strategy);
        if (!empty($variations)) {
            $parts[] = md5(serialize($variations));
        }

        return implode(':', $parts);
    }

    /**
     * Get significant query parameters for caching
     */
    protected function getSignificantQueryParams(Request $request, ?string $strategy): array
    {
        $allParams = $request->query();
        
        // Remove cache-busting and tracking parameters
        $ignoredParams = [
            '_', 'timestamp', 'cache_bust', 'v', 'version',
            'utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content',
            'gclid', 'fbclid', 'msclkid',
        ];

        return array_diff_key($allParams, array_flip($ignoredParams));
    }

    /**
     * Get cache variations based on strategy
     */
    protected function getCacheVariations(Request $request, ?string $strategy): array
    {
        $strategies = config('cache.strategies', []);
        $variations = [];

        if ($strategy && isset($strategies[$strategy]['vary_by'])) {
            $varyBy = $strategies[$strategy]['vary_by'];

            foreach ($varyBy as $variation) {
                switch ($variation) {
                    case 'user_type':
                        $variations['user_type'] = $this->getUserType($request);
                        break;
                    case 'device_type':
                        $variations['device_type'] = $this->getDeviceType($request);
                        break;
                    case 'language':
                        $variations['language'] = app()->getLocale();
                        break;
                    case 'theme':
                        $variations['theme'] = $request->cookie('theme', 'default');
                        break;
                    default:
                        // Custom variation from request
                        if ($request->has($variation)) {
                            $variations[$variation] = $request->get($variation);
                        }
                        break;
                }
            }
        }

        return $variations;
    }

    /**
     * Get user type for cache variations
     */
    protected function getUserType(Request $request): string
    {
        if (!$request->user()) {
            return 'guest';
        }

        // Determine user type based on roles or other criteria
        $user = $request->user();
        
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) return 'admin';
            if ($user->hasRole('teacher')) return 'teacher';
            if ($user->hasRole('student')) return 'student';
            if ($user->hasRole('parent')) return 'parent';
        }

        return 'user';
    }

    /**
     * Get device type for cache variations
     */
    protected function getDeviceType(Request $request): string
    {
        $userAgent = strtolower($request->userAgent() ?? '');

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }

        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Get cache tags for the request
     */
    protected function getCacheTags(Request $request, ?string $strategy): array
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        
        $tags = ['website'];

        // Add strategy-specific tags
        if ($strategy) {
            $strategies = config('cache.strategies', []);
            if (isset($strategies[$strategy]['tags'])) {
                $tags = array_merge($tags, $strategies[$strategy]['tags']);
            }
        }

        // Add route-based tags
        if (str_contains($routeName, 'blog')) {
            $tags[] = 'blog_posts';
            if (str_contains($routeName, 'category')) {
                $tags[] = 'blog_categories';
            }
        }

        if (str_contains($routeName, 'events')) {
            $tags[] = 'events';
        }

        if (str_contains($routeName, 'gallery')) {
            $tags[] = 'gallery';
        }

        if (str_contains($routeName, 'staff')) {
            $tags[] = 'staff';
        }

        if (str_contains($routeName, 'page')) {
            $tags[] = 'website_pages';
        }

        return array_unique($tags);
    }

    /**
     * Get cache TTL for the request
     */
    protected function getCacheTtl(Request $request, ?string $strategy): int
    {
        if ($strategy) {
            $strategies = config('cache.strategies', []);
            if (isset($strategies[$strategy]['ttl'])) {
                return $strategies[$strategy]['ttl'];
            }
        }

        return config('cache.default_ttl', 3600);
    }

    /**
     * Get cached response
     */
    protected function getCachedResponse(string $cacheKey, array $cacheTags): ?Response
    {
        try {
            if (config('cache.tags_enabled', true) && !empty($cacheTags)) {
                return Cache::tags($cacheTags)->get($cacheKey);
            } else {
                return Cache::get($cacheKey);
            }
        } catch (\Exception $e) {
            Log::warning('Cache retrieval failed', [
                'key' => $cacheKey,
                'tags' => $cacheTags,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Check if response should be cached
     */
    protected function shouldCacheResponse(Request $request, Response $response, ?string $strategy): bool
    {
        // Don't cache error responses
        if ($response->getStatusCode() >= 400) {
            return false;
        }

        // Don't cache responses with errors
        if ($response->headers->has('X-Error')) {
            return false;
        }

        // Don't cache responses that set cookies (except session cookies)
        $setCookies = $response->headers->getCookies();
        foreach ($setCookies as $cookie) {
            if (!in_array($cookie->getName(), ['laravel_session', 'XSRF-TOKEN'])) {
                return false;
            }
        }

        // Don't cache responses with no-cache headers
        $cacheControl = $response->headers->get('Cache-Control', '');
        if (str_contains($cacheControl, 'no-cache') || str_contains($cacheControl, 'no-store')) {
            return false;
        }

        // Check content type
        $contentType = $response->headers->get('Content-Type', '');
        $cacheableTypes = [
            'text/html',
            'application/json',
            'text/xml',
            'application/xml',
        ];

        foreach ($cacheableTypes as $type) {
            if (str_contains($contentType, $type)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Cache the response
     */
    protected function cacheResponse(string $cacheKey, array $cacheTags, Response $response, int $ttl): void
    {
        try {
            // Clone response to avoid modification
            $cacheableResponse = clone $response;
            
            // Remove headers that shouldn't be cached
            $headersToRemove = ['Set-Cookie', 'Date', 'Last-Modified', 'ETag'];
            foreach ($headersToRemove as $header) {
                $cacheableResponse->headers->remove($header);
            }

            if (config('cache.tags_enabled', true) && !empty($cacheTags)) {
                Cache::tags($cacheTags)->put($cacheKey, $cacheableResponse, $ttl);
            } else {
                Cache::put($cacheKey, $cacheableResponse, $ttl);
            }

            $this->logCacheStore($cacheKey, $cacheTags, $ttl);
        } catch (\Exception $e) {
            Log::warning('Cache storage failed', [
                'key' => $cacheKey,
                'tags' => $cacheTags,
                'ttl' => $ttl,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Add cache headers to response
     */
    protected function addCacheHeaders(Response $response, bool $fromCache): void
    {
        $maxAge = config('cache.browser_cache.max_age', 300);
        
        // Add cache control headers
        $response->headers->set('Cache-Control', "public, max-age={$maxAge}");
        
        // Add ETag for cache validation
        $etag = md5($response->getContent());
        $response->headers->set('ETag', '"' . $etag . '"');
        
        // Add last modified header
        $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s') . ' GMT');
        
        // Add custom headers to indicate cache status
        $response->headers->set('X-Cache', $fromCache ? 'HIT' : 'MISS');
        $response->headers->set('X-Cache-Key', $this->hashCacheKey($response->headers->get('X-Cache-Key', '')));
        
        // Add Vary headers for cache variations
        $varyHeaders = ['Accept-Encoding', 'User-Agent'];
        $response->headers->set('Vary', implode(', ', $varyHeaders));
    }

    /**
     * Hash cache key for header (security)
     */
    protected function hashCacheKey(string $key): string
    {
        return substr(md5($key), 0, 8);
    }

    /**
     * Log cache hit
     */
    protected function logCacheHit(Request $request, string $cacheKey): void
    {
        if (config('cache.debugging.log_hits', false)) {
            Log::info('Cache HIT', [
                'url' => $request->fullUrl(),
                'key' => $this->hashCacheKey($cacheKey),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Update hit metrics
        $this->updateCacheMetrics('hits');
    }

    /**
     * Log cache miss
     */
    protected function logCacheMiss(Request $request, string $cacheKey): void
    {
        if (config('cache.debugging.log_misses', false)) {
            Log::info('Cache MISS', [
                'url' => $request->fullUrl(),
                'key' => $this->hashCacheKey($cacheKey),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Update miss metrics
        $this->updateCacheMetrics('misses');
    }

    /**
     * Log cache storage
     */
    protected function logCacheStore(string $cacheKey, array $cacheTags, int $ttl): void
    {
        if (config('cache.debugging.log_stores', false)) {
            Log::info('Cache STORE', [
                'key' => $this->hashCacheKey($cacheKey),
                'tags' => $cacheTags,
                'ttl' => $ttl,
            ]);
        }

        // Update store metrics
        $this->updateCacheMetrics('stores');
    }

    /**
     * Update cache metrics
     */
    protected function updateCacheMetrics(string $type): void
    {
        if (!config('cache.monitoring.enabled', true)) {
            return;
        }

        try {
            $today = now()->format('Y-m-d');
            $hour = now()->format('H');
            
            // Daily metrics
            Cache::increment("cache:metrics:daily:{$today}:{$type}");
            
            // Hourly metrics
            Cache::increment("cache:metrics:hourly:{$today}:{$hour}:{$type}");
            
            // Total metrics
            Cache::increment("cache:metrics:total:{$type}");
        } catch (\Exception $e) {
            // Silently fail metrics collection
        }
    }

    /**
     * Warm cache for specific routes
     */
    public static function warmCache(array $urls = []): void
    {
        if (empty($urls)) {
            $urls = config('cache.warming.urls', []);
        }

        foreach ($urls as $url) {
            try {
                $response = app('Illuminate\Http\Client\Factory')->get($url);
                Log::info('Cache warmed', ['url' => $url, 'status' => $response->status()]);
            } catch (\Exception $e) {
                Log::warning('Cache warming failed', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Clear cache by tags
     */
    public static function clearByTags(array $tags): void
    {
        try {
            if (config('cache.tags_enabled', true)) {
                Cache::tags($tags)->flush();
                Log::info('Cache cleared by tags', ['tags' => $tags]);
            }
        } catch (\Exception $e) {
            Log::warning('Cache clearing by tags failed', ['tags' => $tags, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Clear cache by pattern
     */
    public static function clearByPattern(string $pattern): void
    {
        try {
            // This is a simplified implementation
            // In production, implement with Redis SCAN or specific cache store methods
            // For now, we'll use a basic approach
            Log::info('Cache clear by pattern requested', ['pattern' => $pattern]);
            
            // This would need to be implemented based on your specific cache store
            // For Redis: use SCAN command
            // For file cache: scan cache directory
            // For database cache: query cache table
            
        } catch (\Exception $e) {
            Log::warning('Cache clearing by pattern failed', ['pattern' => $pattern, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Get cache statistics
     */
    public static function getStatistics(): array
    {
        $today = now()->format('Y-m-d');
        
        return [
            'hits' => Cache::get("cache:metrics:daily:{$today}:hits", 0),
            'misses' => Cache::get("cache:metrics:daily:{$today}:misses", 0),
            'stores' => Cache::get("cache:metrics:daily:{$today}:stores", 0),
            'hit_ratio' => self::calculateHitRatio($today),
            'total_hits' => Cache::get('cache:metrics:total:hits', 0),
            'total_misses' => Cache::get('cache:metrics:total:misses', 0),
        ];
    }

    /**
     * Calculate hit ratio
     */
    protected static function calculateHitRatio(string $date): float
    {
        $hits = Cache::get("cache:metrics:daily:{$date}:hits", 0);
        $misses = Cache::get("cache:metrics:daily:{$date}:misses", 0);
        $total = $hits + $misses;
        
        return $total > 0 ? round(($hits / $total) * 100, 2) : 0.0;
    }
}