<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\BlogController;
use Modules\Website\Http\Controllers\WebsiteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['middleware' => ['cors', 'json.response']], function () {
    Route::post('auth/login', 'AuthController@login');
    Route::get('/auth',  function () {
        return response()->json(['message' => 'healthy.'], 200);
    });

    Route::controller(BlogController::class)->as('webapp.')->group(function ($routes) {
        $routes->get('home', 'index')->name('home-page');
        $routes->get('blogs/{skip?}', 'index')->name('blog-list');
        $routes->get('blog-view/{id}', 'show')->name('blog-view');
        $routes->get('blog-comments/{id}/{skip?}', 'comments')->name('blog-comments');
        $routes->get('blog-edit-comment/{id}', 'destroy')->name('edit-comment');
        $routes->get('blog-delete-comment/{id}', 'destroy')->name('delete-comment');
        $routes->middleware(['auth', 'subdomain'])->group(function ($routes) {
            $routes->post('store-blog-comment', 'update')->name('store-blog-comment');
        });
    });

    Route::controller(WebsiteController::class)->as('webapp.')->group(function ($routes) {
        $routes->get('home', 'index')->name('home-page');
        $routes->middleware(['auth', 'subdomain'])->group(function ($routes) {
            $routes->post('store-blog-comment', 'update')->name('store-blog-comment');
        });
    });
});

Route::middleware('auth:api')->group(function () {
    // our routes to be protected will go in here
    // Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');
});
