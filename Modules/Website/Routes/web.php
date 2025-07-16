<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\WebsiteController;
use Modules\Website\Http\Controllers\BlogController;
use Modules\Website\Http\Controllers\ContactController;
use Modules\Website\Http\Controllers\GoogleAuthController;
use Modules\Website\Http\Controllers\EventController;
use Modules\Website\Http\Controllers\GalleryController;
use Modules\Website\Http\Controllers\StaffController;
use Modules\Website\Http\Controllers\SearchController;
use Modules\Website\Http\Controllers\NewsletterController;
use Modules\Website\Http\Controllers\FeedController;

// Public routes (no authentication required)
Route::group(['as' => 'website.'], function () {
    
    // Homepage and main pages
    Route::get('/', [WebsiteController::class, 'index'])->name('home');
    Route::get('/about', [WebsiteController::class, 'about'])->name('about');
    Route::get('/admission', [WebsiteController::class, 'admission'])->name('admission');
    Route::get('/academics', [WebsiteController::class, 'academics'])->name('academics');
    Route::get('/portfolio', [WebsiteController::class, 'portfolio'])->name('portfolio');
    Route::get('/news', [WebsiteController::class, 'news'])->name('news');
    
    // SEO routes
    Route::get('/sitemap.xml', [WebsiteController::class, 'sitemap'])->name('sitemap');
    Route::get('/robots.txt', [WebsiteController::class, 'robots'])->name('robots');
    
    // Search functionality
    Route::get('/search', [WebsiteController::class, 'search'])->name('search');
    Route::get('/search/autocomplete', [WebsiteController::class, 'searchAutocomplete'])->name('search.autocomplete');
    
    // Legal pages
    Route::get('/privacy', [WebsiteController::class, 'privacy'])->name('privacy');
    Route::get('/terms', [WebsiteController::class, 'terms'])->name('terms');
    
    // Dynamic pages
    Route::get('/page/{slug}', [WebsiteController::class, 'page'])->name('page');
    
    // Blog routes
    Route::group(['prefix' => 'blog', 'as' => 'blog.'], function () {
        Route::get('/', [BlogController::class, 'index'])->name('index');
        Route::get('/rss', [BlogController::class, 'rss'])->name('rss');
        Route::get('/atom', [BlogController::class, 'atom'])->name('atom');
        Route::get('/category/{slug}', [BlogController::class, 'category'])->name('category');
        Route::get('/tag/{tag}', [BlogController::class, 'tag'])->name('tag');
        Route::get('/author/{id}', [BlogController::class, 'author'])->name('author');
        Route::get('/archive/{year}/{month?}', [BlogController::class, 'archive'])->name('archive');
        Route::get('/search', [BlogController::class, 'search'])->name('search');
        Route::get('/{slug}', [BlogController::class, 'show'])->name('show');
        
        // Comment routes (require authentication)
        Route::middleware(['auth'])->group(function () {
            Route::post('/{postId}/comments', [BlogController::class, 'storeComment'])->name('comments.store');
            Route::post('/{postId}/like', [BlogController::class, 'toggleLike'])->name('like');
            Route::post('/comments/{commentId}/report', [BlogController::class, 'reportComment'])->name('comments.report');
        });
        
        // Public API routes
        Route::get('/{postId}/comments', [BlogController::class, 'getComments'])->name('comments.get');
        Route::get('/{postId}/share', [BlogController::class, 'share'])->name('share');
        Route::get('/api/popular', [BlogController::class, 'popularPosts'])->name('api.popular');
        Route::get('/api/{postId}/related', [BlogController::class, 'relatedPosts'])->name('api.related');
    });
    
    // Contact routes
    Route::group(['prefix' => 'contact', 'as' => 'contact.'], function () {
        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::post('/quick', [ContactController::class, 'quickContact'])->name('quick');
        Route::post('/callback', [ContactController::class, 'requestCallback'])->name('callback');
        Route::post('/issue', [ContactController::class, 'reportIssue'])->name('issue');
        Route::post('/visit', [ContactController::class, 'scheduleVisit'])->name('visit');
        
        // API routes
        Route::get('/api/info', [ContactController::class, 'getContactInfo'])->name('api.info');
        Route::get('/api/hours', [ContactController::class, 'getOfficeHours'])->name('api.hours');
        Route::get('/api/faqs', [ContactController::class, 'getFAQs'])->name('api.faqs');
        Route::get('/api/departments', [ContactController::class, 'getDepartmentContacts'])->name('api.departments');
        Route::get('/api/status', [ContactController::class, 'checkSubmissionStatus'])->name('api.status');
        Route::get('/api/slots', [ContactController::class, 'getAvailableSlots'])->name('api.slots');
    });
    
    // Google OAuth routes
    Route::group(['prefix' => 'auth', 'as' => 'auth.'], function () {
        Route::get('/login', [GoogleAuthController::class, 'showLogin'])->name('login');
        Route::get('/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google');
        Route::get('/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');
        Route::post('/logout', [GoogleAuthController::class, 'logout'])->name('logout');
        
        // Authenticated routes
        Route::middleware(['auth'])->group(function () {
            Route::get('/profile', [GoogleAuthController::class, 'profile'])->name('profile');
            Route::put('/profile', [GoogleAuthController::class, 'updateProfile'])->name('profile.update');
            Route::delete('/account', [GoogleAuthController::class, 'deleteAccount'])->name('account.delete');
            Route::post('/revoke-google', [GoogleAuthController::class, 'revokeGoogleAccess'])->name('google.revoke');
        });
        
        // Public API routes
        Route::get('/check', [GoogleAuthController::class, 'checkAuth'])->name('check');
    });
    
    // Events routes
    Route::group(['prefix' => 'events', 'as' => 'events.'], function () {
        Route::get('/', [EventController::class, 'index'])->name('index');
        Route::get('/calendar', [EventController::class, 'calendar'])->name('calendar');
        Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
        Route::get('/past', [EventController::class, 'past'])->name('past');
        Route::get('/type/{type}', [EventController::class, 'byType'])->name('type');
        Route::get('/{slug}', [EventController::class, 'show'])->name('show');
        
        // API routes
        Route::get('/api/calendar-data', [EventController::class, 'getCalendarData'])->name('api.calendar');
        Route::get('/api/upcoming/{limit?}', [EventController::class, 'getUpcoming'])->name('api.upcoming');
        Route::get('/api/featured', [EventController::class, 'getFeatured'])->name('api.featured');
    });
    
    // Gallery routes
    Route::group(['prefix' => 'gallery', 'as' => 'gallery.'], function () {
        Route::get('/', [GalleryController::class, 'index'])->name('index');
        Route::get('/albums', [GalleryController::class, 'albums'])->name('albums');
        Route::get('/album/{slug}', [GalleryController::class, 'album'])->name('album');
        Route::get('/image/{id}', [GalleryController::class, 'image'])->name('image');
        Route::get('/latest', [GalleryController::class, 'latest'])->name('latest');
        
        // API routes
        Route::get('/api/albums', [GalleryController::class, 'getAlbums'])->name('api.albums');
        Route::get('/api/album/{slug}/images', [GalleryController::class, 'getAlbumImages'])->name('api.album.images');
        Route::get('/api/recent/{limit?}', [GalleryController::class, 'getRecentImages'])->name('api.recent');
    });
    
    // Staff routes
    Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
        Route::get('/', [StaffController::class, 'index'])->name('index');
        Route::get('/department/{department}', [StaffController::class, 'department'])->name('department');
        Route::get('/leadership', [StaffController::class, 'leadership'])->name('leadership');
        Route::get('/{id}', [StaffController::class, 'show'])->name('show');
        
        // API routes
        Route::get('/api/departments', [StaffController::class, 'getDepartments'])->name('api.departments');
        Route::get('/api/featured', [StaffController::class, 'getFeatured'])->name('api.featured');
    });
    
    // Newsletter routes
    Route::group(['prefix' => 'newsletter', 'as' => 'newsletter.'], function () {
        Route::post('/subscribe', [NewsletterController::class, 'subscribe'])->name('subscribe');
        Route::get('/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])->name('unsubscribe');
        Route::get('/verify/{token}', [NewsletterController::class, 'verify'])->name('verify');
        Route::post('/preferences/{token}', [NewsletterController::class, 'updatePreferences'])->name('preferences');
    });
    
    // Feed routes
    Route::group(['prefix' => 'feeds', 'as' => 'feeds.'], function () {
        Route::get('/blog.rss', [FeedController::class, 'blogRss'])->name('blog.rss');
        Route::get('/blog.atom', [FeedController::class, 'blogAtom'])->name('blog.atom');
        Route::get('/events.rss', [FeedController::class, 'eventsRss'])->name('events.rss');
        Route::get('/news.rss', [FeedController::class, 'newsRss'])->name('news.rss');
    });
    
    // Advanced search routes
    Route::group(['prefix' => 'advanced-search', 'as' => 'search.'], function () {
        Route::get('/', [SearchController::class, 'index'])->name('advanced');
        Route::post('/', [SearchController::class, 'search'])->name('advanced.post');
        Route::get('/suggest', [SearchController::class, 'suggestions'])->name('suggestions');
        Route::get('/popular', [SearchController::class, 'popularQueries'])->name('popular');
    });
});

// Rate limited routes
Route::middleware(['throttle:60,1'])->group(function () {
    // High-frequency API routes that need rate limiting
    Route::get('/api/search/autocomplete', [WebsiteController::class, 'searchAutocomplete']);
    Route::post('/api/contact/quick', [ContactController::class, 'quickContact']);
});

// Special routes for SEO and crawlers
Route::get('/404-suggestions', [WebsiteController::class, 'notFound'])->name('website.404');

// Catch-all route for dynamic pages (should be last)
Route::fallback(function () {
    return redirect()->route('website.404');
});