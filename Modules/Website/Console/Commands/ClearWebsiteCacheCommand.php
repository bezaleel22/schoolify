<?php

namespace Modules\Website\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Modules\Website\Http\Middleware\CacheMiddleware;

class ClearWebsiteCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:clear-cache 
                            {--tags= : Specific cache tags to clear (comma-separated)}
                            {--pattern= : Clear cache by pattern}
                            {--all : Clear all website cache}
                            {--stats : Show cache statistics before clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear website module cache';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting website cache clearing...');

        try {
            // Show statistics if requested
            if ($this->option('stats')) {
                $this->displayCacheStatistics();
            }

            $cleared = 0;

            if ($this->option('all')) {
                $cleared = $this->clearAllCache();
                $this->info("All website cache cleared: {$cleared} entries");
            } elseif ($this->option('tags')) {
                $tags = array_map('trim', explode(',', $this->option('tags')));
                $cleared = $this->clearCacheByTags($tags);
                $this->info("Cache cleared for tags [" . implode(', ', $tags) . "]: {$cleared} entries");
            } elseif ($this->option('pattern')) {
                $pattern = $this->option('pattern');
                $cleared = $this->clearCacheByPattern($pattern);
                $this->info("Cache cleared for pattern '{$pattern}': {$cleared} entries");
            } else {
                // Default: clear common website caches
                $cleared = $this->clearCommonCache();
                $this->info("Common website cache cleared: {$cleared} entries");
            }

            $this->info('Website cache clearing completed successfully!');
            return 0;

        } catch (\Exception $e) {
            $this->error('Cache clearing failed: ' . $e->getMessage());
            Log::error('Website cache clearing failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Clear all website cache
     */
    protected function clearAllCache(): int
    {
        $cleared = 0;

        // Clear all website-related cache tags
        $allTags = [
            'website_pages',
            'blog_posts',
            'blog_categories',
            'events',
            'gallery',
            'staff',
            'navigation',
            'sitemap',
            'feeds',
            'search',
            'analytics',
            'social',
        ];

        foreach ($allTags as $tag) {
            $cleared += $this->clearSingleTag($tag);
        }

        // Clear specific cache keys
        $cacheKeys = [
            'website:menu:primary',
            'website:menu:footer',
            'website:homepage:data',
            'website:contact:info',
            'website:settings',
            'website:seo:defaults',
        ];

        foreach ($cacheKeys as $key) {
            if (Cache::has($key)) {
                Cache::forget($key);
                $cleared++;
            }
        }

        // Clear analytics cache
        $cleared += $this->clearAnalyticsCache();

        return $cleared;
    }

    /**
     * Clear cache by specific tags
     */
    protected function clearCacheByTags(array $tags): int
    {
        $cleared = 0;

        foreach ($tags as $tag) {
            $cleared += $this->clearSingleTag($tag);
        }

        return $cleared;
    }

    /**
     * Clear cache by pattern
     */
    protected function clearCacheByPattern(string $pattern): int
    {
        // Use the CacheMiddleware method
        CacheMiddleware::clearByPattern($pattern);
        
        // Return estimated count (since we can't easily count pattern matches)
        return 1;
    }

    /**
     * Clear common website cache
     */
    protected function clearCommonCache(): int
    {
        $cleared = 0;

        // Common tags to clear
        $commonTags = [
            'website_pages',
            'blog_posts',
            'navigation',
            'sitemap',
        ];

        foreach ($commonTags as $tag) {
            $cleared += $this->clearSingleTag($tag);
        }

        return $cleared;
    }

    /**
     * Clear a single cache tag
     */
    protected function clearSingleTag(string $tag): int
    {
        try {
            if (config('cache.tags_enabled', true)) {
                Cache::tags([$tag])->flush();
            }
            return 1;
        } catch (\Exception $e) {
            Log::warning("Failed to clear cache tag: {$tag}", ['error' => $e->getMessage()]);
            return 0;
        }
    }

    /**
     * Clear analytics cache
     */
    protected function clearAnalyticsCache(): int
    {
        $cleared = 0;
        $today = now()->format('Y-m-d');
        
        // Analytics cache patterns
        $patterns = [
            "analytics:daily:{$today}:*",
            "analytics:hourly:{$today}:*",
            "analytics:events:*",
            "analytics:response_times:*",
            "analytics:session_durations",
            "analytics:recent_errors",
        ];

        foreach ($patterns as $pattern) {
            // This is a simplified approach
            // In production, you'd implement proper pattern matching based on your cache store
            $keys = $this->getCacheKeysByPattern($pattern);
            foreach ($keys as $key) {
                Cache::forget($key);
                $cleared++;
            }
        }

        return $cleared;
    }

    /**
     * Get cache keys by pattern (simplified implementation)
     */
    protected function getCacheKeysByPattern(string $pattern): array
    {
        // This is a placeholder implementation
        // In production, implement based on your specific cache store
        // For Redis: use SCAN command
        // For file cache: scan cache directory
        // For database cache: query cache table
        
        return [];
    }

    /**
     * Display cache statistics
     */
    protected function displayCacheStatistics(): void
    {
        $this->info('Current cache statistics:');
        
        try {
            $stats = CacheMiddleware::getStatistics();
            
            $this->table(
                ['Metric', 'Value'],
                [
                    ['Daily Hits', number_format($stats['hits'])],
                    ['Daily Misses', number_format($stats['misses'])],
                    ['Hit Ratio', $stats['hit_ratio'] . '%'],
                    ['Total Hits', number_format($stats['total_hits'])],
                    ['Total Misses', number_format($stats['total_misses'])],
                ]
            );
        } catch (\Exception $e) {
            $this->warn('Unable to retrieve cache statistics: ' . $e->getMessage());
        }
        
        $this->line('');
    }

    /**
     * Warm cache after clearing
     */
    public function warmCache(): void
    {
        $this->info('Warming cache...');
        
        try {
            // Get URLs to warm from config
            $urls = config('cache.warming.urls', [
                '/',
                '/about',
                '/blog',
                '/events',
                '/gallery',
                '/contact',
            ]);
            
            CacheMiddleware::warmCache($urls);
            $this->info('Cache warmed for ' . count($urls) . ' URLs');
            
        } catch (\Exception $e) {
            $this->warn('Cache warming failed: ' . $e->getMessage());
        }
    }

    /**
     * Clear cache for specific content types
     */
    public function clearContentTypeCache(string $contentType): int
    {
        $cleared = 0;
        
        switch ($contentType) {
            case 'pages':
                $cleared += $this->clearSingleTag('website_pages');
                $cleared += $this->clearSingleTag('navigation');
                $cleared += $this->clearSingleTag('sitemap');
                break;
                
            case 'blog':
                $cleared += $this->clearSingleTag('blog_posts');
                $cleared += $this->clearSingleTag('blog_categories');
                $cleared += $this->clearSingleTag('feeds');
                break;
                
            case 'events':
                $cleared += $this->clearSingleTag('events');
                $cleared += $this->clearSingleTag('feeds');
                break;
                
            case 'gallery':
                $cleared += $this->clearSingleTag('gallery');
                break;
                
            case 'staff':
                $cleared += $this->clearSingleTag('staff');
                break;
                
            default:
                $this->warn("Unknown content type: {$contentType}");
                break;
        }
        
        return $cleared;
    }

    /**
     * Get cache size estimation
     */
    protected function getCacheSizeEstimation(): string
    {
        try {
            // This would need to be implemented based on your cache store
            // For Redis: use MEMORY USAGE command
            // For file cache: calculate directory size
            // For database cache: sum data sizes
            
            return 'Size calculation not implemented';
        } catch (\Exception $e) {
            return 'Unable to calculate cache size';
        }
    }

    /**
     * Show detailed cache information
     */
    public function showCacheInfo(): void
    {
        $this->info('Website Cache Information:');
        $this->line('');
        
        $this->table(
            ['Setting', 'Value'],
            [
                ['Cache Enabled', config('cache.enabled') ? 'Yes' : 'No'],
                ['Default TTL', config('cache.default_ttl') . ' seconds'],
                ['Tags Enabled', config('cache.tags_enabled') ? 'Yes' : 'No'],
                ['Cache Store', config('cache.default')],
                ['Estimated Size', $this->getCacheSizeEstimation()],
            ]
        );
        
        $this->line('');
        $this->info('Available cache tags:');
        $this->line('- website_pages (Page content and navigation)');
        $this->line('- blog_posts (Blog posts and comments)');
        $this->line('- blog_categories (Blog categories)');
        $this->line('- events (Events and calendar data)');
        $this->line('- gallery (Gallery albums and images)');
        $this->line('- staff (Staff member profiles)');
        $this->line('- navigation (Menu and navigation data)');
        $this->line('- sitemap (XML sitemap data)');
        $this->line('- feeds (RSS/Atom feeds)');
        $this->line('- search (Search results)');
        $this->line('- analytics (Analytics data)');
        $this->line('- social (Social media data)');
    }
}