<?php

namespace Modules\Website\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Website\Entities\WebsitePage;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\Event;
use Modules\Website\Entities\GalleryAlbum;
use Carbon\Carbon;

class GenerateSitemapCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'website:generate-sitemap 
                            {--force : Force regeneration even if cache exists}
                            {--output= : Custom output path for sitemap}
                            {--ping : Ping search engines after generation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate XML sitemap for the website module';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting sitemap generation...');

        // Check if we should skip due to cache
        if (!$this->option('force') && $this->isCacheValid()) {
            $this->info('Sitemap is up to date. Use --force to regenerate.');
            return 0;
        }

        try {
            // Generate sitemap
            $sitemap = $this->generateSitemap();
            
            // Save sitemap
            $outputPath = $this->option('output') ?: 'sitemap.xml';
            $this->saveSitemap($sitemap, $outputPath);
            
            // Update cache
            $this->updateCache();
            
            // Ping search engines if requested
            if ($this->option('ping')) {
                $this->pingSearchEngines();
            }
            
            $this->info('Sitemap generated successfully!');
            $this->info("Sitemap saved to: {$outputPath}");
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to generate sitemap: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Check if sitemap cache is valid
     */
    protected function isCacheValid(): bool
    {
        $cacheKey = 'website:sitemap:last_generated';
        $lastGenerated = Cache::get($cacheKey);
        
        if (!$lastGenerated) {
            return false;
        }
        
        $cacheExpiry = config('website.sitemap.cache_ttl', 86400); // 24 hours
        return Carbon::parse($lastGenerated)->addSeconds($cacheExpiry)->isFuture();
    }

    /**
     * Generate the complete sitemap
     */
    protected function generateSitemap(): string
    {
        $urls = [];
        
        // Add static pages
        $urls = array_merge($urls, $this->getStaticPageUrls());
        
        // Add dynamic content
        if (config('website.sitemap.include.pages', true)) {
            $urls = array_merge($urls, $this->getWebsitePageUrls());
        }
        
        if (config('website.sitemap.include.blog_posts', true)) {
            $urls = array_merge($urls, $this->getBlogPostUrls());
        }
        
        if (config('website.sitemap.include.events', true)) {
            $urls = array_merge($urls, $this->getEventUrls());
        }
        
        if (config('website.sitemap.include.gallery_albums', true)) {
            $urls = array_merge($urls, $this->getGalleryUrls());
        }
        
        // Sort URLs by priority and lastmod
        usort($urls, function ($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return strcmp($b['lastmod'], $a['lastmod']);
            }
            return $b['priority'] <=> $a['priority'];
        });
        
        // Check URL limit
        $maxUrls = config('website.sitemap.max_urls', 50000);
        if (count($urls) > $maxUrls) {
            $this->warn("URL count ({count($urls)}) exceeds limit ({$maxUrls}). Trimming...");
            $urls = array_slice($urls, 0, $maxUrls);
        }
        
        return $this->generateXml($urls);
    }

    /**
     * Get static page URLs
     */
    protected function getStaticPageUrls(): array
    {
        $baseUrl = config('app.url');
        $priority = config('website.sitemap.priority', []);
        $changefreq = config('website.sitemap.changefreq', []);
        
        return [
            [
                'loc' => $baseUrl . '/',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['homepage'] ?? 'daily',
                'priority' => $priority['homepage'] ?? 1.0,
            ],
            [
                'loc' => $baseUrl . '/about',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['pages'] ?? 'weekly',
                'priority' => $priority['pages'] ?? 0.8,
            ],
            [
                'loc' => $baseUrl . '/admissions',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['pages'] ?? 'weekly',
                'priority' => $priority['pages'] ?? 0.8,
            ],
            [
                'loc' => $baseUrl . '/academics',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['pages'] ?? 'weekly',
                'priority' => $priority['pages'] ?? 0.8,
            ],
            [
                'loc' => $baseUrl . '/contact',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['pages'] ?? 'weekly',
                'priority' => $priority['pages'] ?? 0.8,
            ],
            [
                'loc' => $baseUrl . '/blog',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['blog'] ?? 'daily',
                'priority' => $priority['blog'] ?? 0.9,
            ],
            [
                'loc' => $baseUrl . '/events',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['events'] ?? 'weekly',
                'priority' => $priority['events'] ?? 0.7,
            ],
            [
                'loc' => $baseUrl . '/gallery',
                'lastmod' => now()->toISOString(),
                'changefreq' => $changefreq['gallery'] ?? 'monthly',
                'priority' => $priority['gallery'] ?? 0.5,
            ],
            [
                'loc' => $baseUrl . '/staff',
                'lastmod' => now()->toISOString(),
                'changefreq' => 'monthly',
                'priority' => 0.6,
            ],
        ];
    }

    /**
     * Get website page URLs
     */
    protected function getWebsitePageUrls(): array
    {
        $baseUrl = config('app.url');
        $urls = [];
        
        $pages = WebsitePage::where('status', 'published')
            ->where('show_in_sitemap', true)
            ->select('slug', 'updated_at', 'meta_title')
            ->get();
        
        foreach ($pages as $page) {
            $urls[] = [
                'loc' => $baseUrl . '/page/' . $page->slug,
                'lastmod' => $page->updated_at->toISOString(),
                'changefreq' => config('website.sitemap.changefreq.pages', 'weekly'),
                'priority' => config('website.sitemap.priority.pages', 0.8),
            ];
        }
        
        return $urls;
    }

    /**
     * Get blog post URLs
     */
    protected function getBlogPostUrls(): array
    {
        $baseUrl = config('app.url');
        $urls = [];
        
        $posts = BlogPost::where('status', 'published')
            ->where('published_at', '<=', now())
            ->select('slug', 'updated_at', 'title')
            ->orderBy('published_at', 'desc')
            ->limit(1000) // Reasonable limit for blog posts
            ->get();
        
        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $post->slug,
                'lastmod' => $post->updated_at->toISOString(),
                'changefreq' => config('website.sitemap.changefreq.blog_posts', 'monthly'),
                'priority' => config('website.sitemap.priority.blog_posts', 0.6),
            ];
        }
        
        return $urls;
    }

    /**
     * Get event URLs
     */
    protected function getEventUrls(): array
    {
        $baseUrl = config('app.url');
        $urls = [];
        
        $events = Event::where('status', 'published')
            ->where('start_date', '>=', now()->subMonths(6)) // Include events from last 6 months
            ->select('slug', 'updated_at', 'title')
            ->orderBy('start_date', 'desc')
            ->limit(500) // Reasonable limit for events
            ->get();
        
        foreach ($events as $event) {
            $urls[] = [
                'loc' => $baseUrl . '/events/' . $event->slug,
                'lastmod' => $event->updated_at->toISOString(),
                'changefreq' => config('website.sitemap.changefreq.events', 'weekly'),
                'priority' => config('website.sitemap.priority.events', 0.7),
            ];
        }
        
        return $urls;
    }

    /**
     * Get gallery URLs
     */
    protected function getGalleryUrls(): array
    {
        $baseUrl = config('app.url');
        $urls = [];
        
        $albums = GalleryAlbum::select('slug', 'updated_at', 'title')
            ->orderBy('updated_at', 'desc')
            ->limit(200) // Reasonable limit for gallery albums
            ->get();
        
        foreach ($albums as $album) {
            $urls[] = [
                'loc' => $baseUrl . '/gallery/album/' . $album->slug,
                'lastmod' => $album->updated_at->toISOString(),
                'changefreq' => config('website.sitemap.changefreq.gallery', 'monthly'),
                'priority' => config('website.sitemap.priority.gallery', 0.5),
            ];
        }
        
        return $urls;
    }

    /**
     * Generate XML sitemap
     */
    protected function generateXml(array $urls): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Create urlset element
        $urlset = $xml->createElement('urlset');
        $urlset->setAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
        $urlset->setAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
        $xml->appendChild($urlset);
        
        // Add URLs
        foreach ($urls as $urlData) {
            $url = $xml->createElement('url');
            
            // Location (required)
            $loc = $xml->createElement('loc', htmlspecialchars($urlData['loc']));
            $url->appendChild($loc);
            
            // Last modified
            if (isset($urlData['lastmod'])) {
                $lastmod = $xml->createElement('lastmod', $urlData['lastmod']);
                $url->appendChild($lastmod);
            }
            
            // Change frequency
            if (isset($urlData['changefreq'])) {
                $changefreq = $xml->createElement('changefreq', $urlData['changefreq']);
                $url->appendChild($changefreq);
            }
            
            // Priority
            if (isset($urlData['priority'])) {
                $priority = $xml->createElement('priority', number_format($urlData['priority'], 1));
                $url->appendChild($priority);
            }
            
            // Images (if any)
            if (isset($urlData['images'])) {
                foreach ($urlData['images'] as $imageData) {
                    $image = $xml->createElement('image:image');
                    
                    $imageLoc = $xml->createElement('image:loc', htmlspecialchars($imageData['loc']));
                    $image->appendChild($imageLoc);
                    
                    if (isset($imageData['title'])) {
                        $imageTitle = $xml->createElement('image:title', htmlspecialchars($imageData['title']));
                        $image->appendChild($imageTitle);
                    }
                    
                    if (isset($imageData['caption'])) {
                        $imageCaption = $xml->createElement('image:caption', htmlspecialchars($imageData['caption']));
                        $image->appendChild($imageCaption);
                    }
                    
                    $url->appendChild($image);
                }
            }
            
            $urlset->appendChild($url);
        }
        
        return $xml->saveXML();
    }

    /**
     * Save sitemap to file
     */
    protected function saveSitemap(string $sitemap, string $outputPath): void
    {
        // Save to public directory
        $publicPath = public_path($outputPath);
        file_put_contents($publicPath, $sitemap);
        
        // Also save to storage for backup
        Storage::disk('local')->put('sitemaps/' . $outputPath, $sitemap);
        
        $this->info("Sitemap contains " . substr_count($sitemap, '<url>') . " URLs");
    }

    /**
     * Update cache timestamp
     */
    protected function updateCache(): void
    {
        Cache::put('website:sitemap:last_generated', now()->toISOString(), 86400 * 7); // Cache for 7 days
    }

    /**
     * Ping search engines
     */
    protected function pingSearchEngines(): void
    {
        $sitemapUrl = config('app.url') . '/sitemap.xml';
        
        $searchEngines = [
            'Google' => "https://www.google.com/ping?sitemap=" . urlencode($sitemapUrl),
            'Bing' => "https://www.bing.com/ping?sitemap=" . urlencode($sitemapUrl),
        ];
        
        $this->info('Pinging search engines...');
        
        foreach ($searchEngines as $engine => $pingUrl) {
            try {
                $response = file_get_contents($pingUrl);
                $this->info("✓ Pinged {$engine}");
            } catch (\Exception $e) {
                $this->warn("✗ Failed to ping {$engine}: " . $e->getMessage());
            }
        }
    }
}