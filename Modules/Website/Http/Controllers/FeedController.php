<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\BlogPost;
use Modules\Website\Entities\Event;
use Modules\Website\Entities\WebsitePage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;

class FeedController extends Controller
{
    /**
     * Generate RSS feed for blog posts
     */
    public function blogRss()
    {
        try {
            $feed = Cache::remember('feeds.blog_rss', 3600, function () {
                return $this->generateBlogRssFeed();
            });

            return Response::make($feed, 200, [
                'Content-Type' => 'application/rss+xml; charset=UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate Atom feed for blog posts
     */
    public function blogAtom()
    {
        try {
            $feed = Cache::remember('feeds.blog_atom', 3600, function () {
                return $this->generateBlogAtomFeed();
            });

            return Response::make($feed, 200, [
                'Content-Type' => 'application/atom+xml; charset=UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate RSS feed for events
     */
    public function eventsRss()
    {
        try {
            $feed = Cache::remember('feeds.events_rss', 3600, function () {
                return $this->generateEventsRssFeed();
            });

            return Response::make($feed, 200, [
                'Content-Type' => 'application/rss+xml; charset=UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate RSS feed for news
     */
    public function newsRss()
    {
        try {
            $feed = Cache::remember('feeds.news_rss', 3600, function () {
                return $this->generateNewsRssFeed();
            });

            return Response::make($feed, 200, [
                'Content-Type' => 'application/rss+xml; charset=UTF-8'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate RSS feed for blog posts
     */
    private function generateBlogRssFeed()
    {
        $posts = BlogPost::where('status', 'published')
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>' . htmlspecialchars(config('app.name', 'School Website') . ' - Blog') . '</title>' . "\n";
        $rss .= '<link>' . htmlspecialchars(route('website.blog.index')) . '</link>' . "\n";
        $rss .= '<description>Latest blog posts from ' . htmlspecialchars(config('app.name', 'School Website')) . '</description>' . "\n";
        $rss .= '<language>en-us</language>' . "\n";
        $rss .= '<lastBuildDate>' . now()->toRssString() . '</lastBuildDate>' . "\n";
        $rss .= '<atom:link href="' . htmlspecialchars(route('website.feeds.blog.rss')) . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($posts as $post) {
            $rss .= '<item>' . "\n";
            $rss .= '<title>' . htmlspecialchars($post->title) . '</title>' . "\n";
            $rss .= '<link>' . htmlspecialchars(route('website.blog.show', $post->slug)) . '</link>' . "\n";
            $rss .= '<description>' . htmlspecialchars($post->excerpt ?: strip_tags(substr($post->content, 0, 300))) . '</description>' . "\n";
            $rss .= '<pubDate>' . $post->published_at->toRssString() . '</pubDate>' . "\n";
            $rss .= '<guid>' . htmlspecialchars(route('website.blog.show', $post->slug)) . '</guid>' . "\n";
            
            if ($post->category) {
                $rss .= '<category>' . htmlspecialchars($post->category->name) . '</category>' . "\n";
            }
            
            if ($post->author) {
                $rss .= '<author>' . htmlspecialchars($post->author->email . ' (' . $post->author->name . ')') . '</author>' . "\n";
            }
            
            $rss .= '</item>' . "\n";
        }

        $rss .= '</channel>' . "\n";
        $rss .= '</rss>' . "\n";

        return $rss;
    }

    /**
     * Generate Atom feed for blog posts
     */
    private function generateBlogAtomFeed()
    {
        $posts = BlogPost::where('status', 'published')
            ->with(['category', 'author'])
            ->orderBy('published_at', 'desc')
            ->limit(20)
            ->get();

        $atom = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $atom .= '<feed xmlns="http://www.w3.org/2005/Atom">' . "\n";
        $atom .= '<title>' . htmlspecialchars(config('app.name', 'School Website') . ' - Blog') . '</title>' . "\n";
        $atom .= '<link href="' . htmlspecialchars(route('website.blog.index')) . '" />' . "\n";
        $atom .= '<link href="' . htmlspecialchars(route('website.feeds.blog.atom')) . '" rel="self" />' . "\n";
        $atom .= '<id>' . htmlspecialchars(route('website.blog.index')) . '</id>' . "\n";
        $atom .= '<updated>' . now()->toAtomString() . '</updated>' . "\n";
        $atom .= '<subtitle>Latest blog posts from ' . htmlspecialchars(config('app.name', 'School Website')) . '</subtitle>' . "\n";

        foreach ($posts as $post) {
            $atom .= '<entry>' . "\n";
            $atom .= '<title>' . htmlspecialchars($post->title) . '</title>' . "\n";
            $atom .= '<link href="' . htmlspecialchars(route('website.blog.show', $post->slug)) . '" />' . "\n";
            $atom .= '<id>' . htmlspecialchars(route('website.blog.show', $post->slug)) . '</id>' . "\n";
            $atom .= '<updated>' . $post->updated_at->toAtomString() . '</updated>' . "\n";
            $atom .= '<published>' . $post->published_at->toAtomString() . '</published>' . "\n";
            $atom .= '<summary>' . htmlspecialchars($post->excerpt ?: strip_tags(substr($post->content, 0, 300))) . '</summary>' . "\n";
            $atom .= '<content type="html">' . htmlspecialchars($post->content) . '</content>' . "\n";
            
            if ($post->author) {
                $atom .= '<author>' . "\n";
                $atom .= '<name>' . htmlspecialchars($post->author->name) . '</name>' . "\n";
                $atom .= '<email>' . htmlspecialchars($post->author->email) . '</email>' . "\n";
                $atom .= '</author>' . "\n";
            }
            
            if ($post->category) {
                $atom .= '<category term="' . htmlspecialchars($post->category->slug) . '" label="' . htmlspecialchars($post->category->name) . '" />' . "\n";
            }
            
            $atom .= '</entry>' . "\n";
        }

        $atom .= '</feed>' . "\n";

        return $atom;
    }

    /**
     * Generate RSS feed for events
     */
    private function generateEventsRssFeed()
    {
        $events = Event::where('status', 'published')
            ->where('start_date', '>=', now())
            ->orderBy('start_date', 'asc')
            ->limit(20)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>' . htmlspecialchars(config('app.name', 'School Website') . ' - Events') . '</title>' . "\n";
        $rss .= '<link>' . htmlspecialchars(route('website.events.index')) . '</link>' . "\n";
        $rss .= '<description>Upcoming events from ' . htmlspecialchars(config('app.name', 'School Website')) . '</description>' . "\n";
        $rss .= '<language>en-us</language>' . "\n";
        $rss .= '<lastBuildDate>' . now()->toRssString() . '</lastBuildDate>' . "\n";
        $rss .= '<atom:link href="' . htmlspecialchars(route('website.feeds.events.rss')) . '" rel="self" type="application/rss+xml" />' . "\n";

        foreach ($events as $event) {
            $rss .= '<item>' . "\n";
            $rss .= '<title>' . htmlspecialchars($event->title) . '</title>' . "\n";
            $rss .= '<link>' . htmlspecialchars(route('website.events.show', $event->slug)) . '</link>' . "\n";
            $rss .= '<description>' . htmlspecialchars($event->description ?: 'Event: ' . $event->title) . '</description>' . "\n";
            $rss .= '<pubDate>' . $event->start_date->toRssString() . '</pubDate>' . "\n";
            $rss .= '<guid>' . htmlspecialchars(route('website.events.show', $event->slug)) . '</guid>' . "\n";
            
            if ($event->location) {
                $rss .= '<category>Location: ' . htmlspecialchars($event->location) . '</category>' . "\n";
            }
            
            $rss .= '</item>' . "\n";
        }

        $rss .= '</channel>' . "\n";
        $rss .= '</rss>' . "\n";

        return $rss;
    }

    /**
     * Generate RSS feed for news
     */
    private function generateNewsRssFeed()
    {
        // Get news from blog posts with news category or pages with news type
        $newsPosts = BlogPost::where('status', 'published')
            ->whereHas('category', function ($query) {
                $query->where('slug', 'news')
                      ->orWhere('slug', 'announcements');
            })
            ->orderBy('published_at', 'desc')
            ->limit(15)
            ->get();

        $newsPages = WebsitePage::where('status', 'published')
            ->where('type', 'news')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $rss = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $rss .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $rss .= '<channel>' . "\n";
        $rss .= '<title>' . htmlspecialchars(config('app.name', 'School Website') . ' - News') . '</title>' . "\n";
        $rss .= '<link>' . htmlspecialchars(route('website.news')) . '</link>' . "\n";
        $rss .= '<description>Latest news and announcements from ' . htmlspecialchars(config('app.name', 'School Website')) . '</description>' . "\n";
        $rss .= '<language>en-us</language>' . "\n";
        $rss .= '<lastBuildDate>' . now()->toRssString() . '</lastBuildDate>' . "\n";
        $rss .= '<atom:link href="' . htmlspecialchars(route('website.feeds.news.rss')) . '" rel="self" type="application/rss+xml" />' . "\n";

        // Add blog posts
        foreach ($newsPosts as $post) {
            $rss .= '<item>' . "\n";
            $rss .= '<title>' . htmlspecialchars($post->title) . '</title>' . "\n";
            $rss .= '<link>' . htmlspecialchars(route('website.blog.show', $post->slug)) . '</link>' . "\n";
            $rss .= '<description>' . htmlspecialchars($post->excerpt ?: strip_tags(substr($post->content, 0, 300))) . '</description>' . "\n";
            $rss .= '<pubDate>' . $post->published_at->toRssString() . '</pubDate>' . "\n";
            $rss .= '<guid>' . htmlspecialchars(route('website.blog.show', $post->slug)) . '</guid>' . "\n";
            $rss .= '<category>News</category>' . "\n";
            $rss .= '</item>' . "\n";
        }

        // Add news pages
        foreach ($newsPages as $page) {
            $rss .= '<item>' . "\n";
            $rss .= '<title>' . htmlspecialchars($page->title) . '</title>' . "\n";
            $rss .= '<link>' . htmlspecialchars(route('website.page', $page->slug)) . '</link>' . "\n";
            $rss .= '<description>' . htmlspecialchars($page->excerpt ?: strip_tags(substr($page->content, 0, 300))) . '</description>' . "\n";
            $rss .= '<pubDate>' . $page->created_at->toRssString() . '</pubDate>' . "\n";
            $rss .= '<guid>' . htmlspecialchars(route('website.page', $page->slug)) . '</guid>' . "\n";
            $rss .= '<category>News</category>' . "\n";
            $rss .= '</item>' . "\n";
        }

        $rss .= '</channel>' . "\n";
        $rss .= '</rss>' . "\n";

        return $rss;
    }
}