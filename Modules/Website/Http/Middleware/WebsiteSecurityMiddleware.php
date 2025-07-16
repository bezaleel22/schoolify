<?php

namespace Modules\Website\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class WebsiteSecurityMiddleware
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
        // Security headers
        $this->addSecurityHeaders($request);
        
        // CSRF protection for state-changing requests
        $this->verifyCsrfToken($request);
        
        // Rate limiting
        if ($this->isRateLimited($request)) {
            return $this->rateLimitExceeded($request);
        }
        
        // Input validation and sanitization
        $this->validateAndSanitizeInput($request);
        
        // Content Security Policy
        $this->enforceContentSecurityPolicy($request);
        
        // SQL injection protection
        $this->detectSqlInjectionAttempts($request);
        
        // XSS protection
        $this->detectXssAttempts($request);
        
        $response = $next($request);
        
        // Add security headers to response
        $this->addResponseSecurityHeaders($response);
        
        // Log security events
        $this->logSecurityEvent($request, $response);
        
        return $response;
    }

    /**
     * Add security headers to the request
     */
    protected function addSecurityHeaders(Request $request): void
    {
        if (config('website.security.force_https', true) && !$request->secure()) {
            // Force HTTPS redirect
            $secureUrl = $request->fullUrlWithQuery([]);
            $secureUrl = str_replace('http://', 'https://', $secureUrl);
            
            abort(redirect($secureUrl, 301));
        }
    }

    /**
     * Verify CSRF token for state-changing requests
     */
    protected function verifyCsrfToken(Request $request): void
    {
        if (config('website.security.csrf_enabled', true)) {
            $stateMethods = ['POST', 'PUT', 'PATCH', 'DELETE'];
            
            if (in_array($request->method(), $stateMethods)) {
                $token = $request->header('X-CSRF-TOKEN') ?? $request->input('_token');
                
                if (!$token || !hash_equals(csrf_token(), $token)) {
                    Log::warning('CSRF token mismatch', [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'url' => $request->fullUrl(),
                    ]);
                    
                    abort(419, 'CSRF token mismatch');
                }
            }
        }
    }

    /**
     * Check if request is rate limited
     */
    protected function isRateLimited(Request $request): bool
    {
        if (!config('website.security.rate_limiting.enabled', true)) {
            return false;
        }

        $key = $this->getRateLimitKey($request);
        $maxAttempts = $this->getRateLimitMaxAttempts($request);
        $decayMinutes = $this->getRateLimitDecayMinutes($request);

        return RateLimiter::tooManyAttempts($key, $maxAttempts);
    }

    /**
     * Handle rate limit exceeded
     */
    protected function rateLimitExceeded(Request $request): Response
    {
        $key = $this->getRateLimitKey($request);
        $retryAfter = RateLimiter::availableIn($key);

        Log::warning('Rate limit exceeded', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'retry_after' => $retryAfter,
        ]);

        return response()->json([
            'message' => 'Too many requests. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429)->header('Retry-After', $retryAfter);
    }

    /**
     * Get rate limit key for the request
     */
    protected function getRateLimitKey(Request $request): string
    {
        return 'website_security:' . $request->ip() . ':' . $request->path();
    }

    /**
     * Get max attempts for rate limiting
     */
    protected function getRateLimitMaxAttempts(Request $request): int
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';

        // Different limits for different endpoints
        if (str_contains($routeName, 'contact')) {
            return config('website.security.rate_limiting.contact_form', 5);
        }
        
        if (str_contains($routeName, 'newsletter')) {
            return config('website.security.rate_limiting.newsletter', 3);
        }
        
        if (str_contains($routeName, 'search')) {
            return config('website.security.rate_limiting.search', 30);
        }

        return config('website.security.rate_limiting.default', 60);
    }

    /**
     * Get decay minutes for rate limiting
     */
    protected function getRateLimitDecayMinutes(Request $request): int
    {
        return 1; // 1 minute decay window
    }

    /**
     * Validate and sanitize input
     */
    protected function validateAndSanitizeInput(Request $request): void
    {
        $input = $request->all();
        
        foreach ($input as $key => $value) {
            if (is_string($value)) {
                // Check for suspicious patterns
                if ($this->containsSuspiciousPatterns($value)) {
                    Log::warning('Suspicious input detected', [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'field' => $key,
                        'value' => substr($value, 0, 100),
                    ]);
                }
                
                // Sanitize input
                $request->merge([$key => $this->sanitizeInput($value)]);
            }
        }
    }

    /**
     * Check for suspicious patterns in input
     */
    protected function containsSuspiciousPatterns(string $input): bool
    {
        $suspiciousPatterns = [
            '/(<script[^>]*>.*?<\/script>)/is', // Script tags
            '/(javascript:)/i', // JavaScript protocol
            '/(on\w+\s*=)/i', // Event handlers
            '/(\bUNION\b.*\bSELECT\b)/i', // SQL injection
            '/(\bDROP\b.*\bTABLE\b)/i', // SQL injection
            '/(\bINSERT\b.*\bINTO\b)/i', // SQL injection
            '/(\bDELETE\b.*\bFROM\b)/i', // SQL injection
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sanitize input string
     */
    protected function sanitizeInput(string $input): string
    {
        // Remove potential XSS vectors
        $input = strip_tags($input, '<b><i><u><strong><em><p><br><ul><ol><li>');
        
        // Encode special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return trim($input);
    }

    /**
     * Enforce Content Security Policy
     */
    protected function enforceContentSecurityPolicy(Request $request): void
    {
        if (config('website.security.csp_enabled', true)) {
            $nonce = base64_encode(random_bytes(16));
            $request->attributes->set('csp_nonce', $nonce);
        }
    }

    /**
     * Detect SQL injection attempts
     */
    protected function detectSqlInjectionAttempts(Request $request): void
    {
        $sqlPatterns = [
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
            '/(\'|(\\|\/)|(;|--|\|))/i',
            '/(\*|%|\+|=)/i',
        ];

        $queryString = $request->getQueryString();
        if ($queryString) {
            foreach ($sqlPatterns as $pattern) {
                if (preg_match($pattern, $queryString)) {
                    Log::alert('SQL injection attempt detected', [
                        'ip' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'query_string' => $queryString,
                        'url' => $request->fullUrl(),
                    ]);
                    
                    abort(403, 'Forbidden');
                }
            }
        }
    }

    /**
     * Detect XSS attempts
     */
    protected function detectXssAttempts(Request $request): void
    {
        $xssPatterns = [
            '/<script[^>]*>.*?<\/script>/is',
            '/<iframe[^>]*>.*?<\/iframe>/is',
            '/<object[^>]*>.*?<\/object>/is',
            '/<embed[^>]*>/is',
            '/javascript:/i',
            '/vbscript:/i',
            '/on\w+\s*=/i',
        ];

        $allInput = json_encode($request->all());
        
        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $allInput)) {
                Log::alert('XSS attempt detected', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'input' => substr($allInput, 0, 500),
                    'url' => $request->fullUrl(),
                ]);
                
                abort(403, 'Forbidden');
            }
        }
    }

    /**
     * Add security headers to response
     */
    protected function addResponseSecurityHeaders(Response $response): void
    {
        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
        ];

        if (config('website.security.hsts_enabled', true)) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        if (config('website.security.csp_enabled', true)) {
            $csp = "default-src 'self'; " .
                   "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; " .
                   "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
                   "font-src 'self' https://fonts.gstatic.com; " .
                   "img-src 'self' data: https:; " .
                   "connect-src 'self'; " .
                   "frame-ancestors 'none';";
                   
            $headers['Content-Security-Policy'] = $csp;
        }

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }
    }

    /**
     * Log security events
     */
    protected function logSecurityEvent(Request $request, Response $response): void
    {
        // Log suspicious activity
        if ($response->getStatusCode() >= 400) {
            Log::info('Security middleware response', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'status_code' => $response->getStatusCode(),
                'response_time' => microtime(true) - LARAVEL_START,
            ]);
        }
    }
}