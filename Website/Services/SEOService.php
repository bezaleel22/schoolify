<?php

namespace Modules\Website\Services;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;

class SEOService
{
    /**
     * Current page SEO data
     */
    protected $seoData = [];

    /**
     * Set page SEO data
     */
    public function setPageSEO(array $data): void
    {
        $defaults = [
            'title' => config('seo.defaults.title'),
            'description' => config('seo.defaults.description'),
            'keywords' => config('seo.defaults.keywords'),
            'author' => config('seo.defaults.author'),
            'robots' => config('seo.defaults.robots'),
            'canonical' => request()->url(),
            'image' => config('seo.site.url') . config('seo.open_graph.image.default'),
        ];

        $this->seoData = array_merge($defaults, $data);

        // Share with views
        View::share('seoData', $this->seoData);
    }

    /**
     * Get SEO data
     */
    public function getSEOData(): array
    {
        return $this->seoData;
    }

    /**
     * Set page title
     */
    public function setTitle(string $title, bool $appendSiteName = true): void
    {
        if ($appendSiteName) {
            $siteName = config('seo.site.name', config('app.name'));
            $title = $title . ' - ' . $siteName;
        }

        $this->seoData['title'] = $title;
        View::share('pageTitle', $title);
    }

    /**
     * Set meta description
     */
    public function setDescription(string $description): void
    {
        $this->seoData['description'] = $description;
    }

    /**
     * Set meta keywords
     */
    public function setKeywords(string $keywords): void
    {
        $this->seoData['keywords'] = $keywords;
    }

    /**
     * Set canonical URL
     */
    public function setCanonical(string $url): void
    {
        $this->seoData['canonical'] = $url;
    }

    /**
     * Set Open Graph image
     */
    public function setImage(string $imageUrl): void
    {
        $this->seoData['image'] = $imageUrl;
    }

    /**
     * Set robots meta tag
     */
    public function setRobots(string $robots): void
    {
        $this->seoData['robots'] = $robots;
    }

    /**
     * Generate meta tags HTML
     */
    public function generateMetaTags(): string
    {
        $html = '';
        
        // Basic meta tags
        if (!empty($this->seoData['title'])) {
            $html .= '<title>' . htmlspecialchars($this->seoData['title']) . '</title>' . "\n";
        }

        if (!empty($this->seoData['description'])) {
            $html .= '<meta name="description" content="' . htmlspecialchars($this->seoData['description']) . '">' . "\n";
        }

        if (!empty($this->seoData['keywords'])) {
            $html .= '<meta name="keywords" content="' . htmlspecialchars($this->seoData['keywords']) . '">' . "\n";
        }

        if (!empty($this->seoData['author'])) {
            $html .= '<meta name="author" content="' . htmlspecialchars($this->seoData['author']) . '">' . "\n";
        }

        if (!empty($this->seoData['robots'])) {
            $html .= '<meta name="robots" content="' . htmlspecialchars($this->seoData['robots']) . '">' . "\n";
        }

        // Canonical URL
        if (!empty($this->seoData['canonical'])) {
            $html .= '<link rel="canonical" href="' . htmlspecialchars($this->seoData['canonical']) . '">' . "\n";
        }

        return $html;
    }

    /**
     * Generate Open Graph tags
     */
    public function generateOpenGraphTags(): string
    {
        $html = '';

        $html .= '<meta property="og:site_name" content="' . htmlspecialchars(config('seo.site.name', config('app.name'))) . '">' . "\n";
        $html .= '<meta property="og:type" content="' . ($this->seoData['type'] ?? 'website') . '">' . "\n";
        $html .= '<meta property="og:url" content="' . htmlspecialchars($this->seoData['canonical']) . '">' . "\n";

        if (!empty($this->seoData['title'])) {
            $html .= '<meta property="og:title" content="' . htmlspecialchars($this->seoData['title']) . '">' . "\n";
        }

        if (!empty($this->seoData['description'])) {
            $html .= '<meta property="og:description" content="' . htmlspecialchars($this->seoData['description']) . '">' . "\n";
        }

        if (!empty($this->seoData['image'])) {
            $html .= '<meta property="og:image" content="' . htmlspecialchars($this->seoData['image']) . '">' . "\n";
        }

        return $html;
    }

