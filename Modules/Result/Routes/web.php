<?php

use App\SmEmailSetting;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
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

Route::get('/mail', function () {
    // Example data (replace with actual data or logic as needed)
    $result_data = (object) [
        'student' => (object) [
            'full_name' => 'Godsgrace Brown',
            'parent_email' => 'onosbrown.saved@gmail.com',
            'parent_name' => 'Brown Bezaleel'
        ]
    ];

    $student = (object) [
        'student_id' => 1,
        'exam_id' => 'exam',
        'term' => 'FIRST TERM EXAMINATION 2024	',
        'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
        'filepath' => 'public/uploads/student/timeline/stu-0ac59f3663dd7437b7f264b58784476e.pdf',
        'full_name' => $result_data->student->full_name,
        'parent_email' => $result_data->student->parent_email,
        'parent_name' => $result_data->student->parent_name,
        'admin' => 'Miss. Abigal Ojone',
        "gender" => 'Female',
        'support' => '+2348096041650'
    ];

    $setting = SmEmailSetting::where('school_id', 1)
        ->where('active_status', 1)->first();

    if ($setting) {
        $details = (object)[
            'id' => 1,
            'sender_email' => $setting->from_email,
            'sender_name' => $setting->from_name,
            'subject' => 'Result Notification'
        ];

        for ($i = 1; $i <= 1000; $i++) {
            dispatch(new SendResultEmail($student, $details));
        }

        return response()->json(['message' => 'Email queued successfully']);
    }

    return view('result::mail', compact('student'));
});

Route::get('send-emails', 'ImportController@sendEmails')->name('result.send_emails');
Route::get('upload-data', 'ImportController@upload')->name('result.upload');
Route::get('download-result/{id}/{exam_id?}', 'ResultController@download')->name('result.download');
Route::post('publish/{id}', 'ResultController@publish')->name('result.publish');
Route::post('preview/{id}/{exam_id}', 'ResultController@preview')->name('result.preview');
Route::post('remark/{id}/{exam_id?}', 'ResultController@remark')->name('result.remark');
Route::post('comments/{id}', 'ResultController@comments')->name('result.comment');
Route::post('rating/{id}/{exam_id?}', 'ResultController@rating')->name('result.rating');

// Overrides
Route::get('student-view/{id}/{type?}', 'StudentController@show')->name('student_view');
Route::get('my-children/{id}', 'ParentController@myChildren')->name('my_children_result');
Route::get('utility', 'UtilityController@index')->name('utility');
