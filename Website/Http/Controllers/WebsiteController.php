<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Services\WebsiteService;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Modules\Website\Entities\WebsitePage;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\Event;
use Modules\Website\Http\Requests\SearchRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class WebsiteController extends Controller
{
    protected $websiteService;
    protected $seoService;
    protected $analyticsService;

    public function __construct(
        WebsiteService $websiteService,
        SEOService $seoService,
        AnalyticsService $analyticsService
    ) {
        $this->websiteService = $websiteService;
        $this->seoService = $seoService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the homepage with dynamic content
     */
    public function index()
    {
        try {
            $homeData = Cache::remember('website.homepage', 60 * 15, function () {
                return $this->websiteService->getHomepageData();
            });

            // Track page view
            $this->analyticsService->trackPageView('homepage');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Welcome to Our School',
                'description' => 'Discover excellence in education at our modern school with comprehensive programs and facilities.',
                'type' => 'website'
            ]);

            return view('website::pages.home', compact('homeData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a specific page by slug
     */
    public function page($slug)
    {
        try {
            $page = WebsitePage::published()
                ->where('slug', $slug)
                ->firstOrFail();

            // Increment view count
            $page->incrementViewCount();

            // Track page view
            $this->analyticsService->trackPageView('page', $page->id);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => $page->meta_title ?: $page->title,
                'description' => $page->meta_description ?: $page->excerpt,
                'keywords' => $page->meta_keywords,
                'schema' => $page->schema_markup
            ]);

            return view('website::pages.page', compact('page'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display about page with dynamic content
     */
    public function about()
    {
        try {
            $aboutData = Cache::remember('website.about', 60 * 30, function () {
                return $this->websiteService->getAboutPageData();
            });

            // Track page view
            $this->analyticsService->trackPageView('about');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'About Our School',
                'description' => 'Learn about our school\'s history, mission, and commitment to educational excellence.',
                'type' => 'page'
            ]);

            return view('website::pages.about', compact('aboutData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display admission information
     */
    public function admission()
    {
        try {
            $admissionData = Cache::remember('website.admission', 60 * 30, function () {
                return $this->websiteService->getAdmissionData();
            });

            // Track page view
            $this->analyticsService->trackPageView('admission');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'School Admission Information',
                'description' => 'Learn about our admission process, requirements, and how to enroll in our school.',
                'type' => 'page'
            ]);

            return view('website::pages.admission', compact('admissionData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display academic programs
     */
    public function academics()
    {
        try {
            $academicData = Cache::remember('website.academics', 60 * 30, function () {
                return $this->websiteService->getAcademicData();
            });

            // Track page view
            $this->analyticsService->trackPageView('academics');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Academic Programs',
                'description' => 'Explore our comprehensive academic programs designed to foster student success.',
                'type' => 'page'
            ]);

            return view('website::pages.academics', compact('academicData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display portfolio/gallery page
     */
    public function portfolio()
    {
        try {
            $portfolioData = Cache::remember('website.portfolio', 60 * 30, function () {
                return $this->websiteService->getPortfolioData();
            });

            // Track page view
            $this->analyticsService->trackPageView('portfolio');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Our Portfolio',
                'description' => 'Explore our school portfolio showcasing student achievements, projects, and memorable moments.',
                'type' => 'page'
            ]);

            return view('website::pages.portfolio', compact('portfolioData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display school news and announcements
     */
    public function news(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 12);
            $category = $request->get('category');

            $newsData = $this->websiteService->getNewsData($perPage, $category);

            // Track page view
            $this->analyticsService->trackPageView('news');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'School News & Announcements',
                'description' => 'Stay updated with the latest news, announcements, and events from our school.',
                'type' => 'page'
            ]);

            return view('website::pages.news', compact('newsData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get sitemap for SEO
     */
    public function sitemap()
    {
        try {
            $sitemap = $this->seoService->generateSitemap();
            
            return response($sitemap, 200, [
                'Content-Type' => 'application/xml'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get robots.txt
     */
    public function robots()
    {
        try {
            $robots = $this->seoService->generateRobotsTxt();
            
            return response($robots, 200, [
                'Content-Type' => 'text/plain'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search functionality
     */
    public function search(SearchRequest $request)
    {
        try {
            $query = $request->get('q');
            $type = $request->get('type', 'all'); // all, pages, blogs, events
            $perPage = $request->get('per_page', 10);

            $results = $this->websiteService->search($query, $type, $perPage);

            // Track search
            $this->analyticsService->trackSearch($query, $results['total']);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Search Results for: ' . $query,
                'description' => 'Search results for your query on our school website.',
                'robots' => 'noindex,follow'
            ]);

            return view('website::pages.search', compact('results', 'query', 'type'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API endpoint for autocomplete search
     */
    public function searchAutocomplete(Request $request)
    {
        try {
            $query = $request->get('q');
            
            if (strlen($query) < 2) {
                return response()->json(['suggestions' => []]);
            }

            $suggestions = Cache::remember(
                'search.autocomplete.' . md5($query),
                60 * 5,
                function () use ($query) {
                    return $this->websiteService->getSearchSuggestions($query);
                }
            );

            return response()->json(['suggestions' => $suggestions]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Handle 404 errors with suggestions
     */
    public function notFound()
    {
        try {
            $suggestions = Cache::remember('website.404_suggestions', 60 * 60, function () {
                return $this->websiteService->get404Suggestions();
            });

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Page Not Found',
                'description' => 'The page you are looking for could not be found.',
                'robots' => 'noindex,nofollow'
            ]);

            return response()->view('website::pages.404', compact('suggestions'), 404);
        } catch (\Exception $e) {
            return response()->view('website::pages.404', [], 404);
        }
    }

    /**
     * Display privacy policy
     */
    public function privacy()
    {
        try {
            // Track page view
            $this->analyticsService->trackPageView('privacy');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Privacy Policy',
                'description' => 'Our privacy policy outlines how we collect, use, and protect your personal information.',
                'type' => 'page'
            ]);

            return view('website::pages.privacy');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display terms of service
     */
    public function terms()
    {
        try {
            // Track page view
            $this->analyticsService->trackPageView('terms');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Terms of Service',
                'description' => 'Terms and conditions for using our school website and services.',
                'type' => 'page'
            ]);

            return view('website::pages.terms');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
