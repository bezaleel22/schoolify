<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\Event;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class EventController extends Controller
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
     * Display events listing
     */
    public function index(Request $request)
    {
        try {
            $type = $request->get('type');
            $month = $request->get('month');
            $year = $request->get('year');
            $perPage = $request->get('per_page', 12);

            $events = Event::where('status', 'published');

            if ($type) {
                $events->where('type', $type);
            }

            if ($month && $year) {
                $events->whereMonth('start_date', $month)
                      ->whereYear('start_date', $year);
            }

            $events = $events->orderBy('start_date', 'asc')->paginate($perPage);

            // Get event types for filter
            $eventTypes = Cache::remember('events.types', 3600, function () {
                return Event::where('status', 'published')
                    ->distinct()
                    ->pluck('type')
                    ->filter()
                    ->sort()
                    ->values();
            });

            // Track page view
            $this->analyticsService->trackPageView('events');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'School Events',
                'description' => 'Stay updated with our school events, activities, and important dates.',
                'type' => 'page'
            ]);

            return view('website::pages.events.index', compact('events', 'eventTypes', 'type'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display events calendar
     */
    public function calendar()
    {
        try {
            // Track page view
            $this->analyticsService->trackPageView('events_calendar');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Events Calendar',
                'description' => 'View our school events in calendar format.',
                'type' => 'page'
            ]);

            return view('website::pages.events.calendar');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display upcoming events
     */
    public function upcoming(Request $request)
    {
        try {
            $limit = $request->get('limit', 10);
            $type = $request->get('type');

            $events = Event::where('status', 'published')
                ->where('start_date', '>=', now());

            if ($type) {
                $events->where('type', $type);
            }

            $events = $events->orderBy('start_date', 'asc')->limit($limit)->get();

            // Track page view
            $this->analyticsService->trackPageView('events_upcoming');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Upcoming Events',
                'description' => 'View our upcoming school events and activities.',
                'type' => 'page'
            ]);

            return view('website::pages.events.upcoming', compact('events'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display past events
     */
    public function past(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 12);
            $type = $request->get('type');

            $events = Event::where('status', 'published')
                ->where('end_date', '<', now());

            if ($type) {
                $events->where('type', $type);
            }

            $events = $events->orderBy('start_date', 'desc')->paginate($perPage);

            // Track page view
            $this->analyticsService->trackPageView('events_past');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Past Events',
                'description' => 'Browse our past school events and activities.',
                'type' => 'page'
            ]);

            return view('website::pages.events.past', compact('events'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display events by type
     */
    public function byType($type)
    {
        try {
            $events = Event::where('status', 'published')
                ->where('type', $type)
                ->orderBy('start_date', 'asc')
                ->paginate(12);

            if ($events->isEmpty()) {
                abort(404);
            }

            // Track page view
            $this->analyticsService->trackPageView('events_by_type', null, [
                'type' => $type
            ]);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => ucfirst($type) . ' Events',
                'description' => "Browse our {$type} events and activities.",
                'type' => 'page'
            ]);

            return view('website::pages.events.by-type', compact('events', 'type'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display specific event
     */
    public function show($slug)
    {
        try {
            $event = Event::where('slug', $slug)
                ->where('status', 'published')
                ->firstOrFail();

            // Increment view count
            $event->increment('views_count');

            // Get related events
            $relatedEvents = Event::where('status', 'published')
                ->where('id', '!=', $event->id)
                ->where(function ($query) use ($event) {
                    $query->where('type', $event->type)
                          ->orWhere('location', $event->location);
                })
                ->limit(3)
                ->get();

            // Track page view
            $this->analyticsService->trackPageView('event', $event->id);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => $event->title,
                'description' => $event->description ? substr(strip_tags($event->description), 0, 160) :
                               "Event: {$event->title} - " . $event->start_date->format('F j, Y'),
                'type' => 'event',
                'image' => $event->featured_image_url
            ]);

            return view('website::pages.events.show', compact('event', 'relatedEvents'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * API: Get calendar data
     */
    public function getCalendarData(Request $request)
    {
        try {
            $start = $request->get('start');
            $end = $request->get('end');

            $events = Event::where('status', 'published');

            if ($start) {
                $events->where('start_date', '>=', $start);
            }
            if ($end) {
                $events->where('end_date', '<=', $end);
            }

            $events = $events->get()->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->start_date->toISOString(),
                    'end' => $event->end_date ? $event->end_date->toISOString() : null,
                    'url' => route('website.events.show', $event->slug),
                    'backgroundColor' => $this->getEventTypeColor($event->type),
                    'borderColor' => $this->getEventTypeColor($event->type),
                    'textColor' => '#ffffff'
                ];
            });

            return response()->json($events);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get upcoming events
     */
    public function getUpcoming(Request $request, $limit = 5)
    {
        try {
            $type = $request->get('type');

            $events = Event::where('status', 'published')
                ->where('start_date', '>=', now());

            if ($type) {
                $events->where('type', $type);
            }

            $events = $events->orderBy('start_date', 'asc')
                           ->limit($limit)
                           ->get();

            return response()->json([
                'success' => true,
                'events' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get featured events
     */
    public function getFeatured(Request $request)
    {
        try {
            $limit = $request->get('limit', 3);

            $events = Event::where('status', 'published')
                ->where('is_featured', true)
                ->where('start_date', '>=', now())
                ->orderBy('start_date', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'events' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get event type color for calendar display
     */
    private function getEventTypeColor($type)
    {
        $colors = [
            'academic' => '#3498db',
            'sports' => '#e74c3c',
            'cultural' => '#9b59b6',
            'meeting' => '#f39c12',
            'holiday' => '#2ecc71',
            'examination' => '#e67e22',
            'other' => '#95a5a6'
        ];

        return $colors[$type] ?? $colors['other'];
    }
}
