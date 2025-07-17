<?php

namespace Modules\Website\Services;

use Modules\Website\Entities\ContactSubmission;
use Modules\Website\Services\CacheService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ContactService
{
    protected $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get contact information
     */
    public function getContactInformation()
    {
        return [
            'main' => [
                'phone' => '+1 (555) 123-4567',
                'email' => 'info@school.edu',
                'address' => '123 Education Street, Learning City, State 12345',
                'hours' => 'Monday - Friday: 8:00 AM - 5:00 PM'
            ],
            'departments' => [
                'admissions' => [
                    'name' => 'Admissions Office',
                    'phone' => '+1 (555) 123-4568',
                    'email' => 'admissions@school.edu',
                    'hours' => 'Monday - Friday: 8:00 AM - 4:00 PM'
                ],
                'academic' => [
                    'name' => 'Academic Affairs',
                    'phone' => '+1 (555) 123-4569',
                    'email' => 'academic@school.edu',
                    'hours' => 'Monday - Friday: 9:00 AM - 5:00 PM'
                ],
                'finance' => [
                    'name' => 'Finance Office',
                    'phone' => '+1 (555) 123-4570',
                    'email' => 'finance@school.edu',
                    'hours' => 'Monday - Friday: 8:30 AM - 4:30 PM'
                ]
            ],
            'social_media' => [
                'facebook' => 'https://facebook.com/school',
                'twitter' => 'https://twitter.com/school',
                'instagram' => 'https://instagram.com/school',
                'youtube' => 'https://youtube.com/school'
            ],
            'location' => [
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'map_url' => 'https://maps.google.com/?q=School+Address'
            ]
        ];
    }

    /**
     * Submit contact form
     */
    public function submitContactForm($data)
    {
        // Store submission in database
        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? 'General Inquiry',
            'message' => $data['message'],
            'inquiry_type' => $data['inquiry_type'] ?? 'general',
            'preferred_contact_method' => $data['preferred_contact_method'] ?? 'email',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'submitted_at' => now(),
            'status' => 'new'
        ]);

        // Send notification emails
        $this->sendContactNotifications($submission);

        return $submission;
    }

    /**
     * Submit quick contact
     */
    public function submitQuickContact($data)
    {
        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => 'Quick Contact',
            'message' => $data['message'],
            'inquiry_type' => 'quick',
            'preferred_contact_method' => 'email',
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'submitted_at' => now(),
            'status' => 'new'
        ]);

        // Send quick notification
        $this->sendQuickContactNotification($submission);

        return $submission;
    }

    /**
     * Request callback
     */
    public function requestCallback($data)
    {
        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'email' => null,
            'phone' => $data['phone'],
            'subject' => 'Callback Request',
            'message' => $data['message'] ?? 'Callback requested for ' . $data['preferred_time'],
            'inquiry_type' => 'callback',
            'preferred_contact_method' => 'phone',
            'additional_data' => json_encode([
                'preferred_time' => $data['preferred_time']
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'submitted_at' => now(),
            'status' => 'new'
        ]);

        $this->sendCallbackNotification($submission);

        return $submission;
    }

    /**
     * Report an issue
     */
    public function reportIssue($data)
    {
        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => null,
            'subject' => 'Issue Report - ' . ucfirst($data['issue_type']),
            'message' => $data['description'],
            'inquiry_type' => 'issue_report',
            'preferred_contact_method' => 'email',
            'additional_data' => json_encode([
                'issue_type' => $data['issue_type'],
                'priority' => $data['priority'],
                'student_id' => $data['student_id'] ?? null
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'submitted_at' => now(),
            'status' => 'new'
        ]);

        $this->sendIssueReportNotification($submission);

        return $submission;
    }

    /**
     * Schedule a visit
     */
    public function scheduleVisit($data)
    {
        $submission = ContactSubmission::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'subject' => 'Visit Request - ' . ucfirst($data['visit_type']),
            'message' => $data['message'] ?? '',
            'inquiry_type' => 'visit_request',
            'preferred_contact_method' => 'email',
            'additional_data' => json_encode([
                'visit_type' => $data['visit_type'],
                'preferred_date' => $data['preferred_date'],
                'preferred_time' => $data['preferred_time'],
                'number_of_visitors' => $data['number_of_visitors']
            ]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'submitted_at' => now(),
            'status' => 'new'
        ]);

        $this->sendVisitRequestNotification($submission);

        return $submission;
    }

    /**
     * Get office hours
     */
    public function getOfficeHours()
    {
        return [
            'main_office' => [
                'monday' => '8:00 AM - 5:00 PM',
                'tuesday' => '8:00 AM - 5:00 PM',
                'wednesday' => '8:00 AM - 5:00 PM',
                'thursday' => '8:00 AM - 5:00 PM',
                'friday' => '8:00 AM - 5:00 PM',
                'saturday' => '9:00 AM - 1:00 PM',
                'sunday' => 'Closed'
            ],
            'admissions' => [
                'monday' => '8:00 AM - 4:00 PM',
                'tuesday' => '8:00 AM - 4:00 PM',
                'wednesday' => '8:00 AM - 4:00 PM',
                'thursday' => '8:00 AM - 4:00 PM',
                'friday' => '8:00 AM - 4:00 PM',
                'saturday' => '9:00 AM - 12:00 PM',
                'sunday' => 'Closed'
            ],
            'library' => [
                'monday' => '7:00 AM - 8:00 PM',
                'tuesday' => '7:00 AM - 8:00 PM',
                'wednesday' => '7:00 AM - 8:00 PM',
                'thursday' => '7:00 AM - 8:00 PM',
                'friday' => '7:00 AM - 6:00 PM',
                'saturday' => '9:00 AM - 4:00 PM',
                'sunday' => '1:00 PM - 6:00 PM'
            ]
        ];
    }

    /**
     * Get frequently asked questions
     */
    public function getFAQs()
    {
        return [
            [
                'category' => 'Admissions',
                'questions' => [
                    [
                        'question' => 'What are the admission requirements?',
                        'answer' => 'Please visit our admissions page for detailed requirements including academic qualifications, documents needed, and application deadlines.'
                    ],
                    [
                        'question' => 'When is the application deadline?',
                        'answer' => 'Application deadlines vary by program. Please check our admissions calendar for specific dates.'
                    ]
                ]
            ],
            [
                'category' => 'Academic',
                'questions' => [
                    [
                        'question' => 'What programs do you offer?',
                        'answer' => 'We offer a wide range of academic programs. Please visit our academics page for a complete list.'
                    ],
                    [
                        'question' => 'What is the student-teacher ratio?',
                        'answer' => 'Our student-teacher ratio varies by program but averages 15:1 to ensure personalized attention.'
                    ]
                ]
            ],
            [
                'category' => 'General',
                'questions' => [
                    [
                        'question' => 'How can I schedule a campus tour?',
                        'answer' => 'You can schedule a campus tour by filling out our visit request form or calling our admissions office.'
                    ],
                    [
                        'question' => 'What are your office hours?',
                        'answer' => 'Our main office is open Monday-Friday 8:00 AM - 5:00 PM, and Saturday 9:00 AM - 1:00 PM.'
                    ]
                ]
            ]
        ];
    }

    /**
     * Get department contacts
     */
    public function getDepartmentContacts()
    {
        return [
            'admissions' => [
                'name' => 'Admissions Office',
                'head' => 'Dr. Sarah Johnson',
                'phone' => '+1 (555) 123-4568',
                'email' => 'admissions@school.edu',
                'location' => 'Administration Building, Room 101'
            ],
            'academics' => [
                'name' => 'Academic Affairs',
                'head' => 'Prof. Michael Davis',
                'phone' => '+1 (555) 123-4569',
                'email' => 'academic@school.edu',
                'location' => 'Academic Building, Room 205'
            ],
            'student_affairs' => [
                'name' => 'Student Affairs',
                'head' => 'Ms. Emily Rodriguez',
                'phone' => '+1 (555) 123-4571',
                'email' => 'students@school.edu',
                'location' => 'Student Center, Room 110'
            ],
            'finance' => [
                'name' => 'Finance Office',
                'head' => 'Mr. David Thompson',
                'phone' => '+1 (555) 123-4570',
                'email' => 'finance@school.edu',
                'location' => 'Administration Building, Room 150'
            ],
            'it_support' => [
                'name' => 'IT Support',
                'head' => 'Mr. Alex Chen',
                'phone' => '+1 (555) 123-4572',
                'email' => 'itsupport@school.edu',
                'location' => 'Technology Center, Room 020'
            ]
        ];
    }

    /**
     * Get available time slots for visits
     */
    public function getAvailableTimeSlots($date, $visitType)
    {
        // Define available slots based on visit type
        $slots = [
            'tour' => [
                '09:00', '10:00', '11:00', '14:00', '15:00', '16:00'
            ],
            'meeting' => [
                '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
                '14:00', '14:30', '15:00', '15:30', '16:00', '16:30'
            ],
            'consultation' => [
                '09:00', '10:00', '11:00', '14:00', '15:00'
            ],
            'enrollment' => [
                '09:00', '10:00', '11:00', '14:00', '15:00'
            ]
        ];

        $availableSlots = $slots[$visitType] ?? $slots['tour'];

        // Filter out booked slots (you would check against a bookings table)
        $bookedSlots = $this->getBookedSlots($date, $visitType);
        $available = array_diff($availableSlots, $bookedSlots);

        // Filter out past times if the date is today
        if (Carbon::parse($date)->isToday()) {
            $currentTime = now()->format('H:i');
            $available = array_filter($available, function ($slot) use ($currentTime) {
                return $slot > $currentTime;
            });
        }

        return array_values($available);
    }

    // Private helper methods

    private function sendContactNotifications($submission)
    {
        // Send email to admin
        $this->sendAdminNotification($submission);

        // Send auto-reply to user
        $this->sendAutoReply($submission);
    }

    private function sendQuickContactNotification($submission)
    {
        // Simple notification for quick contacts
        $this->sendAdminNotification($submission, 'quick');
    }

    private function sendCallbackNotification($submission)
    {
        $this->sendAdminNotification($submission, 'callback');
    }

    private function sendIssueReportNotification($submission)
    {
        $this->sendAdminNotification($submission, 'issue');
    }

    private function sendVisitRequestNotification($submission)
    {
        $this->sendAdminNotification($submission, 'visit');
    }

    private function sendAdminNotification($submission, $type = 'general')
    {
        // In a real implementation, you would send actual emails
        // For now, we'll just log the notification
        Log::info("Contact notification sent: {$type}", [
            'submission_id' => $submission->id,
            'name' => $submission->name,
            'email' => $submission->email,
            'subject' => $submission->subject
        ]);
    }

    private function sendAutoReply($submission)
    {
        // Send auto-reply email to the user
        Log::info("Auto-reply sent to: {$submission->email}");
    }

    private function getBookedSlots($date, $visitType)
    {
        // In a real implementation, you would query a bookings table
        // For now, return some example booked slots
        return ['10:00', '15:00']; // Example booked slots
    }

    /**
     * Get contact form statistics
     */
    public function getContactStats($period = 30)
    {
        $startDate = now()->subDays($period);

        return [
            'total_submissions' => ContactSubmission::where('submitted_at', '>=', $startDate)->count(),
            'by_type' => ContactSubmission::where('submitted_at', '>=', $startDate)
                ->groupBy('inquiry_type')
                ->selectRaw('inquiry_type, COUNT(*) as count')
                ->pluck('count', 'inquiry_type'),
            'response_rate' => $this->calculateResponseRate($period),
            'average_response_time' => $this->calculateAverageResponseTime($period)
        ];
    }

    private function calculateResponseRate($period)
    {
        $total = ContactSubmission::where('submitted_at', '>=', now()->subDays($period))->count();
        $responded = ContactSubmission::where('submitted_at', '>=', now()->subDays($period))
            ->where('status', 'responded')->count();

        return $total > 0 ? round(($responded / $total) * 100, 2) : 0;
    }

    private function calculateAverageResponseTime($period)
    {
        $submissions = ContactSubmission::where('submitted_at', '>=', now()->subDays($period))
            ->where('status', 'responded')
            ->whereNotNull('responded_at')
            ->get();

        if ($submissions->isEmpty()) {
            return 0;
        }

        $totalHours = $submissions->sum(function ($submission) {
            return $submission->submitted_at->diffInHours($submission->responded_at);
        });

        return round($totalHours / $submissions->count(), 1);
    }
}