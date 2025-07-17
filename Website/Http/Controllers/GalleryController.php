<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\GalleryAlbum;
use Modules\Website\Entities\GalleryImage;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;

class GalleryController extends Controller
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
     * Display gallery index page
     */
    public function index()
    {
        try {
            $featuredAlbums = Cache::remember('gallery.featured_albums', 3600, function () {
                return GalleryAlbum::where('status', 'published')
                    ->where('is_featured', true)
                    ->with(['images' => function ($query) {
                        $query->limit(4);
                    }])
                    ->orderBy('created_at', 'desc')
                    ->limit(6)
                    ->get();
            });

            $recentImages = Cache::remember('gallery.recent_images', 1800, function () {
                return GalleryImage::with('album')
                    ->whereHas('album', function ($query) {
                        $query->where('status', 'published');
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(12)
                    ->get();
            });

            // Track page view
            $this->analyticsService->trackPageView('gallery');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Photo Gallery',
                'description' => 'Browse our photo gallery featuring school events, activities, and memorable moments.',
                'type' => 'page'
            ]);

            return view('website::pages.gallery.index', compact('featuredAlbums', 'recentImages'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display all albums
     */
    public function albums(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 12);
            $sortBy = $request->get('sort', 'latest'); // latest, popular, alphabetical

            $albums = GalleryAlbum::where('status', 'published')
                ->withCount('images')
                ->with(['images' => function ($query) {
                    $query->limit(1); // Cover image
                }]);

            switch ($sortBy) {
                case 'popular':
                    $albums->orderBy('views_count', 'desc');
                    break;
                case 'alphabetical':
                    $albums->orderBy('title', 'asc');
                    break;
                default:
                    $albums->orderBy('created_at', 'desc');
            }

            $albums = $albums->paginate($perPage);

            // Track page view
            $this->analyticsService->trackPageView('gallery_albums');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Photo Albums',
                'description' => 'Browse all our photo albums documenting school life and events.',
                'type' => 'page'
            ]);

            return view('website::pages.gallery.albums', compact('albums', 'sortBy'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display specific album
     */
    public function album($slug)
    {
        try {
            $album = GalleryAlbum::where('slug', $slug)
                ->where('status', 'published')
                ->with(['images' => function ($query) {
                    $query->orderBy('sort_order', 'asc');
                }])
                ->firstOrFail();

            // Increment view count
            $album->increment('views_count');

            // Track page view
            $this->analyticsService->trackPageView('gallery_album', $album->id);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => $album->title,
                'description' => $album->description ?: "Photo album: {$album->title}",
                'type' => 'page',
                'image' => $album->images->first()?->image_url
            ]);

            return view('website::pages.gallery.album', compact('album'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display specific image
     */
    public function image($id)
    {
        try {
            $image = GalleryImage::with(['album'])
                ->whereHas('album', function ($query) {
                    $query->where('status', 'published');
                })
                ->findOrFail($id);

            // Get adjacent images
            $prevImage = GalleryImage::where('album_id', $image->album_id)
                ->where('sort_order', '<', $image->sort_order)
                ->orderBy('sort_order', 'desc')
                ->first();

            $nextImage = GalleryImage::where('album_id', $image->album_id)
                ->where('sort_order', '>', $image->sort_order)
                ->orderBy('sort_order', 'asc')
                ->first();

            // Track page view
            $this->analyticsService->trackPageView('gallery_image', $image->id);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => $image->title ?: $image->album->title,
                'description' => $image->description ?: "Image from {$image->album->title}",
                'type' => 'article',
                'image' => $image->image_url
            ]);

            return view('website::pages.gallery.image', compact('image', 'prevImage', 'nextImage'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display latest images
     */
    public function latest(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 20);

            $images = GalleryImage::with('album')
                ->whereHas('album', function ($query) {
                    $query->where('status', 'published');
                })
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            // Track page view
            $this->analyticsService->trackPageView('gallery_latest');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Latest Photos',
                'description' => 'View the latest photos uploaded to our gallery.',
                'type' => 'page'
            ]);

            return view('website::pages.gallery.latest', compact('images'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get albums data
     */
    public function getAlbums(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $featured = $request->get('featured', false);

            $albums = GalleryAlbum::where('status', 'published')
                ->withCount('images')
                ->with(['images' => function ($query) {
                    $query->limit(1);
                }]);

            if ($featured) {
                $albums->where('is_featured', true);
            }

            $albums = $albums->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'albums' => $albums
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get album images
     */
    public function getAlbumImages($slug)
    {
        try {
            $album = GalleryAlbum::where('slug', $slug)
                ->where('status', 'published')
                ->with(['images' => function ($query) {
                    $query->orderBy('sort_order', 'asc');
                }])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'album' => $album,
                'images' => $album->images
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get recent images
     */
    public function getRecentImages(Request $request, $limit = 8)
    {
        try {
            $images = GalleryImage::with('album')
                ->whereHas('album', function ($query) {
                    $query->where('status', 'published');
                })
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'images' => $images
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}