<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Website\Entities\NewsletterSubscriber;
use Modules\Website\Services\SEOService;
use Modules\Website\Services\AnalyticsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
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
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        try {
            // Rate limiting
            $executed = RateLimiter::attempt(
                'newsletter-subscribe:' . $request->ip(),
                5, // 5 attempts
                function () use ($request) {
                    return $this->processSubscription($request);
                },
                3600 // per hour
            );

            if (!$executed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many subscription attempts. Please try again later.'
                ], 429);
            }

            return response()->json([
                'success' => true,
                'message' => 'Thank you for subscribing! Please check your email to confirm your subscription.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to subscribe. Please try again.'
            ], 500);
        }
    }

    /**
     * Process the subscription
     */
    private function processSubscription(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:100',
            'preferences' => 'nullable|array',
            'preferences.*' => 'string|in:news,events,blog,announcements'
        ]);

        if ($validator->fails()) {
            throw new \Exception('Invalid input data.');
        }

        $email = $request->email;
        $name = $request->name;
        $preferences = $request->preferences ?: ['news', 'events'];

        // Check if already subscribed
        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing) {
            if ($existing->status === 'subscribed') {
                throw new \Exception('This email is already subscribed to our newsletter.');
            } else {
                // Reactivate subscription
                $existing->update([
                    'name' => $name ?: $existing->name,
                    'status' => 'pending',
                    'preferences' => $preferences,
                    'verification_token' => Str::random(64),
                    'subscribed_at' => now()
                ]);
                $subscriber = $existing;
            }
        } else {
            // Create new subscription
            $subscriber = NewsletterSubscriber::create([
                'email' => $email,
                'name' => $name,
                'status' => 'pending',
                'preferences' => $preferences,
                'verification_token' => Str::random(64),
                'subscribed_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        // Send verification email
        $this->sendVerificationEmail($subscriber);

        // Track subscription
        $this->analyticsService->trackPageView('newsletter_subscribe', null, [
            'preferences' => implode(',', $preferences)
        ]);

        return true;
    }

    /**
     * Verify email subscription
     */
    public function verify($token)
    {
        try {
            $subscriber = NewsletterSubscriber::where('verification_token', $token)
                ->where('status', 'pending')
                ->first();

            if (!$subscriber) {
                // Set SEO data for error page
                $this->seoService->setPageSEO([
                    'title' => 'Invalid Verification Link',
                    'description' => 'The newsletter verification link is invalid or has expired.',
                    'robots' => 'noindex,nofollow'
                ]);

                return view('website::pages.newsletter.verify-error');
            }

            // Verify subscription
            $subscriber->update([
                'status' => 'subscribed',
                'verified_at' => now(),
                'verification_token' => null
            ]);

            // Track verification
            $this->analyticsService->trackPageView('newsletter_verified');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Subscription Confirmed',
                'description' => 'Your newsletter subscription has been confirmed successfully.',
                'robots' => 'noindex,nofollow'
            ]);

            return view('website::pages.newsletter.verified', compact('subscriber'));

        } catch (\Exception $e) {
            return view('website::pages.newsletter.verify-error');
        }
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe($token)
    {
        try {
            $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

            if (!$subscriber) {
                // Set SEO data for error page
                $this->seoService->setPageSEO([
                    'title' => 'Invalid Unsubscribe Link',
                    'description' => 'The unsubscribe link is invalid.',
                    'robots' => 'noindex,nofollow'
                ]);

                return view('website::pages.newsletter.unsubscribe-error');
            }

            // Show unsubscribe confirmation page
            $this->seoService->setPageSEO([
                'title' => 'Unsubscribe from Newsletter',
                'description' => 'Confirm your newsletter unsubscription.',
                'robots' => 'noindex,nofollow'
            ]);

            return view('website::pages.newsletter.unsubscribe', compact('subscriber'));

        } catch (\Exception $e) {
            return view('website::pages.newsletter.unsubscribe-error');
        }
    }

    /**
     * Confirm unsubscription
     */
    public function confirmUnsubscribe(Request $request, $token)
    {
        try {
            $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid unsubscribe link.'
                ], 404);
            }

            $reason = $request->get('reason');
            $feedback = $request->get('feedback');

            // Update subscription status
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
                'unsubscribe_reason' => $reason,
                'unsubscribe_feedback' => $feedback
            ]);

            // Track unsubscription
            $this->analyticsService->trackPageView('newsletter_unsubscribed', null, [
                'reason' => $reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'You have been successfully unsubscribed from our newsletter.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unsubscribe. Please try again.'
            ], 500);
        }
    }

    /**
     * Update subscription preferences
     */
    public function updatePreferences(Request $request, $token)
    {
        try {
            $subscriber = NewsletterSubscriber::where('preferences_token', $token)
                ->where('status', 'subscribed')
                ->first();

            if (!$subscriber) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid preferences link.'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'preferences' => 'required|array|min:1',
                'preferences.*' => 'string|in:news,events,blog,announcements',
                'frequency' => 'nullable|string|in:daily,weekly,monthly'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid preferences data.',
                    'errors' => $validator->errors()
                ], 400);
            }

            // Update preferences
            $subscriber->update([
                'preferences' => $request->preferences,
                'frequency' => $request->frequency ?: $subscriber->frequency,
                'updated_at' => now()
            ]);

            // Track preference update
            $this->analyticsService->trackPageView('newsletter_preferences_updated', null, [
                'preferences' => implode(',', $request->preferences)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your preferences have been updated successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update preferences. Please try again.'
            ], 500);
        }
    }

    /**
     * Show preferences page
     */
    public function showPreferences($token)
    {
        try {
            $subscriber = NewsletterSubscriber::where('preferences_token', $token)
                ->where('status', 'subscribed')
                ->first();

            if (!$subscriber) {
                $this->seoService->setPageSEO([
                    'title' => 'Invalid Preferences Link',
                    'description' => 'The preferences link is invalid.',
                    'robots' => 'noindex,nofollow'
                ]);

                return view('website::pages.newsletter.preferences-error');
            }

            $availablePreferences = [
                'news' => 'School News & Updates',
                'events' => 'Events & Activities',
                'blog' => 'Blog Posts',
                'announcements' => 'Important Announcements'
            ];

            $frequencies = [
                'weekly' => 'Weekly',
                'monthly' => 'Monthly'
            ];

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Newsletter Preferences',
                'description' => 'Update your newsletter subscription preferences.',
                'robots' => 'noindex,nofollow'
            ]);

            return view('website::pages.newsletter.preferences', compact(
                'subscriber',
                'availablePreferences',
                'frequencies'
            ));

        } catch (\Exception $e) {
            return view('website::pages.newsletter.preferences-error');
        }
    }

    /**
     * Get newsletter statistics (for admin/analytics)
     */
    public function getStatistics()
    {
        try {
            $stats = [
                'total_subscribers' => NewsletterSubscriber::where('status', 'subscribed')->count(),
                'pending_verification' => NewsletterSubscriber::where('status', 'pending')->count(),
                'unsubscribed' => NewsletterSubscriber::where('status', 'unsubscribed')->count(),
                'recent_subscriptions' => NewsletterSubscriber::where('status', 'subscribed')
                    ->where('subscribed_at', '>=', now()->subDays(30))
                    ->count(),
                'preferences_breakdown' => NewsletterSubscriber::where('status', 'subscribed')
                    ->get()
                    ->pluck('preferences')
                    ->flatten()
                    ->countBy()
                    ->toArray()
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send verification email
     */
    private function sendVerificationEmail($subscriber)
    {
        $verificationUrl = route('website.newsletter.verify', $subscriber->verification_token);
        
        // This would typically use a proper email template and mailing service
        // For now, we'll simulate the email sending
        
        try {
            // Mail::send('website::emails.newsletter.verification', [
            //     'subscriber' => $subscriber,
            //     'verificationUrl' => $verificationUrl
            // ], function ($message) use ($subscriber) {
            //     $message->to($subscriber->email, $subscriber->name)
            //             ->subject('Confirm Your Newsletter Subscription');
            // });
            
            // For development, we'll just log the verification URL
            Log::info("Newsletter verification email would be sent to {$subscriber->email}: {$verificationUrl}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send newsletter verification email: " . $e->getMessage());
        }
    }

    /**
     * Clean up expired pending subscriptions
     */
    public function cleanupExpired()
    {
        try {
            $expiredCount = NewsletterSubscriber::where('status', 'pending')
                ->where('created_at', '<', now()->subDays(7))
                ->delete();

            return response()->json([
                'success' => true,
                'message' => "Cleaned up {$expiredCount} expired subscriptions."
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}