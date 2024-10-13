<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->group(function () {
    Route::prefix('blog')->group(function () {
        Route::get('/', 'BlogController@index');
        Route::get('/show/{id}', 'BlogController@show');
        Route::get('/comments/{blog_id}', 'BlogController@comments');
        Route::get('/update/{id}', 'BlogController@update');
        Route::get('/destroy/{id}', 'BlogController@destroy');
    });

    Route::prefix('event')->group(function () {
        Route::get('/', 'WebsiteController@index');
    });
});
