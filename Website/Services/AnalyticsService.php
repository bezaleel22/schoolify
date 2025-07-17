<?php

namespace Modules\Website\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class AnalyticsService
{
    /**
     * Track page view
     */
    public function trackPageView(string $page, ?Request $request = null, array $customData = []): void
    {
        try {
            $data = [
                'page' => $page,
                'timestamp' => now()->toISOString(),
                'ip' => $request ? $request->ip() : request()->ip(),
                'user_agent' => $request ? $request->userAgent() : request()->userAgent(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'custom_data' => $customData,
            ];

            // Store in cache for processing
            $this->storeAnalyticsEvent('page_view', $data);

            // Update counters
            $this->incrementMetric('page_views');
            $this->incrementMetric("page_views:{$page}");

        } catch (\Exception $e) {
            Log::warning('Analytics tracking failed', [
                'page' => $page,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Track custom event
     */
    public function trackEvent(string $eventName, array $data = []): void
    {
        try {
            $eventData = [
                'event' => $eventName,
                'timestamp' => now()->toISOString(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'data' => $data,
            ];

            $this->storeAnalyticsEvent('custom_event', $eventData);
            $this->incrementMetric("events:{$eventName}");

        } catch (\Exception $e) {
            Log::warning('Event tracking failed', [
                'event' => $eventName,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Store analytics event
     */
    protected function storeAnalyticsEvent(string $type, array $data): void
    {
        $key = "analytics:events:" . date('Y-m-d-H');
        $events = Cache::get($key, []);
        $events[] = ['type' => $type, 'data' => $data];
        
        // Keep only last 1000 events per hour
        if (count($events) > 1000) {
            $events = array_slice($events, -1000);
        }
        
        Cache::put($key, $events, now()->addHours(25));
    }

    /**
     * Increment metric counter
     */
    protected function incrementMetric(string $metric): void
    {
        $today = now()->format('Y-m-d');
        $hour = now()->format('H');
        
        Cache::increment("analytics:daily:{$today}:{$metric}");
        Cache::increment("analytics:hourly:{$today}:{$hour}:{$metric}");
        Cache::increment("analytics:total:{$metric}");
    }

    /**
     * Track search query
     */
    public function trackSearch(string $query, int $resultsCount): void
    {
        try {
            $this->trackEvent('search', [
                'query' => $query,
                'results_count' => $resultsCount,
                'has_results' => $resultsCount > 0,
            ]);

            // Track search metrics
            $this->incrementMetric('searches');
            if ($resultsCount === 0) {
                $this->incrementMetric('searches:no_results');
            }

        } catch (\Exception $e) {
            Log::warning('Search tracking failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get analytics data
     */
    public function getAnalytics(string $period = 'today'): array
    {
        $date = now()->format('Y-m-d');
        
        return [
            'page_views' => Cache::get("analytics:daily:{$date}:page_views", 0),
            'unique_visitors' => Cache::get("analytics:daily:{$date}:unique_visitors", 0),
            'sessions' => Cache::get("analytics:daily:{$date}:sessions", 0),
            'top_pages' => $this->getTopPages($period),
            'user_agents' => $this->getUserAgentStats($period),
        ];
    }

    /**
     * Get top pages
     */
    protected function getTopPages(string $period): array
    {
        // Simplified implementation - return placeholder data
        return [
            'homepage' => Cache::get("analytics:daily:" . now()->format('Y-m-d') . ":page_views:home", 0),
            'blog' => Cache::get("analytics:daily:" . now()->format('Y-m-d') . ":page_views:blog", 0),
            'about' => Cache::get("analytics:daily:" . now()->format('Y-m-d') . ":page_views:about", 0),
        ];
    }

    /**
     * Get user agent statistics
     */
    protected function getUserAgentStats(string $period): array
    {
        // Simplified implementation - return placeholder data
        return [
            'desktop' => 60,
            'mobile' => 35,
            'tablet' => 5,
        ];
    }
}