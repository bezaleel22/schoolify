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
use App\SmParent;
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

    public function comments(Request $request, $id, $exam_id)
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

                $params = ['id' => $id, 'exam_id' => $exam_id];
                return response()->json([
                    'student' => $student,
                    'preview' => false,
                    'title' => "Add Remark",
                    'url' => route('result.remark', $params),
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

            $this->updateRelation($id, $request->parent_id);

            Toastr::success('Remark added successfully', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
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

                $params = ['id' => $id, 'exam_id' => $exam_id];
                return response()->json([
                    'preview' => false,
                    'title' => "Add Performance Rating",
                    'url' => route('result.rating', $params),
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

            $this->updateRelation($id, $request->parent_id);
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
            $this->optimizeImage($student->student_photo);

            return response()->json([
                'preview' => true,
                'title' => "Result Preview",
                'url' => route('result.publish', $params),
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
        $fileName = md5("$id-$exam_id");
        $filePath = "result/$fileName.pdf";
        
        $student_id = $request->local_stu_id;
        $exam_type = $request->exam_id;
        $cacheKey = "{$student_id}_{$exam_type}";
        try {
            if ($request->has('local_stu_id')) {
                Cache::forget("result_$cacheKey");
                $result = Cache::remember("result_$cacheKey", now()->addDays(7), function () use ($student_id, $exam_type) {
                    return $this->getResultData($student_id, $exam_type, 'old');
                });

                return generatePDF($result, $student_id, $exam_type);
            }

            $cachedResult = Cache::get("result_{$id}_{$exam_id}");
            $result_data =  $cachedResult ?? $this->getResultData($id, $exam_id);
            $body = generatePDF($result_data, $id, $exam_id)->getBody();

            Storage::put($filePath, $body->getContents());
            return Storage::response($filePath);
        } catch (\Exception $e) {
            return response()->json(array_merge([
                'error' => 1,
                'message' => $e->getMessage(),
            ]), 400);
        }
    }



    public function publish(Request $request, $id, $exam_id)
    {
        $request->validate([
            'parent_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
        ]);
    
        $fileName = 'illustration.svg';
        $publicFilePath = public_path('uploads/settings' . $fileName);
        $storageFilePath = storage_path('app/uploaded_files/' . $fileName);
        if (!file_exists($publicFilePath) && file_exists($storageFilePath)) {
            File::copy($storageFilePath, $publicFilePath);
        }

        try {
            $type = "exam-$exam_id";
            $timeline = SmStudentTimeline::where('academic_id', getAcademicId())
                ->where('type', $type)
                ->where('staff_student_id', $id)
                ->first();

            if (!$timeline) {
                $params = ['id' => $id, 'exam_id' => $exam_id];
                $timeline = new SmStudentTimeline();
                $timeline->staff_student_id = $id;
                $timeline->type = $type;
                $timeline->title = $request->title;
                $timeline->date = Carbon::create(2024, 3, 22)->toDateString();
                $timeline->description = 'TERMLY SUMMARY OF PROGRESS REPORT';
                $timeline->visible_to_student = 1;
                $timeline->file = route('result.download', $params);
                $timeline->school_id = Auth::user()->school_id;
                $timeline->academic_id = getAcademicId();
                $timeline->save();
            }

            $category = $request->category;
            $contacts = Cache::remember("contacts-$category", now()->addDay(7), function () use ($category) {
                return $this->getContacts($category);
            });

            $parent = SmParent::findOrFail($request->parent_id);
            $stu = SmStudent::findOrFail($id);
            $stu->parent_id = $request->parent_id;
            $stu->save();

            $reciver_email = env('TEST_RECIEVER_EMAIL', $parent->guardians_email);
            $data = (object) [
                'subject' => 'Result Notification',
                'student_id' => $id,
                'exam_id' => $exam_id, //explode('-', $timeline->type)[1],
                'term' => $timeline->title,
                'title' => $timeline->description,
                'full_name' => $stu->getOriginal('full_name'),
                'reciver_email' => $reciver_email,
                'receiver_name' => $parent->fathers_name ?? $parent->mothers_name,
                'school_name' => schoolConfig()->site_title,
                'principal' => $contacts['principal'],
                'contact' => $contacts['contact'],
                'support' => $contacts['support'],
            ];
     
            @post_mail($data);

            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
            dd($e->getMessage());
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
                ->get(['id', 'parent_id', 'student_category_id', 'full_name as name']);

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
                $reciver_email = env('TEST_RECIEVER_EMAIL', $parent->guardians_email);
                $data = (object) [
                    'subject' => 'Result Notification',
                    'reciver_email' => $reciver_email,
                    'receiver_name' => $parent->fathers_name ?? $parent->mothers_name,
                    'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
                    'full_name' => $stu->name,
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
            Queue::push(function () {
                Artisan::call('queue:retry', ['id' => 'all']);
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

        $fileName = 'illustration.svg';
        $publicFilePath = public_path('uploads/settings' . $fileName);
        $storageFilePath = storage_path('app/uploaded_files/' . $fileName);
        if (!file_exists($publicFilePath) && file_exists($storageFilePath)) {
            File::copy($storageFilePath, $publicFilePath);
        }

        try {
            $students = SmStudent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->with(['studentTimeline', 'parents', 'category'])
                ->limit(2)
                ->get(['id', 'parent_id', 'student_category_id', 'full_name as name']);
            $data = [];
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
                $reciver_email = env('TEST_RECIEVER_EMAIL', $parent->guardians_email);
                $data = (object) [
                    'subject' => 'Result Notification',
                    'reciver_email' => $reciver_email,
                    'receiver_name' => $parent->fathers_name ?? $parent->mothers_name,
                    'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
                    'full_name' => $stu->name,
                    'principal' => $contacts['principal'],
                    'contact' => $contacts['contact'],
                    'support' => $contacts['support'],
                    'school_name' => schoolConfig()->site_title,
                    'session' => "$session->year - [$session->title]",
                    'links' => $this->generateLinks($timelines)
                ];

                @post_mail($data);
            }

            return view('result::mail', ['student' => $data]);
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
