<?php

use App\SmEmailSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\Result\Jobs\SendResultEmail;

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

Route::get('test-mail', 'ResultController@testEmails')->name('result.test_emails');
Route::get('resend-emails', 'ResultController@resendEmails')->name('result.resend_emails');
Route::get('send-emails', 'ResultController@sendEmails')->name('result.send_emails');
Route::get('download-result/{id}/{exam_id?}', 'ResultController@download')->name('result.download');
Route::post('publish/{id}', 'ResultController@publish')->name('result.publish');
Route::post('preview/{id}/{exam_id}', 'ResultController@preview')->name('result.preview');
Route::post('remark/{id}/{exam_id?}', 'ResultController@remark')->name('result.remark');
Route::post('comments/{id}', 'ResultController@comments')->name('result.comment');
Route::post('rating/{id}/{exam_id?}', 'ResultController@rating')->name('result.rating');
Route::post('upload-data', 'ImportController@upload')->name('result.upload');

// Overrides
Route::get('student-view/{id}/{type?}', 'StudentController@show')->name('student_view');
Route::get('my-children/{id}', 'ParentController@myChildren')->name('my_children_result');
Route::get('utility', 'UtilityController@index')->name('utility');
Route::get('email-sms-log', 'ResultController@emailLogs')->name('email-sms-log')->middleware('userRolePermission:email-sms-log');

Event::listen(JobFailed::class, function (JobFailed $event) {

    $msg = $event->exception->getMessage(); // Use the exception message as the description
    Log::error('Job failed: ' . $msg);
});
