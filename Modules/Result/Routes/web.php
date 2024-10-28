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

Route::get('download-result/{id}/{exam_id?}', 'ResultController@download')->name('result.download');
Route::post('publish/{id}', 'ResultController@publish')->name('result.publish');
Route::post('preview/{id}/{exam_id}', 'ResultController@preview')->name('result.preview');

Route::post('remark/{id}/{exam_id?}', 'ResultController@remark')->name('result.remark');
Route::post('comments/{id}', 'ResultController@comments')->name('result.comment');

Route::post('rating/{id}/{exam_id?}', 'ResultController@rating')->name('result.rating');

Route::get('student-view/{id}/{type?}', 'ResultController@show')->name('student_view'); 

