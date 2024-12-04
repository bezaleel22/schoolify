<?php

namespace Modules\Result\Http\Controllers;

use App\SmStaff;
use Carbon\Carbon;
use App\SmExamType;
use App\SmStudentTimeline;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\SmEmailSetting;
use App\SmEmailSmsLog;
use App\SmStudent;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Modules\Result\Entities\ClassAttendance;
use Modules\Result\Entities\Comment;
use Modules\Result\Entities\CommentTag;
use Modules\Result\Entities\SmOldResult;
use Modules\Result\Entities\StudentRating;
use Modules\Result\Entities\TeacherRemark;
use Modules\Result\Jobs\SendResultEmail;
use Modules\Result\Traits\ResultTrait;
use Throwable;

class ResultController extends Controller
{
    private $resultService;
    protected $token = '';

    use ResultTrait;

    public function comments(Request $request, $id)
    {
        try {
            $student = (object)$request->student;
            $comments = Comment::with('tags')
                ->where('type', $request->type ?? 'neutral')
                ->where('is_flagged', $request->is_flagged ?? 0)
                ->whereHas('tags', function ($q) use ($request) {
                    $q->whereIn('comment_tag_id', $request->tag_ids ?? [1, 2]);
                })
                ->inRandomOrder()->take(7)
                ->get();

            foreach ($comments as $comment) {
                $comment->text = $this->transformComment($comment->text, $student);
            }

            return response()->json([
                'student' => $student,
                'content' => view('result::partials.comments', compact('comments'))->render(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function remark(Request $request, $id, $exam_id)
    {
        try {
            if ($request->ajax()) {
                $student = (object)$request->student;
                $comments = Comment::with('tags')
                    ->where('type', 'neutral')
                    ->whereHas('tags', function ($q) {
                        $q->whereIn('comment_tag_id', [1, 2]);
                    })
                    ->inRandomOrder()->take(7)
                    ->get();

                foreach ($comments as $comment) {
                    $comment->text = $this->transformComment($comment->text, $student);
                }

                $tags = CommentTag::all();
                $remark = TeacherRemark::where('student_id', $id)
                    ->where('exam_type_id', $request->type_id)
                    ->first();


                return response()->json([
                    'student' => $student,
                    'preview' => false,
                    'title' => "Add Remark",
                    'content' => view('result::partials.remark', compact('remark', 'tags', 'comments', 'id', 'exam_id', 'student'))->render(),
                ]);
            }

            $user_id = Auth::user()->id;
            $teacher = SmStaff::where('user_id', $user_id)->whereIn('role_id', [1, 4, 5])->first();
            if (!$teacher) {
                Toastr::error("You are not authorized to add remarks", 'Error');
                return redirect()->back();
            }

            TeacherRemark::upsert(
                [
                    'remark' => $request->teacher_remark,
                    'teacher_id' => $teacher->id,
                    'student_id' => $id,
                    'exam_type_id' => $exam_id,
                    'academic_id' => getAcademicId()
                ],
                ['student_id', 'exam_type_id'],
                ['remark', 'teacher_id']
            );

            Toastr::success('Remark added successfully', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]);
            }
            dd($e->getMessage());
            Toastr::error('Operation failed', 'Failed');
            return redirect()->back()->with(['studentExam' => 'active']);
        }
    }

    public function rating(Request $request, $id, $exam_id)
    {
        try {
            if ($request->ajax()) {
                $attendance = ClassAttendance::where('student_id', $id)
                    ->where('exam_type_id', $exam_id)
                    ->first();

                $ratings = StudentRating::where('student_id', $id)
                    ->where('exam_type_id', $request->exam_type_id)
                    ->get();

                $attributes = [
                    "Adherent and Independent",
                    "Self-Control and Interaction",
                    "Flexibility and Creativity",
                    "Meticulous",
                    "Neatness",
                    "Overall Progress"
                ];

                $student = (object)$request->student;
                return response()->json([
                    'preview' => false,
                    'title' => "Add Performance Rating",
                    'student' => $student,
                    'content' => view('result::partials.ratings', compact('student', 'ratings', 'attendance', 'attributes', 'exam_id'))->render(),
                ]);
            }

            if (!isset($request->ratings) || !is_array($request->ratings)) {
                Toastr::error('No ratings provided', 'Error');
                return redirect()->back();
            }

            $academic_id = getAcademicId();
            $studentRatings = [];
            foreach ($request->ratings as $rating) {
                $map = mapRating((int)$rating['rate']);
                $studentRatings[] = [
                    'rate' => $rating['rate'],
                    'attribute' => $rating['attribute'],
                    'color' => $map['color'],
                    'remark' => $map['remark'],
                    'exam_type_id' => $request->exam_type_id,
                    'student_id' => $id,
                    'academic_id' => $academic_id,
                ];
            }

            StudentRating::upsert(
                $studentRatings,
                ['student_id', 'exam_type_id'],
                ['rate', 'color', 'remark', 'attribute', 'updated_at']
            );

            ClassAttendance::upsert(
                [
                    'days_opened' => $request->opened,
                    'days_present' => $request->present,
                    'days_absent' => $request->absent,
                    'student_id' => $id,
                    'exam_type_id' => $exam_id,
                    'academic_id' => $academic_id
                ],
                ['student_id', 'exam_type_id'],
                ['days_opened', 'days_present', 'days_absent']
            );

            Toastr::success('Rating added successfully', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
            Log::error('Error in rating method: ' . $e->getMessage());
            if ($request->ajax()) {
                return response()->json([
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]);
            }
            Toastr::error('Operation failed', 'Failed');
            return redirect()->back()->with(['studentExam' => 'active']);
        }
    }


    public function preview(Request $request, $id, $exam_id)
    {
        try {
            $cacheKey = "result_{$id}_{$exam_id}";
            $cachedResult = Cache::get($cacheKey);
            $result_data =  $cachedResult ?? $this->getResultData($id, $exam_id);

            $exam_type = SmExamType::findOrFail($exam_id);
            $params = ['id' => $id, 'exam_id' => $exam_type->id];
            $student = $result_data->student;

            return response()->json([
                'preview' => true,
                'title' => "Result Preview",
                'pdfUrl' => route('result.download', $params),
                'content' => view('result::partials.preview', compact('student'))->render(),
            ]);
        } catch (\Exception $e) {
            Log::error('PDF preview generation failed: ' . $e->getMessage());
            return response()->json([
                'error' => 1,
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function download(Request $request, $id, $exam_id = null)
    {
        $student_id = $request->local_stu_id;
        $exam_type = $request->exam_id;
        $cacheKey = "{$student_id}_{$exam_type}";
        try {
            if ($request->has('local_stu_id')) {
                $result = Cache::remember("result_$cacheKey", now()->addDays(7), function () use ($student_id, $exam_type) {
                    return $this->getResultData($student_id, $exam_type, 'old');
                });

                return generatePDF($result, $student_id, $exam_type);
            }

            $cachedResult = Cache::get("result_{$id}_{$exam_id}");
            $result_data =  $cachedResult ?? $this->getResultData($id, $exam_id);

            return generatePDF($result_data, $id, $exam_id);
        } catch (\Exception $e) {
            return response()->json(array_merge([
                'error' => 1,
                'message' => $e->getMessage(),
            ]), 400);
        }
    }



    public function publish(Request $request, $id)
    {
        $request->validate([
            'filepath' => 'required|string',
            'exam_id' => 'required|integer',
            'title' => 'required|string|max:255',
        ]);

        try {
            if ($request->filepath != "") {
                $timeline = new SmStudentTimeline();
                $timeline->staff_student_id = $id;
                $timeline->type = "exam-$request->exam_id";
                $timeline->title = $request->title;
                $timeline->date = Carbon::create(2024, 3, 22)->toDateString();
                $timeline->description = 'TERMLY SUMMARY OF PROGRESS REPORT';
                $timeline->visible_to_student = 1;
                $timeline->file = $request->filepath;
                $timeline->school_id = Auth::user()->school_id;
                $timeline->academic_id = getAcademicId();
                $timeline->save();

                $category = $request->category;
                $contacts = Cache::remember("contacts-$category", now()->addDay(7), function () use ($category) {
                    return $this->getContacts($category);
                });

                $data = (object) [
                    'subject' => 'Result Notification',
                    'reciver_email' => $request->parent_email,
                    'receiver_name' => $request->parent_name,
                    'student_id' => $id,
                    'exam_id' => $timeline->type,
                    'term' => $timeline->title,
                    'title' => $timeline->description,
                    'full_name' => $request->full_name,
                    'parent_email' => $request->parent_email,
                    'parent_name' => $request->parent_name,
                    "gender" => $request->gender_id,
                    'principal' => $contacts['principal'],
                    'contact' => $contacts['contact'],
                    'support' => $contacts['support'],
                ];

                @post_mail($data);
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
            Log::error('Failed to publish timeline: ' . $e->getMessage());
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentExam' => 'active']);
        }
    }

    public function sendEmails()
    {
        try {
            $students = SmStudent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->with(['studentTimeline', 'parents', 'category'])
                ->get(['id', 'full_name']);

            foreach ($students as $stu) {
                $timelines = $stu->studentTimeline;
                $parent = $stu->parents;
                if (empty($timelines) || !$parent) {
                    continue;
                }

                $category = $stu->category->category_name;
                $contacts = Cache::remember("contacts-$category", now()->addMinutes(5), function () use ($category) {
                    return $this->getContacts($category);
                });

                $session = getSession();
                $data = (object) [
                    'subject' => 'Result Notification',
                    'reciver_email' => $parent->parent_email,
                    'receiver_name' => $parent->parent_name,
                    'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
                    'full_name' => $stu->full_name,
                    'parent_email' => $parent->parent_email,
                    'parent_name' => $parent->parent_name,
                    'principal' => $contacts['principal'],
                    'contact' => $contacts['contact'],
                    'support' => $contacts['support'],
                    'school_name' => schoolConfig()->site_title,
                    'session' => "$session->year - [$session->title]",
                    'links' => $this->generateLinks($timelines)
                ];

                @post_mail($data);
            }

            // Success response
            Toastr::success('Emails Queued successfully', 'Success');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        } catch (\Exception $e) {
            Log::error('Failed to send result emails: ' . $e->getMessage());
            Toastr::error('Operation Failed: ' . $e->getMessage(), 'Failed');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        }
    }

    public function resendEmails()
    {
        try {
            $emailSmsLogs = SmEmailSmsLog::where('academic_id', getAcademicId())
                ->where('title', 'failed')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $queue = $emailSmsLogs[0]->send_through;
            Queue::push(function () use ($queue) {
                Artisan::call('queue:retry', ['queue' => $queue]);
            });

            Toastr::success('Operation Successful', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function emailLogs()
    {
        try {
            $emailSmsLogs = SmEmailSmsLog::where('academic_id', getAcademicId())
                ->orderBy('id', 'DESC')
                ->where('school_id', Auth::user()->school_id)
                ->get();

            return view('result::emailSmsLog', compact('emailSmsLogs'));
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function testEmails()
    {
        try {
            $uploadedDir = storage_path('app/uploaded_files');
            $finalFilePath = "$uploadedDir/student.zip";
            $this->unzip($finalFilePath);
            return $finalFilePath;


            $result = Cache::remember("65-4", now()->addSeconds(5), function () {
                return SmOldResult::queryResultData(65, 4);
            });

            $result_data = (object) [
                'student' => (object) [
                    'full_name' => 'Godsgrace Brown',
                    'parent_email' => 'onosbrown.saved@gmail.com',
                    'parent_name' => 'Brown Bezaleel'
                ]
            ];

            $category = 'EYFS';
            Cache::forget("contacts-$category");
            $contacts = Cache::remember("contacts-$category", now()->addSeconds(5), function () use ($category) {
                return $this->getContacts($category);
            });

            // $student = (object) [
            //     'student_id' => 1,
            //     'exam_id' => 'exam',
            //     'term' => 'FIRST TERM EXAMINATION 2024	',
            //     'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
            //     'full_name' => $result_data->student->full_name,
            //     'parent_email' => $result_data->student->parent_email,
            //     'parent_name' => $result_data->student->parent_name,
            //     'principal' => $contacts['principal'],
            //     'contact' => $contacts['contact'],
            //     'support' => $contacts['support'],
            //     'school_name' => generalSetting()->site_title,
            //     'links' =>  [],
            // ];
            $setup = schoolConfig();
            $session = getSession();
            $student = (object) [
                'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
                'full_name' => $result_data->student->full_name,
                'parent_email' => $result_data->student->parent_email,
                'parent_name' => $result_data->student->parent_name,
                'principal' => $contacts['principal'],
                'contact' => $contacts['contact'],
                'support' => $contacts['support'],
                'school_name' => $setup->site_title,
                'session' => "$session->year - [$session->title]",
                'links' => $this->generateLinks(65)
            ];

            return view('result::mail', compact('student'));

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
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    protected function unzip($zipfile)
    {
        dispatch(function () use ($zipfile) {
            $extractDir = public_path('uploads/' . pathinfo($zipfile, PATHINFO_FILENAME));
            if (file_exists($extractDir) && is_dir($extractDir)) {
                return true; // Already unzipped
            }

            if (!file_exists($extractDir)) {
                mkdir($extractDir, 0755, true);
            }

            $zip = new \ZipArchive;
            if ($zip->open($zipfile) === true) {
                $extracted = $zip->extractTo($extractDir);
                $zip->close();

                if (!$extracted) {
                    Log::error("Failed to extract the zip file: $zipfile to $extractDir");
                    File::deleteDirectory($extractDir);
                }
                Log::error("Failed to open the zip file: $zipfile");
            }
        });
    }
}
