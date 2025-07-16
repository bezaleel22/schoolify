<?php

namespace Modules\Website\Services;

use Modules\Website\Entities\WebsitePage;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\Event;
use Modules\Website\Entities\GalleryAlbum;
use Modules\Website\Entities\StaffMember;
use Modules\Website\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WebsiteService
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get homepage data with caching
     */
    public function getHomepageData()
    {
        return [
            'hero' => $this->getHeroContent(),
            'featured_posts' => $this->getFeaturedBlogPosts(3),
            'upcoming_events' => $this->getUpcomingEvents(4),
            'gallery_preview' => $this->getGalleryPreview(6),
            'staff_highlights' => $this->getFeaturedStaff(4),
            'quick_stats' => $this->getSchoolStats(),
            'testimonials' => $this->getTestimonials(),
            'latest_news' => $this->getLatestNews(3)
        ];
    }

    /**
     * Get about page data
     */
    public function getAboutPageData()
    {
        return [
            'mission' => $this->getMissionStatement(),
            'vision' => $this->getVisionStatement(),
            'history' => $this->getSchoolHistory(),
            'leadership' => $this->getLeadershipTeam(),
            'facilities' => $this->getSchoolFacilities(),
            'achievements' => $this->getSchoolAchievements(),
            'values' => $this->getSchoolValues()
        ];
    }

    /**
     * Get admission data
     */
    public function getAdmissionData()
    {
        return [
            'process' => $this->getAdmissionProcess(),
            'requirements' => $this->getAdmissionRequirements(),
            'fees' => $this->getSchoolFees(),
            'calendar' => $this->getAdmissionCalendar(),
            'forms' => $this->getAdmissionForms(),
            'contact' => $this->getAdmissionContact()
        ];
    }

    /**
     * Get academic data
     */
    public function getAcademicData()
    {
        return [
            'programs' => $this->getAcademicPrograms(),
            'curriculum' => $this->getCurriculum(),
            'departments' => $this->getDepartments(),
            'extracurricular' => $this->getExtracurricularActivities(),
            'calendar' => $this->getAcademicCalendar(),
            'resources' => $this->getAcademicResources()
        ];
    }

    /**
     * Get news data with pagination
     */
    public function getNewsData($perPage = 12, $category = null)
    {
        $query = BlogPost::published()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc');

        if ($category) {
            $query->byCategory($category);
        }

        $posts = $query->paginate($perPage);
        $categories = $this->getBlogCategories();
        $featuredPosts = $this->getFeaturedBlogPosts(3);

        return [
            'posts' => $posts,
            'categories' => $categories,
            'featured_posts' => $featuredPosts,
            'current_category' => $category
        ];
    }

    /**
     * Search functionality
     */
    public function search($query, $type = 'all', $perPage = 10)
    {
        $results = collect();
        $total = 0;

        switch ($type) {
            case 'pages':
                $pages = $this->searchPages($query, $perPage);
                $results = $pages;
                $total = $pages->total();
                break;

            case 'blogs':
                $blogs = $this->searchBlogs($query, $perPage);
                $results = $blogs;
                $total = $blogs->total();
                break;

            case 'events':
                $events = $this->searchEvents($query, $perPage);
                $results = $events;
                $total = $events->total();
                break;

            default:
                $results = $this->searchAll($query, $perPage);
                $total = $results['total'];
                break;
        }

        return [
            'results' => $results,
            'total' => $total,
            'query' => $query,
            'type' => $type
        ];
    }

    /**
     * Get search suggestions for autocomplete
     */
    public function getSearchSuggestions($query)
    {
        $suggestions = [];

        // Search in pages
        $pages = WebsitePage::published()
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(3)
            ->get(['title', 'slug']);

        foreach ($pages as $page) {
            $suggestions[] = [
                'title' => $page->title,
                'type' => 'page',
                'url' => route('website.page', $page->slug)
            ];
        }

        // Search in blog posts
        $posts = BlogPost::published()
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(3)
            ->get(['title', 'slug']);

        foreach ($posts as $post) {
            $suggestions[] = [
                'title' => $post->title,
                'type' => 'blog',
                'url' => route('website.blog.show', $post->slug)
            ];
        }

        // Search in events
        $events = Event::published()
            ->where('title', 'LIKE', "%{$query}%")
            ->limit(2)
            ->get(['title', 'slug']);

        foreach ($events as $event) {
            $suggestions[] = [
                'title' => $event->title,
                'type' => 'event',
                'url' => route('website.events.show', $event->slug)
            ];
        }

        return array_slice($suggestions, 0, 8);
    }

    /**
     * Get 404 page suggestions
     */
    public function get404Suggestions()
    {
        return [
            'popular_pages' => $this->getPopularPages(5),
            'recent_posts' => $this->getRecentBlogPosts(3),
            'upcoming_events' => $this->getUpcomingEvents(3)
        ];
    }

    // Private helper methods

    private function getHeroContent()
    {
        return WebsitePage::published()
            ->where('template', 'hero')
            ->first();
    }

    private function getFeaturedBlogPosts($limit = 3)
    {
        return BlogPost::published()
            ->featured()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getUpcomingEvents($limit = 4)
    {
        return Event::published()
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->limit($limit)
            ->get();
    }

    private function getGalleryPreview($limit = 6)
    {
        return GalleryAlbum::where('status', 'published')
            ->with(['images' => function ($query) {
                $query->limit(1);
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getFeaturedStaff($limit = 4)
    {
        return StaffMember::where('status', 'active')
            ->where('featured', true)
            ->orderBy('sort_order', 'asc')
            ->limit($limit)
            ->get();
    }

    private function getSchoolStats()
    {
        // This would typically come from a settings table or config
        return [
            'students' => 1200,
            'teachers' => 85,
            'programs' => 15,
            'years_established' => Carbon::now()->year - 1985
        ];
    }

    private function getTestimonials()
    {
        // This would come from a testimonials table
        return collect([
            [
                'name' => 'Parent Name',
                'role' => 'Parent',
                'content' => 'Excellent education and caring environment...',
                'rating' => 5
            ]
        ]);
    }

    private function getLatestNews($limit = 3)
    {
        return BlogPost::published()
            ->with(['category'])
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getBlogCategories()
    {
        return \Modules\Website\Entities\BlogCategory::where('status', 'active')
            ->withCount('posts')
            ->orderBy('name', 'asc')
            ->get();
    }

    private function searchPages($query, $perPage)
    {
        return WebsitePage::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%")
                  ->orWhere('excerpt', 'LIKE', "%{$query}%");
            })
            ->orderByRaw("CASE 
                WHEN title LIKE '{$query}%' THEN 1
                WHEN title LIKE '%{$query}%' THEN 2
                ELSE 3
            END")
            ->paginate($perPage);
    }

    private function searchBlogs($query, $perPage)
    {
        return BlogPost::published()
            ->with(['category', 'author'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%")
                  ->orWhere('excerpt', 'LIKE', "%{$query}%");
            })
            ->orderByRaw("CASE 
                WHEN title LIKE '{$query}%' THEN 1
                WHEN title LIKE '%{$query}%' THEN 2
                ELSE 3
            END")
            ->paginate($perPage);
    }

    private function searchEvents($query, $perPage)
    {
        return Event::published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%")
                  ->orWhere('location', 'LIKE', "%{$query}%");
            })
            ->orderBy('start_date', 'asc')
            ->paginate($perPage);
    }

    private function searchAll($query, $perPage)
    {
        $pages = $this->searchPages($query, 5)->items();
        $blogs = $this->searchBlogs($query, 5)->items();
        $events = $this->searchEvents($query, 5)->items();

        $allResults = collect()
            ->merge($pages)
            ->merge($blogs)
            ->merge($events);

        return [
            'pages' => $pages,
            'blogs' => $blogs,
            'events' => $events,
            'all' => $allResults,
            'total' => $allResults->count()
        ];
    }

    private function getPopularPages($limit)
    {
        return WebsitePage::published()
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getRecentBlogPosts($limit)
    {
        return BlogPost::published()
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    // Additional helper methods for different sections
    private function getMissionStatement()
    {
        return "To provide excellent education in a nurturing environment that develops academic excellence, character, and leadership.";
    }

    private function getVisionStatement()
    {
        return "To be a leading educational institution that prepares students for success in the 21st century.";
    }

    private function getSchoolHistory()
    {
        return WebsitePage::published()
            ->where('template', 'history')
            ->first();
    }

    private function getLeadershipTeam()
    {
        return StaffMember::where('status', 'active')
            ->where('department', 'leadership')
            ->orderBy('sort_order', 'asc')
            ->get();
    }

    private function getSchoolFacilities()
    {
        return WebsitePage::published()
            ->where('template', 'facilities')
            ->get();
    }

    private function getSchoolAchievements()
    {
        return WebsitePage::published()
            ->where('template', 'achievements')
            ->get();
    }

    private function getSchoolValues()
    {
        return [
            'Excellence', 'Integrity', 'Innovation', 'Collaboration', 'Respect'
        ];
    }

    private function getAdmissionProcess()
    {
        return WebsitePage::published()
            ->where('template', 'admission_process')
            ->first();
    }

    private function getAdmissionRequirements()
    {
        return WebsitePage::published()
            ->where('template', 'admission_requirements')
            ->first();
    }

    private function getSchoolFees()
    {
        return WebsitePage::published()
            ->where('template', 'school_fees')
            ->first();
    }

    private function getAdmissionCalendar()
    {
        return Event::published()
            ->where('event_type', 'admission')
            ->upcoming()
            ->orderBy('start_date', 'asc')
            ->get();
    }

    private function getAdmissionForms()
    {
        return WebsitePage::published()
            ->where('template', 'admission_forms')
            ->get();
    }

    private function getAdmissionContact()
    {
        return [
            'email' => 'admissions@school.edu',
            'phone' => '+1234567890',
            'office_hours' => '8:00 AM - 4:00 PM',
            'address' => 'School Address'
        ];
    }

    private function getAcademicPrograms()
    {
        return WebsitePage::published()
            ->where('template', 'academic_programs')
            ->get();
    }

    private function getCurriculum()
    {
        return WebsitePage::published()
            ->where('template', 'curriculum')
            ->first();
    }

    private function getDepartments()
    {
        return StaffMember::where('status', 'active')
            ->select('department')
            ->distinct()
            ->get()
            ->pluck('department');
    }

    private function getExtracurricularActivities()
    {
        return WebsitePage::published()
            ->where('template', 'extracurricular')
            ->get();
    }

    private function getAcademicCalendar()
    {
        return Event::published()
            ->where('event_type', 'academic')
            ->orderBy('start_date', 'asc')
            ->get();
    }

    private function getAcademicResources()
    {
        return WebsitePage::published()
            ->where('template', 'academic_resources')
            ->get();
    }
    /**
     * Get portfolio page data
     */
    public function getPortfolioData()
    {
        return [
            'featured_albums' => $this->getFeaturedGalleryAlbums(6),
            'recent_images' => $this->getRecentGalleryImages(12),
            'portfolio_categories' => $this->getPortfolioCategories(),
            'student_work' => $this->getStudentWork(),
            'achievements' => $this->getPortfolioAchievements(),
            'gallery_stats' => $this->getGalleryStats()
        ];
    }

    private function getFeaturedGalleryAlbums($limit = 6)
    {
        return GalleryAlbum::where('status', 'published')
            ->where('is_featured', true)
            ->with(['images' => function ($query) {
                $query->limit(4);
            }])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getRecentGalleryImages($limit = 12)
    {
        return \Modules\Website\Entities\GalleryImage::whereHas('album', function ($query) {
                $query->where('status', 'published');
            })
            ->with('album')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    private function getPortfolioCategories()
    {
        return GalleryAlbum::where('status', 'published')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
    }

    private function getStudentWork()
    {
        return GalleryAlbum::where('status', 'published')
            ->where('type', 'student_work')
            ->with(['images' => function ($query) {
                $query->limit(3);
            }])
            ->orderBy('created_at', 'desc')
            ->limit(4)
            ->get();
    }

    private function getPortfolioAchievements()
    {
        return WebsitePage::published()
            ->where('template', 'portfolio_achievements')
            ->get();
    }

    private function getGalleryStats()
    {
        return [
            'total_albums' => GalleryAlbum::where('status', 'published')->count(),
            'total_images' => \Modules\Website\Entities\GalleryImage::whereHas('album', function ($query) {
                $query->where('status', 'published');
            })->count(),
            'featured_albums' => GalleryAlbum::where('status', 'published')->where('is_featured', true)->count(),
        ];
    }
}