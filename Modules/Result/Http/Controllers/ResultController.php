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
use App\SmStudent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Result\Entities\ClassAttendance;
use Modules\Result\Entities\Comment;
use Modules\Result\Entities\CommentTag;
use Modules\Result\Entities\StudentRating;
use Modules\Result\Entities\TeacherRemark;
use Modules\Result\Jobs\SendResultEmail;
use Modules\Result\Traits\ResultTrait;

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
            $teacher = SmStaff::where('user_id', $user_id)->first();
            $remark = new TeacherRemark();
            // if ($teacher->id != 3) throw new \Exception("You are not a teacher");

            $remark->teachar_id = $teacher->id;
            $remark->remark = $request->teacher_remark;
            $remark->exam_type_id = $exam_id;
            $remark->student_id = $id;
            $remark->save();

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
                    ->where('exam_type_id', $exam_id) // Use the correct parameter here
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
                    'title' => "Add Perfoamance Rating",
                    'student' => $student,
                    'content' => view('result::partials.ratings', compact('student', 'ratings', 'attendance', 'attributes', 'exam_id'))->render(),
                ]);
            }
            // Check if ratings exist in the request
            if (!isset($request->ratings) || !is_array($request->ratings)) {
                Toastr::error('No ratings provided', 'Error');
                return redirect()->back();
            }

            $studentRatings = [];
            foreach ($request->ratings as $rating) {
                $map = $this->mapRating((int)$rating['rate']); // Use array access here
                $studentRatings[] = [
                    'rate' => $rating['rate'],
                    'attribute' => $rating['attribute'],
                    'color' => $map['color'],
                    'remark' => $map['remark'],
                    'exam_type_id' => $request->exam_type_id,
                    'student_id' => $request->student_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            // dd($studentRatings);
            // Use batch insert for better performance
            StudentRating::insert($studentRatings);

            Toastr::success('Rating added successfully', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
            Log::error('Error in rating method: ' . $e->getMessage()); // Log the error for debugging
            if ($request->ajax()) {
                return response()->json([
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]);
            }
            Toastr::error('Operation failed: ' . $e->getMessage(), 'Failed');
            return redirect()->back()->with(['studentExam' => 'active']);
        }
    }

    public function preview(Request $request, $id, $exam_id)
    {
        try {
            $result_data = $this->getResultData($id, $exam_id);
            if (!$result_data) {
                return response()->json([
                    'error' => 1,
                    'message' => 'Failed to retrieve student records.',
                ]);
            }

            $filepath = $this->generatePDF($result_data, $id, $exam_id);
            $exam_type = SmExamType::findOrFail($exam_id);
            $params = ['id' => $id, 'exam_id' => $exam_type->id];
            $student = $result_data->student;
            $student->filepath = $filepath;

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
        try {
            $directory = 'uploads/student/timeline';
            $filename = md5($id . ($exam_id ?? $request->exam_id));
            $filepath = "$directory/$filename.pdf";

            if (Storage::exists($filepath)) {
                return Storage::response($filepath);
            }

            if ($request->has('local_stu_id')) {
                if (!$this->token) {
                    $this->login();
                }

                $result_data = $this->fetchStudentRecords($request->local_stu_id, $request->exam_id);
                if (!$result_data) {
                    throw new \Exception('Failed to retrieve student records.');
                }

                $filepath = $this->generatePDF($result_data, $request->local_stu_id, $request->exam_id);
                return Storage::response($filepath);
            }

            $timeline = SmStudentTimeline::where('staff_student_id', $id)
                ->where('type', "exam-$exam_id")
                ->where('academic_id', getAcademicId())
                ->first();

            if (!$timeline) {
                throw new \Exception('Timeline not found.');
            }

            $result_data = $this->getResultData($id, $exam_id);
            if (!$result_data) {
                throw new \Exception('Failed to retrieve student records.');
            }
            $filepath = $this->generatePDF($result_data, $id, $exam_id);
            $timeline->update(['file' => $filepath]);

            return Storage::response($filepath);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Failed');
            return redirect()->back();
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

                $data = [
                    'subject' => 'Result Notification',
                    'reciver_email' => $request->parent_email,
                    'receiver_name' => $request->parent_name,
                    'attachments' => [$request->filepath], // Attachments as an array
                ];

                $student = (object) [
                    'student_id' => $id,
                    'exam_id' => $timeline->type,
                    'term' => $timeline->title,
                    'title' => $timeline->description,
                    'full_name' => $request->full_name,
                    "gender" => $request->gender_id,
                    'admin' => 'Miss. Abigal Ojone',
                    'support' => '+2348096041650'
                ];

                post_mail($student, $data);
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
            // Get the active students and their associated timelines and parents
            $students = SmStudent::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->with(['studentTimeline' => function ($query) {
                    $query->where('file', 'like', 'uploads')
                        ->where('');
                }, 'parents'])
                ->get(['id', 'full_name']);

            // Iterate over the students
            foreach ($students as $stu) {
                // Ensure the student has a timeline and parent data
                $timeline = $stu->studentTimeline;
                $parent = $stu->parents;

                // Skip if no timeline or parent found
                if ($timeline->isEmpty() || !$parent) {
                    continue;
                }

                // Prepare email data
                $data = [
                    'subject' => 'Result Notification',
                    'reciver_email' => $parent->parent_email,
                    'receiver_name' => $parent->parent_name,
                    'attachments' => $timeline->pluck('file', 'id'), // Attachments as an array
                ];

                $body = (object) [
                    'student_id' => $stu->id,
                    'exam_id' => $timeline->type,
                    'term' => $timeline->title,
                    'title' => $timeline->description,
                    'full_name' => $stu->full_name,
                    "gender" => $stu->gender_id,
                    'admin' => 'Miss. Abigal Ojone',
                    'support' => '+2348096041650'
                ];

                // Dispatch the email sending job
                post_mail($body, $data);
            }

            // Success response
            Toastr::success('Emails Queued successfully', 'Success');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        } catch (\Exception $e) {
            // Error logging and failure response
            Log::error('Failed to send result emails: ' . $e->getMessage());
            Toastr::error('Operation Failed: ' . $e->getMessage(), 'Failed');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        }
    }
}
