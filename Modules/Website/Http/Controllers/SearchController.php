<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\WebsitePage;
use Modules\Website\Entities\Event;
use Modules\Website\Entities\StaffMember;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    protected $seoService;
    protected $analyticsService;

    public function __construct(
        SEOService $seoService,
        AnalyticsService $analyticsService
    ) {
        $this->seoService = $seoService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display advanced search page
     */
    public function index()
    {
        try {
            // Get search categories
            $categories = Cache::remember('search.categories', 3600, function () {
                return [
                    'all' => 'All Content',
                    'pages' => 'Pages',
                    'blog' => 'Blog Posts',
                    'events' => 'Events',
                    'staff' => 'Staff',
                    'gallery' => 'Gallery'
                ];
            });

            // Get popular search terms
            $popularTerms = $this->getPopularSearchTerms();

            // Track page view
            $this->analyticsService->trackPageView('advanced_search');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Advanced Search',
                'description' => 'Use our advanced search to find specific content across our website.',
                'type' => 'page',
                'robots' => 'noindex,follow'
            ]);

            return view('website::pages.search.advanced', compact('categories', 'popularTerms'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Perform advanced search
     */
    public function search(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2|max:100',
                'category' => 'nullable|string|in:all,pages,blog,events,staff,gallery',
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'sort_by' => 'nullable|string|in:relevance,date_desc,date_asc,title',
                'per_page' => 'nullable|integer|min:5|max:50'
            ]);

            $query = $request->get('query');
            $category = $request->get('category', 'all');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $sortBy = $request->get('sort_by', 'relevance');
            $perPage = $request->get('per_page', 15);

            $results = $this->performSearch($query, $category, $dateFrom, $dateTo, $sortBy, $perPage);

            // Store search for analytics
            $this->storeSearchAnalytics($query, $category, $results['total']);

            // Track search
            $this->analyticsService->trackSearch($query, $results['total']);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => "Search Results for: {$query}",
                'description' => "Search results for '{$query}' on our website.",
                'type' => 'page',
                'robots' => 'noindex,follow'
            ]);

            return view('website::pages.search.results', compact('results', 'query', 'category', 'sortBy'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function suggestions(Request $request)
    {
        try {
            $query = $request->get('q');
            $limit = $request->get('limit', 8);

            if (strlen($query) < 2) {
                return response()->json(['suggestions' => []]);
            }

            $suggestions = Cache::remember(
                'search.suggestions.' . md5($query . $limit),
                300, // 5 minutes
                function () use ($query, $limit) {
                    return $this->generateSuggestions($query, $limit);
                }
            );

            return response()->json([
                'success' => true,
                'suggestions' => $suggestions
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get popular search queries
     */
    public function popularQueries()
    {
        try {
            $popular = $this->getPopularSearchTerms(10);

            return response()->json([
                'success' => true,
                'queries' => $popular
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Perform the actual search across different content types
     */
    private function performSearch($query, $category, $dateFrom, $dateTo, $sortBy, $perPage)
    {
        $results = collect();
        $total = 0;

        if ($category === 'all' || $category === 'pages') {
            $pages = $this->searchPages($query, $dateFrom, $dateTo);
            $results = $results->merge($pages);
            $total += $pages->count();
        }

        if ($category === 'all' || $category === 'blog') {
            $posts = $this->searchBlogPosts($query, $dateFrom, $dateTo);
            $results = $results->merge($posts);
            $total += $posts->count();
        }

        if ($category === 'all' || $category === 'events') {
            $events = $this->searchEvents($query, $dateFrom, $dateTo);
            $results = $results->merge($events);
            $total += $events->count();
        }

        if ($category === 'all' || $category === 'staff') {
            $staff = $this->searchStaff($query);
            $results = $results->merge($staff);
            $total += $staff->count();
        }

        // Sort results
        $results = $this->sortResults($results, $sortBy);

        // Paginate results
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedResults = $results->slice($offset, $perPage);

        return [
            'results' => $paginatedResults->values(),
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => ceil($total / $perPage),
            'query' => $query,
            'category' => $category
        ];
    }

    /**
     * Search in pages
     */
    private function searchPages($query, $dateFrom, $dateTo)
    {
        $pages = WebsitePage::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%");
            });

        if ($dateFrom) {
            $pages->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $pages->whereDate('created_at', '<=', $dateTo);
        }

        return $pages->get()->map(function ($page) use ($query) {
            return [
                'type' => 'page',
                'id' => $page->id,
                'title' => $page->title,
                'excerpt' => $this->generateExcerpt($page->content, $query),
                'url' => route('website.page', $page->slug),
                'date' => $page->created_at,
                'relevance' => $this->calculateRelevance($query, $page->title . ' ' . $page->content)
            ];
        });
    }

    /**
     * Search in blog posts
     */
    private function searchBlogPosts($query, $dateFrom, $dateTo)
    {
        $posts = BlogPost::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('excerpt', 'like', "%{$query}%")
                  ->orWhere('tags', 'like', "%{$query}%");
            });

        if ($dateFrom) {
            $posts->whereDate('published_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $posts->whereDate('published_at', '<=', $dateTo);
        }

        return $posts->with('category')->get()->map(function ($post) use ($query) {
            return [
                'type' => 'blog',
                'id' => $post->id,
                'title' => $post->title,
                'excerpt' => $this->generateExcerpt($post->content, $query),
                'url' => route('website.blog.show', $post->slug),
                'date' => $post->published_at,
                'category' => $post->category->name ?? null,
                'relevance' => $this->calculateRelevance($query, $post->title . ' ' . $post->content)
            ];
        });
    }

    /**
     * Search in events
     */
    private function searchEvents($query, $dateFrom, $dateTo)
    {
        $events = Event::where('status', 'published')
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('location', 'like', "%{$query}%");
            });

        if ($dateFrom) {
            $events->whereDate('start_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $events->whereDate('start_date', '<=', $dateTo);
        }

        return $events->get()->map(function ($event) use ($query) {
            return [
                'type' => 'event',
                'id' => $event->id,
                'title' => $event->title,
                'excerpt' => $this->generateExcerpt($event->description, $query),
                'url' => route('website.events.show', $event->slug),
                'date' => $event->start_date,
                'location' => $event->location,
                'relevance' => $this->calculateRelevance($query, $event->title . ' ' . $event->description)
            ];
        });
    }

    /**
     * Search in staff
     */
    private function searchStaff($query)
    {
        $staff = StaffMember::where('status', 'active')
            ->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('position', 'like', "%{$query}%")
                  ->orWhere('department', 'like', "%{$query}%")
                  ->orWhere('bio', 'like', "%{$query}%");
            });

        return $staff->get()->map(function ($member) use ($query) {
            return [
                'type' => 'staff',
                'id' => $member->id,
                'title' => $member->full_name,
                'excerpt' => $member->position . ($member->department ? ' - ' . $member->department : ''),
                'url' => route('website.staff.show', $member->id),
                'date' => $member->created_at,
                'position' => $member->position,
                'department' => $member->department,
                'relevance' => $this->calculateRelevance($query, $member->full_name . ' ' . $member->position . ' ' . $member->bio)
            ];
        });
    }

    /**
     * Generate suggestions for autocomplete
     */
    private function generateSuggestions($query, $limit)
    {
        $suggestions = collect();

        // Get title matches
        $titleMatches = collect()
            ->merge(BlogPost::where('status', 'published')->where('title', 'like', "%{$query}%")->pluck('title'))
            ->merge(WebsitePage::where('status', 'published')->where('title', 'like', "%{$query}%")->pluck('title'))
            ->merge(Event::where('status', 'published')->where('title', 'like', "%{$query}%")->pluck('title'))
            ->unique()
            ->take($limit / 2);

        $suggestions = $suggestions->merge($titleMatches);

        // Get popular search terms that match
        $popularMatches = $this->getPopularSearchTerms()
            ->filter(function ($term) use ($query) {
                return stripos($term, $query) !== false;
            })
            ->take($limit - $suggestions->count());

        $suggestions = $suggestions->merge($popularMatches);

        return $suggestions->unique()->values()->take($limit);
    }

    /**
     * Get popular search terms
     */
    private function getPopularSearchTerms($limit = 5)
    {
        return Cache::remember('search.popular_terms', 3600, function () use ($limit) {
            // This would typically come from a search analytics table
            // For now, return some default popular terms
            return collect([
                'admission',
                'academic programs',
                'contact',
                'events',
                'staff directory',
                'school news',
                'calendar',
                'student life'
            ])->take($limit);
        });
    }

    /**
     * Calculate relevance score for search results
     */
    private function calculateRelevance($query, $content)
    {
        $query = strtolower($query);
        $content = strtolower($content);
        
        $titleMatch = stripos($content, $query) !== false ? 100 : 0;
        $wordCount = substr_count($content, $query);
        
        return $titleMatch + ($wordCount * 10);
    }

    /**
     * Generate excerpt with highlighted search term
     */
    private function generateExcerpt($content, $query, $length = 200)
    {
        $content = strip_tags($content);
        $pos = stripos($content, $query);
        
        if ($pos !== false) {
            $start = max(0, $pos - 50);
            $excerpt = substr($content, $start, $length);
            
            // Highlight the search term
            $excerpt = preg_replace('/(' . preg_quote($query, '/') . ')/i', '<mark>$1</mark>', $excerpt);
            
            return $excerpt . '...';
        }
        
        return substr($content, 0, $length) . '...';
    }

    /**
     * Sort search results
     */
    private function sortResults($results, $sortBy)
    {
        switch ($sortBy) {
            case 'date_desc':
                return $results->sortByDesc('date');
            case 'date_asc':
                return $results->sortBy('date');
            case 'title':
                return $results->sortBy('title');
            default: // relevance
                return $results->sortByDesc('relevance');
        }
    }

    /**
     * Store search analytics
     */
    private function storeSearchAnalytics($query, $category, $resultsCount)
    {
        // This could be expanded to store in a dedicated search analytics table
        Cache::increment('search.query.' . md5($query));
        Cache::increment('search.category.' . $category);
    }
}