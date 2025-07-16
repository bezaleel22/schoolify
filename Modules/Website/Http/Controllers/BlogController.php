<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\BlogCategory;
use Modules\Website\Entities\BlogComment;
use Modules\Website\Services\BlogService;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Modules\Website\Http\Requests\BlogCommentRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class BlogController extends Controller
{
    protected $blogService;
    protected $seoService;
    protected $analyticsService;

    public function __construct(
        BlogService $blogService,
        SEOService $seoService,
        AnalyticsService $analyticsService
    ) {
        $this->blogService = $blogService;
        $this->seoService = $seoService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display blog listing page
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 12);
            $category = $request->get('category');
            $tag = $request->get('tag');
            $search = $request->get('search');
            $sort = $request->get('sort', 'latest');

            $blogData = $this->blogService->getBlogListing([
                'per_page' => $perPage,
                'category' => $category,
                'tag' => $tag,
                'search' => $search,
                'sort' => $sort
            ]);

            // Track page view
            $this->analyticsService->trackPageView('blog_listing');

            // Set SEO data
            $title = 'Blog';
            if ($category) {
                $categoryModel = BlogCategory::where('slug', $category)->first();
                $title = $categoryModel ? "Blog - {$categoryModel->name}" : $title;
            }

            $this->seoService->setPageSEO([
                'title' => $title,
                'description' => 'Read our latest blog posts, news, and educational insights.',
                'type' => 'website'
            ]);

            return view('website::pages.blog.index', compact('blogData'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display a specific blog post
     */
    public function show($slug)
    {
        try {
            $post = BlogPost::published()
                ->with(['category', 'author', 'approvedComments.user'])
                ->where('slug', $slug)
                ->firstOrFail();

            // Increment view count
            $post->incrementViewCount();

            // Track page view
            $this->analyticsService->trackPageView('blog', $post->id);

            // Get related posts
            $relatedPosts = $this->blogService->getRelatedPosts($post, 3);

            // Get blog navigation (previous/next)
            $navigation = $this->blogService->getBlogNavigation($post);

            // Set SEO data
            $seoData = $this->seoService->getBlogPostMeta($post);
            $this->seoService->setPageSEO($seoData);

            return view('website::pages.blog.show', compact('post', 'relatedPosts', 'navigation'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display blog by category
     */
    public function category($slug)
    {
        try {
            $category = BlogCategory::where('slug', $slug)
                ->where('status', 'active')
                ->firstOrFail();

            $posts = BlogPost::published()
                ->byCategory($slug)
                ->with(['category', 'author'])
                ->orderBy('published_at', 'desc')
                ->paginate(12);

            // Track page view
            $this->analyticsService->trackPageView('blog_category', $category->id);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => "Blog - {$category->name}",
                'description' => $category->description ?: "Read our latest posts in {$category->name}.",
                'type' => 'website'
            ]);

            return view('website::pages.blog.category', compact('category', 'posts'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display blog by tag
     */
    public function tag($tag)
    {
        try {
            $posts = BlogPost::published()
                ->byTag($tag)
                ->with(['category', 'author'])
                ->orderBy('published_at', 'desc')
                ->paginate(12);

            // Track page view
            $this->analyticsService->trackPageView('blog_tag');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => "Blog - {$tag}",
                'description' => "Read our latest posts tagged with {$tag}.",
                'type' => 'website'
            ]);

            return view('website::pages.blog.tag', compact('tag', 'posts'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display blog by author
     */
    public function author($authorId)
    {
        try {
            $author = \App\Models\User::findOrFail($authorId);

            $posts = BlogPost::published()
                ->byAuthor($authorId)
                ->with(['category'])
                ->orderBy('published_at', 'desc')
                ->paginate(12);

            // Track page view
            $this->analyticsService->trackPageView('blog_author', $authorId);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => "Blog - Posts by {$author->name}",
                'description' => "Read blog posts written by {$author->name}.",
                'type' => 'website'
            ]);

            return view('website::pages.blog.author', compact('author', 'posts'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Store a new comment (requires Google OAuth)
     */
    public function storeComment(BlogCommentRequest $request, $postId)
    {
        try {
            // Check if user is authenticated via Google OAuth
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to comment.',
                    'redirect' => route('website.auth.google')
                ], 401);
            }

            $post = BlogPost::published()->findOrFail($postId);

            if (!$post->allowsComments()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Comments are not allowed on this post.'
                ], 403);
            }

            $comment = $this->blogService->storeComment($post, $request->validated(), Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Comment submitted successfully.',
                'comment' => $comment->load('user'),
                'awaiting_approval' => $comment->status === 'pending'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit comment. Please try again.'
            ], 500);
        }
    }

    /**
     * Like/Unlike a blog post
     */
    public function toggleLike(Request $request, $postId)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You must be logged in to like posts.'
                ], 401);
            }

            $post = BlogPost::published()->findOrFail($postId);
            $result = $this->blogService->toggleLike($post, Auth::user());

            return response()->json([
                'success' => true,
                'liked' => $result['liked'],
                'like_count' => $result['like_count']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process like action.'
            ], 500);
        }
    }

    /**
     * Share a blog post
     */
    public function share(Request $request, $postId)
    {
        try {
            $platform = $request->get('platform', 'link');
            $post = BlogPost::published()->findOrFail($postId);

            $shareUrl = $this->blogService->generateShareUrl($post, $platform);

            // Track share
            $this->analyticsService->trackPageView('blog_share', $post->id, [
                'platform' => $platform
            ]);

            return response()->json([
                'success' => true,
                'share_url' => $shareUrl
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate share URL.'
            ], 500);
        }
    }

    /**
     * Get RSS feed for blog
     */
    public function rss()
    {
        try {
            $feed = Cache::remember('blog.rss_feed', 3600, function () {
                return $this->blogService->generateRSSFeed();
            });

            return Response::make($feed, 200, [
                'Content-Type' => 'application/rss+xml; charset=UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get Atom feed for blog
     */
    public function atom()
    {
        try {
            $feed = Cache::remember('blog.atom_feed', 3600, function () {
                return $this->blogService->generateAtomFeed();
            });

            return Response::make($feed, 200, [
                'Content-Type' => 'application/atom+xml; charset=UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get popular posts API
     */
    public function popularPosts(Request $request)
    {
        try {
            $limit = $request->get('limit', 5);
            $period = $request->get('period', 30); // days

            $posts = $this->blogService->getPopularPosts($limit, $period);

            return response()->json([
                'success' => true,
                'posts' => $posts
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get related posts API
     */
    public function relatedPosts(Request $request, $postId)
    {
        try {
            $limit = $request->get('limit', 3);
            $post = BlogPost::published()->findOrFail($postId);

            $relatedPosts = $this->blogService->getRelatedPosts($post, $limit);

            return response()->json([
                'success' => true,
                'posts' => $relatedPosts
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search within blog posts
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $category = $request->get('category');
            $perPage = $request->get('per_page', 10);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters.'
                ], 400);
            }

            $results = $this->blogService->searchPosts($query, $category, $perPage);

            // Track search
            $this->analyticsService->trackSearch($query, $results->total());

            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get blog archive (posts by month/year)
     */
    public function archive(Request $request, $year, $month = null)
    {
        try {
            $posts = $this->blogService->getArchivePosts($year, $month);

            // Track page view
            $this->analyticsService->trackPageView('blog_archive');

            $archiveTitle = $month ? 
                date('F Y', mktime(0, 0, 0, $month, 1, $year)) : 
                "Posts from {$year}";

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => "Blog Archive - {$archiveTitle}",
                'description' => "Browse our blog posts from {$archiveTitle}.",
                'type' => 'website'
            ]);

            return view('website::pages.blog.archive', compact('posts', 'year', 'month', 'archiveTitle'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Get comment thread for a post
     */
    public function getComments($postId)
    {
        try {
            $post = BlogPost::published()->findOrFail($postId);
            $comments = $this->blogService->getCommentThread($post);

            return response()->json([
                'success' => true,
                'comments' => $comments
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Report inappropriate comment
     */
    public function reportComment(Request $request, $commentId)
    {
        try {
            $reason = $request->get('reason', 'inappropriate');
            $comment = BlogComment::findOrFail($commentId);

            $this->blogService->reportComment($comment, $reason, Auth::user());

            return response()->json([
                'success' => true,
                'message' => 'Comment reported successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to report comment.'
            ], 500);
        }
    }
}
