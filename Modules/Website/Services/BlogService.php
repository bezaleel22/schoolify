<?php

namespace Modules\Website\Services;

use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\BlogCategory;
use Modules\Website\Entities\BlogComment;
use Modules\Website\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class BlogService
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get blog listing with filters and pagination
     */
    public function getBlogListing($params)
    {
        $query = BlogPost::published()->with(['category', 'author']);

        // Apply filters
        if (!empty($params['category'])) {
            $query->byCategory($params['category']);
        }

        if (!empty($params['tag'])) {
            $query->byTag($params['tag']);
        }

        if (!empty($params['search'])) {
            $searchTerm = $params['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('content', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('excerpt', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apply sorting
        switch ($params['sort']) {
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'commented':
                $query->orderBy('comment_count', 'desc');
                break;
            case 'oldest':
                $query->orderBy('published_at', 'asc');
                break;
            case 'title':
                $query->orderBy('title', 'asc');
                break;
            default: // latest
                $query->orderBy('published_at', 'desc');
                break;
        }

        $posts = $query->paginate($params['per_page']);
        
        // Get additional data
        $categories = $this->getBlogCategories();
        $popularPosts = $this->getPopularPosts(5, 30);
        $recentPosts = $this->getRecentPosts(5);
        $tags = $this->getPopularTags(20);
        $archive = $this->getArchiveMonths();

        return [
            'posts' => $posts,
            'categories' => $categories,
            'popular_posts' => $popularPosts,
            'recent_posts' => $recentPosts,
            'tags' => $tags,
            'archive' => $archive,
            'current_filters' => $params
        ];
    }

    /**
     * Get related posts based on category and tags
     */
    public function getRelatedPosts(BlogPost $post, $limit = 3)
    {
        $cacheKey = "related_posts_{$post->id}_{$limit}";
        
        return $this->cacheService->remember($cacheKey, 3600, function () use ($post, $limit) {
            // First try to find posts in the same category
            $related = BlogPost::published()
                ->where('id', '!=', $post->id)
                ->where('category_id', $post->category_id)
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();

            // If not enough posts, find by tags
            if ($related->count() < $limit && !empty($post->tags)) {
                $remaining = $limit - $related->count();
                $excludeIds = $related->pluck('id')->push($post->id)->toArray();

                $tagRelated = BlogPost::published()
                    ->whereNotIn('id', $excludeIds)
                    ->where(function ($query) use ($post) {
                        foreach ($post->tags as $tag) {
                            $query->orWhereJsonContains('tags', $tag);
                        }
                    })
                    ->orderBy('published_at', 'desc')
                    ->limit($remaining)
                    ->get();

                $related = $related->merge($tagRelated);
            }

            // If still not enough, get recent posts
            if ($related->count() < $limit) {
                $remaining = $limit - $related->count();
                $excludeIds = $related->pluck('id')->push($post->id)->toArray();

                $recentPosts = BlogPost::published()
                    ->whereNotIn('id', $excludeIds)
                    ->orderBy('published_at', 'desc')
                    ->limit($remaining)
                    ->get();

                $related = $related->merge($recentPosts);
            }

            return $related->take($limit);
        });
    }

    /**
     * Get blog navigation (previous/next posts)
     */
    public function getBlogNavigation(BlogPost $post)
    {
        $previous = BlogPost::published()
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->first();

        $next = BlogPost::published()
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->first();

        return [
            'previous' => $previous,
            'next' => $next
        ];
    }

    /**
     * Store a new comment
     */
    public function storeComment(BlogPost $post, $data, $user)
    {
        $comment = new BlogComment([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
            'status' => $this->getCommentStatus($post),
            'user_name' => $user->name,
            'user_email' => $user->email
        ]);

        $comment->save();

        // Update post comment count
        $post->updateCommentCount();

        // Send notification emails if needed
        $this->sendCommentNotifications($comment);

        return $comment;
    }

    /**
     * Toggle like on a blog post
     */
    public function toggleLike(BlogPost $post, $user)
    {
        $existingLike = DB::table('blog_post_likes')
            ->where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingLike) {
            DB::table('blog_post_likes')
                ->where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->delete();
            $liked = false;
        } else {
            DB::table('blog_post_likes')->insert([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $liked = true;
        }

        $likeCount = DB::table('blog_post_likes')
            ->where('post_id', $post->id)
            ->count();

        // Update post like count
        $post->update(['like_count' => $likeCount]);

        return [
            'liked' => $liked,
            'like_count' => $likeCount
        ];
    }

    /**
     * Generate share URL for different platforms
     */
    public function generateShareUrl(BlogPost $post, $platform)
    {
        $url = route('website.blog.show', $post->slug);
        $title = $post->title;
        $description = $post->excerpt;

        switch ($platform) {
            case 'facebook':
                return "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($url);
            
            case 'twitter':
                $text = "{$title} - {$description}";
                return "https://twitter.com/intent/tweet?text=" . urlencode($text) . "&url=" . urlencode($url);
            
            case 'linkedin':
                return "https://www.linkedin.com/sharing/share-offsite/?url=" . urlencode($url);
            
            case 'whatsapp':
                $text = "{$title} - {$url}";
                return "https://wa.me/?text=" . urlencode($text);
            
            case 'email':
                $subject = "Check out this article: {$title}";
                $body = "{$description}\n\nRead more: {$url}";
                return "mailto:?subject=" . urlencode($subject) . "&body=" . urlencode($body);
            
            default:
                return $url;
        }
    }

    /**
     * Generate RSS feed
     */
    public function generateRSSFeed()
    {
        $posts = BlogPost::published()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<channel>' . "\n";
        $xml .= '<title>School Blog</title>' . "\n";
        $xml .= '<description>Latest blog posts from our school</description>' . "\n";
        $xml .= '<link>' . config('app.url') . '</link>' . "\n";
        $xml .= '<atom:link href="' . route('website.blog.rss') . '" rel="self" type="application/rss+xml" />' . "\n";
        $xml .= '<lastBuildDate>' . now()->toRssString() . '</lastBuildDate>' . "\n";

        foreach ($posts as $post) {
            $xml .= '<item>' . "\n";
            $xml .= '<title><![CDATA[' . $post->title . ']]></title>' . "\n";
            $xml .= '<description><![CDATA[' . $post->excerpt . ']]></description>' . "\n";
            $xml .= '<content:encoded><![CDATA[' . $post->content . ']]></content:encoded>' . "\n";
            $xml .= '<link>' . route('website.blog.show', $post->slug) . '</link>' . "\n";
            $xml .= '<guid>' . route('website.blog.show', $post->slug) . '</guid>' . "\n";
            $xml .= '<pubDate>' . $post->published_at->toRssString() . '</pubDate>' . "\n";
            if ($post->author) {
                $xml .= '<author>' . $post->author->email . ' (' . $post->author->name . ')</author>' . "\n";
            }
            if ($post->category) {
                $xml .= '<category>' . $post->category->name . '</category>' . "\n";
            }
            $xml .= '</item>' . "\n";
        }

        $xml .= '</channel>' . "\n";
        $xml .= '</rss>';

        return $xml;
    }

    /**
     * Generate Atom feed
     */
    public function generateAtomFeed()
    {
        $posts = BlogPost::published()
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '<title>School Blog</title>' . "\n";
        $xml .= '<subtitle>Latest blog posts from our school</subtitle>' . "\n";
        $xml .= '<link href="' . config('app.url') . '" />' . "\n";
        $xml .= '<link href="' . route('website.blog.atom') . '" rel="self" />' . "\n";
        $xml .= '<id>' . config('app.url') . '</id>' . "\n";
        $xml .= '<updated>' . now()->toAtomString() . '</updated>' . "\n";

        foreach ($posts as $post) {
            $xml .= '<entry>' . "\n";
            $xml .= '<title><![CDATA[' . $post->title . ']]></title>' . "\n";
            $xml .= '<link href="' . route('website.blog.show', $post->slug) . '" />' . "\n";
            $xml .= '<id>' . route('website.blog.show', $post->slug) . '</id>' . "\n";
            $xml .= '<updated>' . $post->updated_at->toAtomString() . '</updated>' . "\n";
            $xml .= '<published>' . $post->published_at->toAtomString() . '</published>' . "\n";
            $xml .= '<summary><![CDATA[' . $post->excerpt . ']]></summary>' . "\n";
            $xml .= '<content type="html"><![CDATA[' . $post->content . ']]></content>' . "\n";
            if ($post->author) {
                $xml .= '<author><name>' . $post->author->name . '</name><email>' . $post->author->email . '</email></author>' . "\n";
            }
            if ($post->category) {
                $xml .= '<category term="' . $post->category->slug . '" label="' . $post->category->name . '" />' . "\n";
            }
            $xml .= '</entry>' . "\n";
        }

        $xml .= '</feed>';

        return $xml;
    }

    /**
     * Get popular posts
     */
    public function getPopularPosts($limit = 5, $period = 30)
    {
        $cacheKey = "popular_posts_{$limit}_{$period}";
        
        return $this->cacheService->remember($cacheKey, 1800, function () use ($limit, $period) {
            return BlogPost::published()
                ->popular($period)
                ->with(['category'])
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Search blog posts
     */
    public function searchPosts($query, $category = null, $perPage = 10)
    {
        $searchQuery = BlogPost::published()
            ->with(['category', 'author'])
            ->where(function ($q) use ($query) {
                $q->where('title', 'LIKE', "%{$query}%")
                  ->orWhere('content', 'LIKE', "%{$query}%")
                  ->orWhere('excerpt', 'LIKE', "%{$query}%");
            });

        if ($category) {
            $searchQuery->byCategory($category);
        }

        return $searchQuery->orderByRaw("
            CASE 
                WHEN title LIKE '{$query}%' THEN 1
                WHEN title LIKE '%{$query}%' THEN 2
                ELSE 3
            END, published_at DESC
        ")->paginate($perPage);
    }

    /**
     * Get archive posts by year/month
     */
    public function getArchivePosts($year, $month = null)
    {
        $query = BlogPost::published()->with(['category', 'author']);

        if ($month) {
            $query->whereYear('published_at', $year)
                  ->whereMonth('published_at', $month);
        } else {
            $query->whereYear('published_at', $year);
        }

        return $query->orderBy('published_at', 'desc')->paginate(12);
    }

    /**
     * Get comment thread for a post
     */
    public function getCommentThread(BlogPost $post)
    {
        return $post->approvedComments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Report a comment
     */
    public function reportComment(BlogComment $comment, $reason, $user = null)
    {
        // Store the report
        DB::table('comment_reports')->insert([
            'comment_id' => $comment->id,
            'reporter_id' => $user ? $user->id : null,
            'reason' => $reason,
            'reported_at' => now()
        ]);

        // If comment has multiple reports, mark for review
        $reportCount = DB::table('comment_reports')
            ->where('comment_id', $comment->id)
            ->count();

        if ($reportCount >= 3) {
            $comment->update(['status' => 'reported']);
        }
    }

    // Private helper methods

    private function getBlogCategories()
    {
        return $this->cacheService->remember('blog_categories', 3600, function () {
            return BlogCategory::where('status', 'active')
                ->withCount(['posts' => function ($query) {
                    $query->published();
                }])
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    private function getRecentPosts($limit = 5)
    {
        return $this->cacheService->remember("recent_posts_{$limit}", 1800, function () use ($limit) {
            return BlogPost::published()
                ->with(['category'])
                ->orderBy('published_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    private function getPopularTags($limit = 20)
    {
        return $this->cacheService->remember("popular_tags_{$limit}", 3600, function () use ($limit) {
            $tags = BlogPost::published()
                ->whereNotNull('tags')
                ->get()
                ->pluck('tags')
                ->flatten()
                ->countBy()
                ->sortDesc()
                ->take($limit);

            return $tags->map(function ($count, $tag) {
                return [
                    'name' => $tag,
                    'count' => $count,
                    'slug' => \Illuminate\Support\Str::slug($tag)
                ];
            })->values();
        });
    }

    private function getArchiveMonths()
    {
        return $this->cacheService->remember('blog_archive_months', 3600, function () {
            return BlogPost::published()
                ->selectRaw('YEAR(published_at) as year, MONTH(published_at) as month, COUNT(*) as count')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
                ->map(function ($item) {
                    $item->month_name = Carbon::createFromDate($item->year, $item->month, 1)->format('F');
                    return $item;
                });
        });
    }

    private function getCommentStatus(BlogPost $post)
    {
        // Check if auto-approval is enabled
        $autoApprove = config('website.comments.auto_approve', false);
        
        if ($autoApprove) {
            return 'approved';
        }

        return 'pending';
    }

    private function sendCommentNotifications(BlogComment $comment)
    {
        // Send notification to post author
        $post = $comment->post;
        if ($post->author && $post->author->email) {
            // Send email notification (implement as needed)
        }

        // Send notification to parent comment author if this is a reply
        if ($comment->parent_id) {
            $parentComment = BlogComment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_email) {
                // Send reply notification (implement as needed)
            }
        }
    }
}