    /**
     * Generate Twitter Card tags
     */
    public function generateTwitterCardTags(): string
    {
        $html = '';

        $html .= '<meta name="twitter:card" content="summary_large_image">' . "\n";
        
        if (config('seo.twitter.site')) {
            $html .= '<meta name="twitter:site" content="' . config('seo.twitter.site') . '">' . "\n";
        }

        if (!empty($this->seoData['title'])) {
            $html .= '<meta name="twitter:title" content="' . htmlspecialchars($this->seoData['title']) . '">' . "\n";
        }

        if (!empty($this->seoData['description'])) {
            $html .= '<meta name="twitter:description" content="' . htmlspecialchars($this->seoData['description']) . '">' . "\n";
        }

        if (!empty($this->seoData['image'])) {
            $html .= '<meta name="twitter:image" content="' . htmlspecialchars($this->seoData['image']) . '">' . "\n";
        }

        return $html;
    }

    /**
     * Generate structured data (JSON-LD)
     */
    public function generateStructuredData(): string
    {
        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $this->seoData['title'] ?? '',
            'description' => $this->seoData['description'] ?? '',
            'url' => $this->seoData['canonical'] ?? '',
        ];

        // Add organization data
        if (config('seo.json_ld.organization')) {
            $data['publisher'] = config('seo.json_ld.organization');
        }

        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Generate breadcrumbs
     */
    public function generateBreadcrumbs(array $breadcrumbs): string
    {
        if (empty($breadcrumbs)) {
            return '';
        }

        $data = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => []
        ];

        $position = 1;
        foreach ($breadcrumbs as $breadcrumb) {
            $data['itemListElement'][] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $breadcrumb['name'],
                'item' => $breadcrumb['url'] ?? ''
            ];
        }

        return '<script type="application/ld+json">' . json_encode($data, JSON_UNESCAPED_SLASHES) . '</script>';
    }

    /**
     * Clear SEO data
     */
    public function clear(): void
    {
        $this->seoData = [];
    }

    /**
     * Cache SEO data for performance
     */
    public function cacheSEOData(string $key, array $data, int $ttl = 3600): void
    {
        Cache::put("seo:{$key}", $data, $ttl);
    }

    /**
     * Get cached SEO data
     */
    public function getCachedSEOData(string $key): ?array
    {
        return Cache::get("seo:{$key}");
    }

    /**
     * Generate sitemap XML
     */
    public function generateSitemap(): string
    {
        // This would typically be handled by the GenerateSitemapCommand
        // For now, return a basic sitemap structure
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        $xml .= '<url><loc>' . config('app.url') . '</loc><changefreq>daily</changefreq><priority>1.0</priority></url>' . "\n";
        $xml .= '</urlset>';
        
        return $xml;
    }

    /**
     * Generate robots.txt content
     */
    public function generateRobotsTxt(): string
    {
        $robots = "User-agent: *\n";
        $robots .= "Disallow: /admin\n";
        $robots .= "Disallow: /api\n";
        $robots .= "Allow: /\n";
        $robots .= "\n";
        $robots .= "Sitemap: " . config('app.url') . "/sitemap.xml\n";
        
        return $robots;
    }

    /**
     * Get blog post meta data for SEO
     */
    public function getBlogPostMeta($post): array
    {
        return [
            'title' => $post->meta_title ?: ($post->title . ' - ' . config('app.name')),
            'description' => $post->meta_description ?: $post->excerpt,
            'keywords' => $post->meta_keywords ?: implode(',', json_decode($post->tags ?? '[]', true)),
            'canonical' => route('website.blog.show', $post->slug),
            'image' => $post->featured_image ? config('app.url') . $post->featured_image : null,
            'type' => 'article',
            'published_time' => $post->published_at?->toISOString(),
            'modified_time' => $post->updated_at->toISOString(),
            'author' => $post->author_name ?? 'Admin',
            'section' => $post->category->name ?? 'Blog',
        ];
    }
}