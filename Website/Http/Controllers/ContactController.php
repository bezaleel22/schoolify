<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\ContactSubmission;
use Modules\Website\Services\ContactService;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Modules\Website\Http\Requests\ContactFormRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    protected $contactService;
    protected $seoService;
    protected $analyticsService;

    public function __construct(
        ContactService $contactService,
        SEOService $seoService,
        AnalyticsService $analyticsService
    ) {
        $this->contactService = $contactService;
        $this->seoService = $seoService;
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the contact page
     */
    public function index()
    {
        try {
            // Get contact information
            $contactInfo = Cache::remember('contact_info', 3600, function () {
                return $this->contactService->getContactInformation();
            });

            // Track page view
            $this->analyticsService->trackPageView('contact');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Contact Us',
                'description' => 'Get in touch with our school. Find our contact information, location, and send us a message.',
                'type' => 'page'
            ]);

            return view('website::pages.contact', compact('contactInfo'));
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new contact form submission
     */
    public function store(ContactFormRequest $request)
    {
        try {
            // Rate limiting
            $executed = RateLimiter::attempt(
                'contact-form:' . $request->ip(),
                3, // 3 attempts
                function () use ($request) {
                    return $this->contactService->submitContactForm($request->validated());
                },
                3600 // per hour
            );

            if (!$executed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many contact form submissions. Please try again later.'
                ], 429);
            }

            // Track form submission
            $this->analyticsService->trackPageView('contact_form_submit', null, [
                'form_type' => $request->get('inquiry_type', 'general')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Thank you for your message. We will get back to you soon!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }

    /**
     * Get contact information API
     */
    public function getContactInfo()
    {
        try {
            $contactInfo = Cache::remember('contact_info', 3600, function () {
                return $this->contactService->getContactInformation();
            });

            return response()->json([
                'success' => true,
                'data' => $contactInfo
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get office hours
     */
    public function getOfficeHours()
    {
        try {
            $officeHours = Cache::remember('office_hours', 3600, function () {
                return $this->contactService->getOfficeHours();
            });

            return response()->json([
                'success' => true,
                'data' => $officeHours
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get frequently asked questions
     */
    public function getFAQs()
    {
        try {
            $faqs = Cache::remember('contact_faqs', 3600, function () {
                return $this->contactService->getFAQs();
            });

            return response()->json([
                'success' => true,
                'data' => $faqs
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get department contacts
     */
    public function getDepartmentContacts()
    {
        try {
            $departments = Cache::remember('department_contacts', 3600, function () {
                return $this->contactService->getDepartmentContacts();
            });

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Check if contact form can be submitted (rate limiting check)
     */
    public function checkSubmissionStatus(Request $request)
    {
        try {
            $key = 'contact-form:' . $request->ip();
            $remaining = RateLimiter::remaining($key, 3);
            $availableAt = RateLimiter::availableAt($key);

            return response()->json([
                'success' => true,
                'can_submit' => $remaining > 0,
                'remaining_attempts' => $remaining,
                'reset_at' => $availableAt ? date('Y-m-d H:i:s', $availableAt) : null
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Submit quick contact (simplified form)
     */
    public function quickContact(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'message' => 'required|string|max:500',
                'phone' => 'nullable|string|max:20'
            ]);

            // Rate limiting
            $executed = RateLimiter::attempt(
                'quick-contact:' . $request->ip(),
                5, // 5 attempts
                function () use ($request) {
                    return $this->contactService->submitQuickContact($request->only([
                        'name', 'email', 'message', 'phone'
                    ]));
                },
                3600 // per hour
            );

            if (!$executed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many submissions. Please try again later.'
                ], 429);
            }

            // Track submission
            $this->analyticsService->trackPageView('quick_contact_submit');

            return response()->json([
                'success' => true,
                'message' => 'Thank you! We will contact you soon.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message. Please try again.'
            ], 500);
        }
    }

    /**
     * Request callback
     */
    public function requestCallback(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'phone' => 'required|string|max:20',
                'preferred_time' => 'required|string|in:morning,afternoon,evening',
                'message' => 'nullable|string|max:300'
            ]);

            // Rate limiting
            $executed = RateLimiter::attempt(
                'callback-request:' . $request->ip(),
                3,
                function () use ($request) {
                    return $this->contactService->requestCallback($request->only([
                        'name', 'phone', 'preferred_time', 'message'
                    ]));
                },
                3600
            );

            if (!$executed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many callback requests. Please try again later.'
                ], 429);
            }

            // Track request
            $this->analyticsService->trackPageView('callback_request', null, [
                'preferred_time' => $request->preferred_time
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Callback request submitted. We will call you soon!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit callback request.'
            ], 500);
        }
    }

    /**
     * Report an issue or complaint
     */
    public function reportIssue(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'issue_type' => 'required|string|in:technical,academic,facility,staff,other',
                'priority' => 'required|string|in:low,medium,high,urgent',
                'description' => 'required|string|max:1000',
                'student_id' => 'nullable|string|max:20'
            ]);

            // Rate limiting
            $executed = RateLimiter::attempt(
                'issue-report:' . $request->ip(),
                2,
                function () use ($request) {
                    return $this->contactService->reportIssue($request->only([
                        'name', 'email', 'issue_type', 'priority', 'description', 'student_id'
                    ]));
                },
                3600
            );

            if (!$executed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many issue reports. Please try again later.'
                ], 429);
            }

            // Track issue report
            $this->analyticsService->trackPageView('issue_report', null, [
                'issue_type' => $request->issue_type,
                'priority' => $request->priority
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Issue reported successfully. We will investigate and respond soon.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit issue report.'
            ], 500);
        }
    }

    /**
     * Schedule a visit or appointment
     */
    public function scheduleVisit(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:100',
                'email' => 'required|email|max:255',
                'phone' => 'required|string|max:20',
                'visit_type' => 'required|string|in:tour,meeting,consultation,enrollment',
                'preferred_date' => 'required|date|after:today',
                'preferred_time' => 'required|string',
                'number_of_visitors' => 'required|integer|min:1|max:10',
                'message' => 'nullable|string|max:500'
            ]);

            // Rate limiting
            $executed = RateLimiter::attempt(
                'visit-schedule:' . $request->ip(),
                2,
                function () use ($request) {
                    return $this->contactService->scheduleVisit($request->only([
                        'name', 'email', 'phone', 'visit_type', 'preferred_date', 
                        'preferred_time', 'number_of_visitors', 'message'
                    ]));
                },
                3600
            );

            if (!$executed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many visit requests. Please try again later.'
                ], 429);
            }

            // Track visit request
            $this->analyticsService->trackPageView('visit_schedule', null, [
                'visit_type' => $request->visit_type,
                'visitors_count' => $request->number_of_visitors
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visit request submitted. We will contact you to confirm the appointment.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to schedule visit.'
            ], 500);
        }
    }

    /**
     * Get available visit slots
     */
    public function getAvailableSlots(Request $request)
    {
        try {
            $date = $request->get('date');
            $visitType = $request->get('visit_type', 'tour');

            if (!$date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Date is required.'
                ], 400);
            }

            $availableSlots = $this->contactService->getAvailableTimeSlots($date, $visitType);

            return response()->json([
                'success' => true,
                'slots' => $availableSlots
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get available slots.'
            ], 500);
        }
    }
}