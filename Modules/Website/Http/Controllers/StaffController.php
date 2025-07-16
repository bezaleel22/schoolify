<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\StaffMember;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Illuminate\Support\Facades\Cache;

class StaffController extends Controller
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
     * Display staff directory
     */
    public function index(Request $request)
    {
        try {
            $department = $request->get('department');
            $search = $request->get('search');
            $sortBy = $request->get('sort', 'name'); // name, department, position
            $perPage = $request->get('per_page', 12);

            $staff = StaffMember::where('status', 'active');

            if ($department) {
                $staff->where('department', $department);
            }

            if ($search) {
                $staff->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('position', 'like', "%{$search}%")
                          ->orWhere('department', 'like', "%{$search}%");
                });
            }

            switch ($sortBy) {
                case 'department':
                    $staff->orderBy('department', 'asc')->orderBy('last_name', 'asc');
                    break;
                case 'position':
                    $staff->orderBy('position', 'asc')->orderBy('last_name', 'asc');
                    break;
                default:
                    $staff->orderBy('last_name', 'asc')->orderBy('first_name', 'asc');
            }

            $staff = $staff->paginate($perPage);

            // Get departments for filter
            $departments = Cache::remember('staff.departments', 3600, function () {
                return StaffMember::where('status', 'active')
                    ->distinct()
                    ->pluck('department')
                    ->filter()
                    ->sort()
                    ->values();
            });

            // Track page view
            $this->analyticsService->trackPageView('staff_directory');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Staff Directory',
                'description' => 'Meet our dedicated staff members who make our school exceptional.',
                'type' => 'page'
            ]);

            return view('website::pages.staff.index', compact('staff', 'departments', 'department', 'search', 'sortBy'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display staff by department
     */
    public function department($department)
    {
        try {
            $staff = StaffMember::where('status', 'active')
                ->where('department', $department)
                ->orderBy('position_order', 'asc')
                ->orderBy('last_name', 'asc')
                ->paginate(12);

            if ($staff->isEmpty()) {
                abort(404);
            }

            // Track page view
            $this->analyticsService->trackPageView('staff_department', null, [
                'department' => $department
            ]);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => ucfirst($department) . ' Department Staff',
                'description' => "Meet our {$department} department staff members.",
                'type' => 'page'
            ]);

            return view('website::pages.staff.department', compact('staff', 'department'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Display leadership team
     */
    public function leadership()
    {
        try {
            $leadership = Cache::remember('staff.leadership', 3600, function () {
                return StaffMember::where('status', 'active')
                    ->where('is_leadership', true)
                    ->orderBy('leadership_order', 'asc')
                    ->orderBy('position_order', 'asc')
                    ->get();
            });

            // Track page view
            $this->analyticsService->trackPageView('staff_leadership');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'School Leadership',
                'description' => 'Meet our school leadership team and administrative staff.',
                'type' => 'page'
            ]);

            return view('website::pages.staff.leadership', compact('leadership'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display specific staff member
     */
    public function show($id)
    {
        try {
            $staff = StaffMember::where('status', 'active')
                ->findOrFail($id);

            // Increment view count
            $staff->increment('views_count');

            // Get related staff (same department)
            $relatedStaff = StaffMember::where('status', 'active')
                ->where('department', $staff->department)
                ->where('id', '!=', $staff->id)
                ->limit(4)
                ->get();

            // Track page view
            $this->analyticsService->trackPageView('staff_profile', $staff->id);

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => $staff->full_name . ' - ' . $staff->position,
                'description' => $staff->bio ? substr(strip_tags($staff->bio), 0, 160) : 
                               "Learn more about {$staff->full_name}, {$staff->position} at our school.",
                'type' => 'profile',
                'image' => $staff->photo_url
            ]);

            return view('website::pages.staff.show', compact('staff', 'relatedStaff'));
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * API: Get departments
     */
    public function getDepartments()
    {
        try {
            $departments = Cache::remember('staff.departments_with_counts', 3600, function () {
                return StaffMember::where('status', 'active')
                    ->selectRaw('department, COUNT(*) as staff_count')
                    ->whereNotNull('department')
                    ->groupBy('department')
                    ->orderBy('department')
                    ->get();
            });

            return response()->json([
                'success' => true,
                'departments' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Get featured staff
     */
    public function getFeatured(Request $request)
    {
        try {
            $limit = $request->get('limit', 6);
            $type = $request->get('type', 'featured'); // featured, leadership, new_staff

            $staff = StaffMember::where('status', 'active');

            switch ($type) {
                case 'leadership':
                    $staff->where('is_leadership', true)
                          ->orderBy('leadership_order', 'asc');
                    break;
                case 'new_staff':
                    $staff->orderBy('hire_date', 'desc');
                    break;
                default:
                    $staff->where('is_featured', true)
                          ->orderBy('feature_order', 'asc');
            }

            $staff = $staff->limit($limit)->get();

            return response()->json([
                'success' => true,
                'staff' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search staff members
     */
    public function search(Request $request)
    {
        try {
            $query = $request->get('q');
            $department = $request->get('department');
            $perPage = $request->get('per_page', 10);

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Search query must be at least 2 characters.'
                ], 400);
            }

            $staff = StaffMember::where('status', 'active')
                ->where(function ($q) use ($query) {
                    $q->where('first_name', 'like', "%{$query}%")
                      ->orWhere('last_name', 'like', "%{$query}%")
                      ->orWhere('position', 'like', "%{$query}%")
                      ->orWhere('department', 'like', "%{$query}%")
                      ->orWhere('bio', 'like', "%{$query}%");
                });

            if ($department) {
                $staff->where('department', $department);
            }

            $results = $staff->orderBy('last_name', 'asc')
                           ->paginate($perPage);

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
     * Get staff by position/role
     */
    public function getByPosition(Request $request, $position)
    {
        try {
            $staff = StaffMember::where('status', 'active')
                ->where('position', 'like', "%{$position}%")
                ->orderBy('position_order', 'asc')
                ->orderBy('last_name', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'staff' => $staff,
                'position' => $position
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get staff contact information
     */
    public function getContactInfo($id)
    {
        try {
            $staff = StaffMember::where('status', 'active')
                ->select(['id', 'first_name', 'last_name', 'position', 'department', 'email', 'phone', 'office_location'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'contact' => $staff
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}