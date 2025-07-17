<?php

namespace Modules\Website\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class SEOMiddleware
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
        $response = $next($request);
        
        // Only process HTML responses
        if ($this->isHtmlResponse($response)) {
            $this->processResponse($request, $response);
        }
        
        return $response;
    }

    /**
     * Check if response is HTML
     */
    protected function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }

    /**
     * Process the response for SEO optimization
     */
    protected function processResponse(Request $request, Response $response): void
    {
        $content = $response->getContent();
        
        if (!$content) {
            return;
        }

        // Add SEO meta tags
        $content = $this->addSEOMetaTags($request, $content);
        
        // Add structured data
        $content = $this->addStructuredData($request, $content);
        
        // Add Open Graph tags
        $content = $this->addOpenGraphTags($request, $content);
        
        // Add Twitter Card tags
        $content = $this->addTwitterCardTags($request, $content);
        
        // Optimize images
        $content = $this->optimizeImages($content);
        
        // Add canonical URL
        $content = $this->addCanonicalURL($request, $content);
        
        // Add hreflang tags (if multilingual)
        $content = $this->addHreflangTags($request, $content);
        
        // Minify HTML if enabled
        if (config('seo.performance.minify_html', false)) {
            $content = $this->minifyHTML($content);
        }
        
        $response->setContent($content);
    }

    /**
     * Add SEO meta tags
     */
    protected function addSEOMetaTags(Request $request, string $content): string
    {
        $seoData = $this->getSEOData($request);
        
        $metaTags = $this->generateMetaTags($seoData);
        
        // Insert meta tags before closing head tag
        return str_replace('</head>', $metaTags . '</head>', $content);
    }

    /**
     * Get SEO data for the current request
     */
    protected function getSEOData(Request $request): array
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        
        // Default SEO data
        $seoData = [
            'title' => config('seo.defaults.title'),
            'description' => config('seo.defaults.description'),
            'keywords' => config('seo.defaults.keywords'),
            'author' => config('seo.defaults.author'),
            'robots' => config('seo.defaults.robots'),
            'canonical' => $request->url(),
            'image' => config('seo.site.url') . config('seo.open_graph.image.default'),
        ];

        // Try to get SEO data from view data
        if (View::hasSection('seo')) {
            $viewSeoData = View::getSection('seo');
            if (is_array($viewSeoData)) {
                $seoData = array_merge($seoData, $viewSeoData);
            }
        }

        // Get route-specific SEO data
        $routeSeoData = $this->getRouteSEOData($routeName, $request);
        if ($routeSeoData) {
            $seoData = array_merge($seoData, $routeSeoData);
        }

        return $seoData;
    }

    /**
     * Get route-specific SEO data
     */
    protected function getRouteSEOData(string $routeName, Request $request): ?array
    {
        // Cache route SEO data
        $cacheKey = 'seo:route:' . $routeName . ':' . md5($request->fullUrl());
        
        return Cache::remember($cacheKey, 3600, function () use ($routeName, $request) {
            // Handle different route types
            if (str_starts_with($routeName, 'website.blog.show')) {
                return $this->getBlogPostSEO($request);
            } elseif (str_starts_with($routeName, 'website.events.show')) {
                return $this->getEventSEO($request);
            } elseif (str_starts_with($routeName, 'website.page')) {
                return $this->getPageSEO($request);
            } elseif (str_starts_with($routeName, 'website.staff.show')) {
                return $this->getStaffSEO($request);
            }
            
            return null;
        });
    }

    /**
     * Get blog post SEO data
     */
    protected function getBlogPostSEO(Request $request): array
    {
        $slug = $request->route('slug');
        
        // In a real implementation, you'd fetch this from the database
        // This is a placeholder for the structure
        return [
            'title' => 'Blog Post Title - ' . config('app.name'),
            'description' => 'Blog post excerpt or custom meta description',
            'keywords' => 'blog, education, school, article',
            'image' => config('seo.site.url') . '/images/blog/featured-image.jpg',
            'type' => 'article',
            'published_time' => now()->toISOString(),
            'modified_time' => now()->toISOString(),
            'author' => 'Author Name',
            'section' => 'Education',
        ];
    }

    /**
     * Get event SEO data
     */
    protected function getEventSEO(Request $request): array
    {
        $slug = $request->route('slug');
        
        return [
            'title' => 'Event Title - ' . config('app.name'),
            'description' => 'Event description and details',
            'keywords' => 'event, school, community, education',
            'image' => config('seo.site.url') . '/images/events/featured-image.jpg',
            'type' => 'event',
        ];
    }

    /**
     * Get page SEO data
     */
    protected function getPageSEO(Request $request): array
    {
        $slug = $request->route('slug');
        
        return [
            'title' => 'Page Title - ' . config('app.name'),
            'description' => 'Page description',
            'keywords' => 'page, information, school',
            'image' => config('seo.site.url') . '/images/pages/featured-image.jpg',
        ];
    }

    /**
     * Get staff member SEO data
     */
    protected function getStaffSEO(Request $request): array
    {
        $id = $request->route('id');
        
        return [
            'title' => 'Staff Member Name - ' . config('app.name'),
            'description' => 'Staff member bio and information',
            'keywords' => 'staff, teacher, faculty, education',
            'image' => config('seo.site.url') . '/images/staff/photo.jpg',
            'type' => 'profile',
        ];
    }

    /**
     * Generate meta tags HTML
     */
    protected function generateMetaTags(array $seoData): string
    {
        $tags = [
            '<meta charset="' . config('seo.meta_tags.charset', 'UTF-8') . '">',
            '<meta name="viewport" content="' . config('seo.meta_tags.viewport', 'width=device-width, initial-scale=1.0') . '">',
        ];

        // Basic meta tags
        if (!empty($seoData['title'])) {
            $tags[] = '<title>' . htmlspecialchars($seoData['title']) . '</title>';
        }

        if (!empty($seoData['description'])) {
            $tags[] = '<meta name="description" content="' . htmlspecialchars($seoData['description']) . '">';
        }

        if (!empty($seoData['keywords'])) {
            $tags[] = '<meta name="keywords" content="' . htmlspecialchars($seoData['keywords']) . '">';
        }

        if (!empty($seoData['author'])) {
            $tags[] = '<meta name="author" content="' . htmlspecialchars($seoData['author']) . '">';
        }

        if (!empty($seoData['robots'])) {
            $tags[] = '<meta name="robots" content="' . htmlspecialchars($seoData['robots']) . '">';
        }

        // Generator tag
        $tags[] = '<meta name="generator" content="' . config('seo.meta_tags.generator', 'Laravel') . '">';

        // Theme color
        if (config('seo.meta_tags.theme-color')) {
            $tags[] = '<meta name="theme-color" content="' . config('seo.meta_tags.theme-color') . '">';
        }

        // Site verification tags
        $verifications = config('seo.verification', []);
        foreach ($verifications as $service => $code) {
            if ($code) {
                $tags[] = '<meta name="' . $service . '-site-verification" content="' . $code . '">';
            }
        }

        return implode("\n", $tags) . "\n";
    }

    /**
     * Add structured data (JSON-LD)
     */
    protected function addStructuredData(Request $request, string $content): string
    {
        if (!config('seo.json_ld.enabled', true)) {
            return $content;
        }

        $structuredData = $this->generateStructuredData($request);
        
        if ($structuredData) {
            $jsonLd = '<script type="application/ld+json">' . json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>';
            $content = str_replace('</head>', $jsonLd . "\n</head>", $content);
        }

        return $content;
    }

    /**
     * Generate structured data
     */
    protected function generateStructuredData(Request $request): array
    {
        $route = $request->route();
        $routeName = $route ? $route->getName() : '';
        
        $structuredData = [];

        // Organization data
        if (config('seo.json_ld.organization')) {
            $structuredData[] = config('seo.json_ld.organization');
        }

        // Website data
        if (config('seo.json_ld.website')) {
            $structuredData[] = config('seo.json_ld.website');
        }

        // Add route-specific structured data
        if (str_starts_with($routeName, 'website.blog.show')) {
            $structuredData[] = $this->getBlogPostStructuredData($request);
        } elseif (str_starts_with($routeName, 'website.events.show')) {
            $structuredData[] = $this->getEventStructuredData($request);
        }

        // Add breadcrumbs
        if (config('seo.structured_data.breadcrumbs', true)) {
            $breadcrumbs = $this->generateBreadcrumbs($request);
            if ($breadcrumbs) {
                $structuredData[] = $breadcrumbs;
            }
        }

        return count($structuredData) === 1 ? $structuredData[0] : $structuredData;
    }

    /**
     * Get blog post structured data
     */
    protected function getBlogPostStructuredData(Request $request): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => 'Blog Post Title',
            'description' => 'Blog post description',
            'image' => config('seo.site.url') . '/images/blog/featured.jpg',
            'author' => [
                '@type' => 'Person',
                'name' => 'Author Name'
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => config('seo.site.name'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => config('seo.site.url') . config('seo.site.logo')
                ]
            ],
            'datePublished' => now()->toISOString(),
            'dateModified' => now()->toISOString(),
        ];
    }

    /**
     * Get event structured data
     */
    protected function getEventStructuredData(Request $request): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Event',
            'name' => 'Event Name',
            'description' => 'Event description',
            'startDate' => now()->addDays(7)->toISOString(),
            'endDate' => now()->addDays(7)->addHours(2)->toISOString(),
            'location' => [
                '@type' => 'Place',
                'name' => config('seo.site.name'),
                'address' => 'School Address'
            ],
            'organizer' => [
                '@type' => 'Organization',
                'name' => config('seo.site.name'),
                'url' => config('seo.site.url')
            ]
        ];
    }

    /**
     * Generate breadcrumbs structured data
     */
    protected function generateBreadcrumbs(Request $request): ?array
    {
        $segments = $request->segments();
        
        if (empty($segments)) {
            return null;
        }

        $breadcrumbs = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        $url = config('seo.site.url');
        $position = 1;

        // Add home
        $breadcrumbs['itemListElement'][] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Home',
            'item' => $url
        ];

        // Add segments
        foreach ($segments as $segment) {
            $url .= '/' . $segment;
            $name = ucfirst(str_replace('-', ' ', $segment));
            
            $breadcrumbs['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $name,
                'item' => $url
            ];
        }

        return $breadcrumbs;
    }

    /**
     * Add Open Graph tags
     */
    protected function addOpenGraphTags(Request $request, string $content): string
    {
        if (!config('seo.open_graph.enabled', true)) {
            return $content;
        }

        $seoData = $this->getSEOData($request);
        $ogTags = $this->generateOpenGraphTags($seoData);
        
        return str_replace('</head>', $ogTags . '</head>', $content);
    }

    /**
     * Generate Open Graph tags
     */
    protected function generateOpenGraphTags(array $seoData): string
    {
        $tags = [];

        $tags[] = '<meta property="og:site_name" content="' . htmlspecialchars(config('seo.site.name')) . '">';
        $tags[] = '<meta property="og:type" content="' . ($seoData['type'] ?? 'website') . '">';
        $tags[] = '<meta property="og:url" content="' . htmlspecialchars($seoData['canonical']) . '">';

        if (!empty($seoData['title'])) {
            $tags[] = '<meta property="og:title" content="' . htmlspecialchars($seoData['title']) . '">';
        }

        if (!empty($seoData['description'])) {
            $tags[] = '<meta property="og:description" content="' . htmlspecialchars($seoData['description']) . '">';
        }

        if (!empty($seoData['image'])) {
            $tags[] = '<meta property="og:image" content="' . htmlspecialchars($seoData['image']) . '">';
            $tags[] = '<meta property="og:image:width" content="' . config('seo.open_graph.image.width', 1200) . '">';
            $tags[] = '<meta property="og:image:height" content="' . config('seo.open_graph.image.height', 630) . '">';
        }

        // Article-specific tags
        if (($seoData['type'] ?? '') === 'article') {
            if (!empty($seoData['published_time'])) {
                $tags[] = '<meta property="article:published_time" content="' . $seoData['published_time'] . '">';
            }
            if (!empty($seoData['modified_time'])) {
                $tags[] = '<meta property="article:modified_time" content="' . $seoData['modified_time'] . '">';
            }
            if (!empty($seoData['author'])) {
                $tags[] = '<meta property="article:author" content="' . htmlspecialchars($seoData['author']) . '">';
            }
            if (!empty($seoData['section'])) {
                $tags[] = '<meta property="article:section" content="' . htmlspecialchars($seoData['section']) . '">';
            }
        }

        return implode("\n", $tags) . "\n";
    }

    /**
     * Add Twitter Card tags
     */
    protected function addTwitterCardTags(Request $request, string $content): string
    {
        if (!config('seo.twitter.enabled', true)) {
            return $content;
        }

        $seoData = $this->getSEOData($request);
        $twitterTags = $this->generateTwitterCardTags($seoData);
        
        return str_replace('</head>', $twitterTags . '</head>', $content);
    }

    /**
     * Generate Twitter Card tags
     */
    protected function generateTwitterCardTags(array $seoData): string
    {
        $tags = [];

        $tags[] = '<meta name="twitter:card" content="' . config('seo.twitter.card', 'summary_large_image') . '">';
        
        if (config('seo.twitter.site')) {
            $tags[] = '<meta name="twitter:site" content="' . config('seo.twitter.site') . '">';
        }
        
        if (config('seo.twitter.creator')) {
            $tags[] = '<meta name="twitter:creator" content="' . config('seo.twitter.creator') . '">';
        }

        if (!empty($seoData['title'])) {
            $tags[] = '<meta name="twitter:title" content="' . htmlspecialchars($seoData['title']) . '">';
        }

        if (!empty($seoData['description'])) {
            $tags[] = '<meta name="twitter:description" content="' . htmlspecialchars($seoData['description']) . '">';
        }

        if (!empty($seoData['image'])) {
            $tags[] = '<meta name="twitter:image" content="' . htmlspecialchars($seoData['image']) . '">';
        }

        return implode("\n", $tags) . "\n";
    }

    /**
     * Optimize images for SEO
     */
    protected function optimizeImages(string $content): string
    {
        if (!config('seo.images.optimize', true)) {
            return $content;
        }

        // Add loading="lazy" to images
        $content = preg_replace('/<img(?![^>]*loading=)([^>]*)>/i', '<img$1 loading="lazy">', $content);
        
        // Ensure images have alt attributes
        $content = preg_replace('/<img(?![^>]*alt=)([^>]*)>/i', '<img$1 alt="">', $content);

        return $content;
    }

    /**
     * Add canonical URL
     */
    protected function addCanonicalURL(Request $request, string $content): string
    {
        $canonical = $request->url();
        $canonicalTag = '<link rel="canonical" href="' . htmlspecialchars($canonical) . '">';
        
        return str_replace('</head>', $canonicalTag . "\n</head>", $content);
    }

    /**
     * Add hreflang tags for multilingual sites
     */
    protected function addHreflangTags(Request $request, string $content): string
    {
        // Placeholder for multilingual support
        // In a real implementation, you'd generate hreflang tags for different language versions
        return $content;
    }

    /**
     * Minify HTML content
     */
    protected function minifyHTML(string $content): string
    {
        // Remove HTML comments (except IE conditionals)
        $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
        
        // Remove whitespace between tags
        $content = preg_replace('/>\s+</', '><', $content);
        
        // Remove leading and trailing whitespace
        $content = trim($content);
        
        return $content;
    }
}