<?php

use Illuminate\Support\Facades\Route;
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

Route::prefix('result')->group(function() {
    Route::get('/', 'ResultController@index');
});

Route::post('publish', 'ResultController@publish')->name('result.publish');
Route::post('remark', 'ResultController@remark')->name('result.remark');
Route::post('rating', 'ResultController@rating')->name('result.rating'); 
Route::get('student-view/{id}/{type?}', 'ResultController@show')->name('student_view'); 
