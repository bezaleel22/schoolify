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
use Modules\Website\Http\Controllers\BlogController;
use Modules\Website\Http\Controllers\WebsiteController;


Route::group(['middleware' => ['cors', 'json.response']], function () {
    // public routes

    // Route::post('/logout', 'AuthController@logout')->name('logout.api');


});


Route::middleware('auth:web')->group(function () {
    // our routes to be protected will go in here
    // Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');
});