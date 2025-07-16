<?php

namespace Modules\Website\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AnalyticsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Track request start
        $this->trackRequestStart($request);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        // Track response and analytics
        $this->trackResponse($request, $response, $responseTime);
        
        // Add analytics scripts to HTML responses
        if ($this->shouldAddAnalyticsScripts($response)) {
            $this->addAnalyticsScripts($request, $response);
        }
        
        return $response;
    }

    /**
     * Track request start
     */
    protected function trackRequestStart(Request $request): void
    {
        if (!config('analytics.enabled', true)) {
            return;
        }

        // Skip tracking for excluded paths
        if ($this->shouldSkipTracking($request)) {
            return;
        }

        $sessionId = $request->session()->getId();
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        
        // Track unique visitors
        $visitorKey = 'analytics:visitor:' . md5($ip . $userAgent);
        if (!Cache::has($visitorKey)) {
            Cache::put($visitorKey, true, now()->addDay());
            $this->incrementMetric('unique_visitors');
        }

        // Track page views
        $this->incrementMetric('page_views');
        
        // Track by page
        $page = $this->getPageIdentifier($request);
        $this->incrementMetric('page_views:' . $page);
        
        // Track user sessions
        $sessionKey = 'analytics:session:' . $sessionId;
        if (!Cache::has($sessionKey)) {
            Cache::put($sessionKey, [
                'start_time' => now(),
                'user_agent' => $userAgent,
                'ip' => $ip,
                'entry_page' => $page,
            ], now()->addMinutes(30));
            $this->incrementMetric('sessions');
        }

        // Track referrers
        $referrer = $request->header('referer');
        if ($referrer && !str_contains($referrer, $request->getHost())) {
            $this->trackReferrer($referrer);
        }

        // Track device types
        $deviceType = $this->detectDeviceType($userAgent);
        $this->incrementMetric('device_types:' . $deviceType);

        // Track geographic data (basic IP-based)
        $this->trackGeography($ip);
    }

    /**
     * Track response and analytics data
     */
    protected function trackResponse(Request $request, Response $response, float $responseTime): void
    {
        if (!config('analytics.enabled', true)) {
            return;
        }

        if ($this->shouldSkipTracking($request)) {
            return;
        }

        $statusCode = $response->getStatusCode();
        $page = $this->getPageIdentifier($request);

        // Track response times
        $this->trackResponseTime($page, $responseTime);
        
        // Track status codes
        $this->incrementMetric('status_codes:' . $statusCode);
        
        // Track errors
        if ($statusCode >= 400) {
            $this->trackError($request, $response, $statusCode);
        }

        // Track successful page completions
        if ($statusCode === 200) {
            $this->incrementMetric('successful_requests');
            $this->incrementMetric('successful_requests:' . $page);
        }

        // Track bounce rate data
        $this->trackBounceData($request);

        // Log analytics event
        $this->logAnalyticsEvent($request, $response, $responseTime);
    }

    /**
     * Check if tracking should be skipped
     */
    protected function shouldSkipTracking(Request $request): bool
    {
        $excludedPaths = config('analytics.exclusions.paths', []);
        $excludedUserAgents = config('analytics.exclusions.user_agents', []);
        $excludedIPs = config('analytics.exclusions.ip_addresses', []);

        $path = $request->path();
        $userAgent = strtolower($request->userAgent() ?? '');
        $ip = $request->ip();

        // Check excluded paths
        foreach ($excludedPaths as $excludedPath) {
            if (fnmatch($excludedPath, $path)) {
                return true;
            }
        }

        // Check excluded user agents (bots, crawlers)
        foreach ($excludedUserAgents as $excludedAgent) {
            if (str_contains($userAgent, strtolower($excludedAgent))) {
                return true;
            }
        }

        // Check excluded IPs
        if (in_array($ip, $excludedIPs)) {
            return true;
        }

        // Check for Do Not Track header
        if (config('analytics.privacy.respect_do_not_track', true)) {
            if ($request->header('DNT') === '1') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get page identifier for analytics
     */
    protected function getPageIdentifier(Request $request): string
    {
        $route = $request->route();
        
        if ($route && $route->getName()) {
            return $route->getName();
        }

        $path = $request->path();
        
        // Normalize dynamic paths
        $path = preg_replace('/\/\d+/', '/{id}', $path);
        $path = preg_replace('/\/[a-f0-9-]{36}/', '/{uuid}', $path);
        
        return $path;
    }

    /**
     * Increment analytics metric
     */
    protected function incrementMetric(string $metric): void
    {
        $today = now()->format('Y-m-d');
        $hour = now()->format('H');
        
        // Daily metrics
        Cache::increment("analytics:daily:{$today}:{$metric}");
        
        // Hourly metrics
        Cache::increment("analytics:hourly:{$today}:{$hour}:{$metric}");
        
        // All-time metrics
        Cache::increment("analytics:total:{$metric}");
    }

    /**
     * Track referrer information
     */
    protected function trackReferrer(string $referrer): void
    {
        $parsed = parse_url($referrer);
        $domain = $parsed['host'] ?? 'unknown';
        
        // Track referrer domains
        $this->incrementMetric('referrers:' . $domain);
        
        // Identify search engines
        $searchEngines = [
            'google.com' => 'google',
            'bing.com' => 'bing',
            'yahoo.com' => 'yahoo',
            'duckduckgo.com' => 'duckduckgo',
        ];

        foreach ($searchEngines as $engine => $name) {
            if (str_contains($domain, $engine)) {
                $this->incrementMetric('search_engines:' . $name);
                break;
            }
        }
    }

    /**
     * Detect device type from user agent
     */
    protected function detectDeviceType(string $userAgent): string
    {
        $userAgent = strtolower($userAgent);

        if (str_contains($userAgent, 'mobile') || str_contains($userAgent, 'android')) {
            return 'mobile';
        }

        if (str_contains($userAgent, 'tablet') || str_contains($userAgent, 'ipad')) {
            return 'tablet';
        }

        return 'desktop';
    }

    /**
     * Track basic geography from IP
     */
    protected function trackGeography(string $ip): void
    {
        // Simple country detection (in production, use GeoIP service)
        $country = $this->getCountryFromIP($ip);
        
        if ($country) {
            $this->incrementMetric('countries:' . $country);
        }
    }

    /**
     * Get country from IP (placeholder - implement with GeoIP service)
     */
    protected function getCountryFromIP(string $ip): ?string
    {
        // Placeholder - integrate with MaxMind GeoIP or similar service
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return 'localhost';
        }
        
        return null; // Return actual country code in production
    }

    /**
     * Track response times
     */
    protected function trackResponseTime(string $page, float $responseTime): void
    {
        $cacheKey = "analytics:response_times:{$page}";
        $times = Cache::get($cacheKey, []);
        
        $times[] = $responseTime;
        
        // Keep only last 100 response times
        if (count($times) > 100) {
            $times = array_slice($times, -100);
        }
        
        Cache::put($cacheKey, $times, now()->addHour());
        
        // Track average response time
        $average = array_sum($times) / count($times);
        Cache::put("analytics:avg_response_time:{$page}", $average, now()->addHour());
    }

    /**
     * Track error information
     */
    protected function trackError(Request $request, Response $response, int $statusCode): void
    {
        $this->incrementMetric('errors');
        $this->incrementMetric('errors:' . $statusCode);
        
        $errorData = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'status_code' => $statusCode,
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ];

        // Store recent errors
        $errorsKey = 'analytics:recent_errors';
        $errors = Cache::get($errorsKey, []);
        $errors[] = $errorData;
        
        // Keep only last 50 errors
        if (count($errors) > 50) {
            $errors = array_slice($errors, -50);
        }
        
        Cache::put($errorsKey, $errors, now()->addHour());
    }

    /**
     * Track bounce rate data
     */
    protected function trackBounceData(Request $request): void
    {
        $sessionId = $request->session()->getId();
        $sessionKey = 'analytics:session:' . $sessionId;
        $sessionData = Cache::get($sessionKey, []);

        if ($sessionData) {
            $sessionData['page_count'] = ($sessionData['page_count'] ?? 0) + 1;
            $sessionData['last_activity'] = now();
            
            Cache::put($sessionKey, $sessionData, now()->addMinutes(30));
            
            // Track session duration
            if (isset($sessionData['start_time'])) {
                $duration = now()->diffInMinutes($sessionData['start_time']);
                $this->trackSessionDuration($duration);
            }
        }
    }

    /**
     * Track session duration
     */
    protected function trackSessionDuration(int $duration): void
    {
        $durationsKey = 'analytics:session_durations';
        $durations = Cache::get($durationsKey, []);
        
        $durations[] = $duration;
        
        // Keep only last 100 durations
        if (count($durations) > 100) {
            $durations = array_slice($durations, -100);
        }
        
        Cache::put($durationsKey, $durations, now()->addHour());
        
        // Update average session duration
        $average = array_sum($durations) / count($durations);
        Cache::put('analytics:avg_session_duration', $average, now()->addHour());
    }

    /**
     * Check if analytics scripts should be added
     */
    protected function shouldAddAnalyticsScripts(Response $response): bool
    {
        return $response->headers->get('Content-Type') === 'text/html; charset=UTF-8'
            && str_contains($response->getContent(), '</body>');
    }

    /**
     * Add analytics scripts to response
     */
    protected function addAnalyticsScripts(Request $request, Response $response): void
    {
        $scripts = $this->generateAnalyticsScripts($request);
        
        $content = $response->getContent();
        $content = str_replace('</body>', $scripts . '</body>', $content);
        
        $response->setContent($content);
    }

    /**
     * Generate analytics scripts
     */
    protected function generateAnalyticsScripts(Request $request): string
    {
        $scripts = '';

        // Google Analytics
        if (config('analytics.providers.google_analytics.enabled')) {
            $trackingId = config('analytics.providers.google_analytics.tracking_id');
            $scripts .= $this->getGoogleAnalyticsScript($trackingId);
        }

        // Google Tag Manager
        if (config('analytics.providers.google_tag_manager.enabled')) {
            $containerId = config('analytics.providers.google_tag_manager.container_id');
            $scripts .= $this->getGoogleTagManagerScript($containerId);
        }

        // Custom analytics events
        $scripts .= $this->getCustomAnalyticsScript($request);

        return $scripts;
    }

    /**
     * Get Google Analytics script
     */
    protected function getGoogleAnalyticsScript(string $trackingId): string
    {
        if (!$trackingId) {
            return '';
        }

        return "
        <!-- Google Analytics -->
        <script async src=\"https://www.googletagmanager.com/gtag/js?id={$trackingId}\"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{$trackingId}', {
                anonymize_ip: " . (config('analytics.providers.google_analytics.config.anonymize_ip') ? 'true' : 'false') . "
            });
        </script>
        ";
    }

    /**
     * Get Google Tag Manager script
     */
    protected function getGoogleTagManagerScript(string $containerId): string
    {
        if (!$containerId) {
            return '';
        }

        return "
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','{$containerId}');</script>
        ";
    }

    /**
     * Get custom analytics script
     */
    protected function getCustomAnalyticsScript(Request $request): string
    {
        $page = $this->getPageIdentifier($request);
        
        return "
        <script>
            // Custom analytics tracking
            (function() {
                // Track page view
                if (typeof gtag === 'function') {
                    gtag('event', 'page_view', {
                        page_title: document.title,
                        page_location: window.location.href,
                        page_path: window.location.pathname,
                        custom_page_id: '{$page}'
                    });
                }

                // Track scroll depth
                let maxScroll = 0;
                window.addEventListener('scroll', function() {
                    const scrollPercent = Math.round((window.scrollY / (document.body.scrollHeight - window.innerHeight)) * 100);
                    if (scrollPercent > maxScroll) {
                        maxScroll = scrollPercent;
                        if (maxScroll % 25 === 0 && typeof gtag === 'function') {
                            gtag('event', 'scroll', {
                                event_category: 'engagement',
                                value: maxScroll
                            });
                        }
                    }
                });

                // Track time on page
                let startTime = Date.now();
                window.addEventListener('beforeunload', function() {
                    const timeOnPage = Math.round((Date.now() - startTime) / 1000);
                    if (typeof gtag === 'function') {
                        gtag('event', 'timing_complete', {
                            name: 'time_on_page',
                            value: timeOnPage
                        });
                    }
                });
            })();
        </script>
        ";
    }

    /**
     * Log analytics event
     */
    protected function logAnalyticsEvent(Request $request, Response $response, float $responseTime): void
    {
        if (config('analytics.debugging.log_events', false)) {
            Log::info('Analytics Event', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'status_code' => $response->getStatusCode(),
                'response_time' => $responseTime,
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip(),
                'page_id' => $this->getPageIdentifier($request),
            ]);
        }
    }
}