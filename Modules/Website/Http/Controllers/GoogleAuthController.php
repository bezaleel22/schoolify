<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Modules\Website\Entities\BlogComment;
use Modules\Website\Services\AnalyticsService;
use Modules\Website\Services\SEOService;

class GoogleAuthController extends Controller
{
    protected $analyticsService;
    protected $seoService;

    public function __construct(
        AnalyticsService $analyticsService,
        SEOService $seoService
    ) {
        $this->analyticsService = $analyticsService;
        $this->seoService = $seoService;
    }

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle(Request $request)
    {
        try {
            // Store the intended URL to redirect back after authentication
            $intendedUrl = $request->get('redirect', url()->previous());
            session(['google_auth_redirect' => $intendedUrl]);

            // Track authentication attempt
            $this->analyticsService->trackPageView('google_auth_redirect');

            return Socialite::driver('google')
                ->redirect();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Unable to connect to Google. Please try again.');
        }
    }

    /**
     * Handle Google OAuth callback
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Get user from Google
            $googleUser = Socialite::driver('google')->user();

            // Check if user already exists
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update existing user's Google info
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'last_login_at' => now()
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'google_token' => $googleUser->token,
                    'google_refresh_token' => $googleUser->refreshToken,
                    'email_verified_at' => now(),
                    'password' => Hash::make(uniqid()), // Random password since they use Google auth
                    'role_id' => $this->getCommentUserRoleId(),
                    'last_login_at' => now()
                ]);

                // Track new user registration
                $this->analyticsService->trackPageView('google_auth_register', null, [
                    'user_id' => $user->id,
                    'registration_method' => 'google'
                ]);
            }

            // Login the user
            Auth::login($user, true);

            // Track successful login
            $this->analyticsService->trackPageView('google_auth_success', null, [
                'user_id' => $user->id,
                'login_method' => 'google'
            ]);

            // Get the intended redirect URL
            $redirectUrl = $this->sanitizeRedirectUrl(session('google_auth_redirect', route('website.blog.index')));
            session()->forget('google_auth_redirect');

            // Add success message
            session()->flash('success', 'Successfully logged in with Google!');

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            // Track failed login
            $this->analyticsService->trackPageView('google_auth_failed', null, [
                'error' => $e->getMessage()
            ]);

            return redirect()->route('website.blog.index')
                ->with('error', 'Authentication failed. Please try again.');
        }
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        try {
            $userId = Auth::id();

            // Track logout
            if ($userId) {
                $this->analyticsService->trackPageView('google_auth_logout', null, [
                    'user_id' => $userId
                ]);
            }

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('website.home')
                ->with('success', 'Successfully logged out.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Logout failed. Please try again.');
        }
    }

    /**
     * Show login page with Google OAuth option
     */
    public function showLogin(Request $request)
    {
        try {
            // If user is already logged in, redirect them
            if (Auth::check()) {
                return redirect()->intended(route('website.blog.index'));
            }

            // Track page view
            $this->analyticsService->trackPageView('login_page');

            // Set SEO data
            $this->seoService->setPageSEO([
                'title' => 'Login to Comment',
                'description' => 'Sign in with your Google account to join the conversation.',
                'robots' => 'noindex,nofollow'
            ]);

            return view('website::auth.login', [
                'redirect_url' => $request->get('redirect', url()->previous())
            ]);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Unable to load login page. Please try again.');
        }
    }

    /**
     * Get user profile information
     */
    public function profile()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $user = Auth::user();

            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'joined_at' => $user->created_at->format('F Y'),
                    'comment_count' => $this->getUserCommentCount($user->id)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to load profile.'
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $request->validate([
                'name' => 'required|string|max:255',
                'notification_preferences' => 'nullable|array'
            ]);

            $user = Auth::user();
            $user->name = $request->name;
            $user->notification_preferences = $request->notification_preferences ?? [];
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile.'
            ], 500);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(Request $request)
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $request->validate([
                'confirmation' => 'required|string|in:DELETE_MY_ACCOUNT'
            ]);

            $user = Auth::user();
            $userId = $user->id;
            // Delete or anonymize user's comments
            BlogComment::where('user_id', $userId)
                ->update([
                    'user_id' => null,
                    'user_name' => 'Deleted User',
                    'user_email' => null
                ]);

            // Log the account deletion
            $this->analyticsService->trackPageView('account_deleted', null, [
                'user_id' => $userId,
                'deletion_date' => now()
            ]);

            // Delete the user
            $user->delete();

            // Logout
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account.'
            ], 500);
        }
    }

    /**
     * Check authentication status
     */
    public function checkAuth()
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                return response()->json([
                    'authenticated' => true,
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar
                    ]
                ]);
            }

            return response()->json([
                'authenticated' => false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'authenticated' => false,
                'error' => 'Unable to check authentication status.'
            ]);
        }
    }

    /**
     * Revoke Google access token
     */
    public function revokeGoogleAccess()
    {
        try {
            if (!Auth::check()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated.'
                ], 401);
            }

            $user = Auth::user();

            // Revoke the token with Google (if needed)
            if ($user->google_token) {
                try {
                    // Call Google API to revoke token
                    $response = file_get_contents('https://oauth2.googleapis.com/revoke?token=' . $user->google_token);
                } catch (\Exception $e) {
                    // Continue even if revocation fails
                }
            }

            // Clear Google-related data
            $user->update([
                'google_id' => null,
                'google_token' => null,
                'google_refresh_token' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Google access revoked successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to revoke Google access.'
            ], 500);
        }
    }

    // Private helper methods

    /**
     * Get the role ID for comment users
     */
    private function getCommentUserRoleId()
    {
        // Get the default role for comment users from config or database
        // Try to find a role named 'Comment User' or similar, fallback to a default
        try {
            $role = DB::table('roles')
                ->where('name', 'Comment User')
                ->orWhere('name', 'User')
                ->orWhere('name', 'Student')
                ->first();

            return $role ? $role->id : 4; // Fallback to ID 4 if no role found
        } catch (\Exception $e) {
            // If there's any database error, return default
            return 4;
        }
    }

    /**
     * Get user's comment count
     */
    private function getUserCommentCount($userId)
    {
        return \Modules\Website\Entities\BlogComment::where('user_id', $userId)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Generate secure redirect URL
     */
    private function sanitizeRedirectUrl($url)
    {
        // Ensure the redirect URL is safe and belongs to our domain
        $parsed = parse_url($url);
        $allowedHosts = [config('app.url'), request()->getHost()];

        if (isset($parsed['host']) && !in_array($parsed['host'], $allowedHosts)) {
            return route('website.blog.index');
        }

        return $url;
    }
}
