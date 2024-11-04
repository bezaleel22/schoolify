<?php

namespace Modules\Result\Http\Controllers;

use App\ApiBaseMethod;
use Illuminate\Contracts\Support\Renderable;
use App\User;
use App\SmClass;
use App\SmRoute;
use App\SmStaff;
use App\SmStudent;
use App\SmVehicle;
use Carbon\Carbon;
use App\SmExamType;
use Gotenberg\Stream;
use App\SmMarksGrade;
use App\SmAcademicYear;
use App\SmExamSchedule;
use App\SmStudentTimeline;
use App\CustomResultSetting;
use App\SmStudentAttendance;
use App\SmSubjectAttendance;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use App\Scopes\StatusAcademicSchoolScope;
use App\SmGeneralSettings;
use Attribute;
use Gotenberg\Gotenberg;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\University\Entities\UnSemesterLabel;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
use Modules\Result\Entities\ClassAttendance;
use Modules\Result\Entities\Comment;
use Modules\Result\Entities\CommentTag;
use Modules\Result\Entities\StudentRating;
use Modules\Result\Entities\TeacherRemark;
use Modules\Result\Services\ResultService;
use Modules\Result\Traits\ResultTrait;

use function PHPSTORM_META\map;

class ResultController extends Controller
{
    private $resultService;
    protected $token = '';

    use ResultTrait;

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show(Request $request, $id, $type = null)
    {
        try {
            $next_labels = null;
            $student_detail = SmStudent::withoutGlobalScope(StatusAcademicSchoolScope::class)->find($id);
            $records = $student_detail->allRecords;
            $siblings = SmStudent::where('parent_id', '!=', 0)->where('parent_id', $student_detail->parent_id)->where('id', '!=', $id)->status()->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $exams = SmExamSchedule::where('class_id', $student_detail->class_id)
                ->where('section_id', $student_detail->section_id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $academic_year = $student_detail->academicYear;

            $result_setting = CustomResultSetting::where('school_id', auth()->user()->school_id)->where('academic_id', getAcademicId())->get();

            $grades = SmMarksGrade::where('active_status', 1)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->get();

            $max_gpa = $grades->max('gpa');

            $fail_gpa = $grades->min('gpa');

            $fail_gpa_name = $grades->where('gpa', $fail_gpa)
                ->first();

            $timelines = SmStudentTimeline::where('staff_student_id', $id)
                ->where('school_id', Auth::user()->school_id)
                ->get();

            if (!empty($student_detail->vechile_id)) {
                $driver_id = SmVehicle::where('id', '=', $student_detail->vechile_id)->first();
                $driver_info = SmStaff::where('id', '=', $driver_id->driver_id)->first();
            } else {
                $driver_id = '';
                $driver_info = '';
            }

            $exam_terms = SmExamType::where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->get();

            $custom_field_data = $student_detail->custom_field;

            if (!is_null($custom_field_data)) {
                $custom_field_values = json_decode($custom_field_data);
            } else {
                $custom_field_values = null;
            }
            $sessions = SmAcademicYear::get(['id', 'year', 'title']);

            $now = Carbon::now();
            $year = $now->year;
            $month  = $now->month;
            $days = cal_days_in_month(CAL_GREGORIAN, $now->month, $now->year);
            $studentRecord = StudentRecord::where('student_id', $student_detail->id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', $student_detail->school_id)
                ->get();

            $attendance = SmStudentAttendance::where('student_id', $student_detail->id)
                ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                ->whereIn('student_record_id', $studentRecord->pluck('id'))
                ->get();

            $subjectAttendance = SmSubjectAttendance::with('student')
                ->whereIn('academic_id', $studentRecord->pluck('academic_id'))
                ->whereIn('student_record_id', $studentRecord->pluck('id'))
                ->where('school_id', $student_detail->school_id)
                ->get();

            $studentBehaviourRecords = (moduleStatusCheck('BehaviourRecords')) ? AssignIncident::where('student_id', $id)->with('incident', 'user', 'academicYear')->get() : null;
            $behaviourRecordSetting = BehaviourRecordSetting::where('id', 1)->first();

            $re = $this->fetchStudentRecords(339, 5);
            $results = [];
            if ($exam_terms) {
                foreach ($exam_terms as $term) {
                    $results[] = $this->getResultData($id, $term);
                }
            }
            $student_info = $results[0]->student ?? null;
            return view('result::student_view', compact('results', 'student_info', 'timelines', 'student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type', 'result_setting', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

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
            if ($request->ajax()) {
                return response()->json([
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]);
            }
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

            $teachar_id = null;
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
            return redirect()->back();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]);
            }
            dd($e->getMessage());
            Toastr::error('Operation failed', 'Failed');
            return redirect()->back();
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
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Error in rating method: ' . $e->getMessage()); // Log the error for debugging
            if ($request->ajax()) {
                return response()->json([
                    'error' => 1,
                    'message' => $e->getMessage(),
                ]);
            }
            Toastr::error('Operation failed: ' . $e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    public function preview(Request $request, $id, $exam_id)
    {
        try {
            if (!$this->token) {
                $this->login();
            }

            $result_data = $this->fetchStudentRecords(339, 5);
            if (!$result_data) {
                return response()->json([
                    'error' => 1,
                    'message' => 'Failed to retrieve student records.',
                ]);
            }

            $filepath = $this->generatePDF($result_data, $id, $exam_id);
            $exam_type = SmExamType::findOrFail($exam_id);
            $params = ['id' => $id, 'exam_id' => $exam_type->id];

            return response()->json([
                'preview' => true,
                'title' => "Result Preview",
                'pdfUrl' => route('result.download', $params),
                'content' => view('result::partials.preview', compact('filepath', 'id', 'exam_type'))->render(),
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
            $filename = md5($id . $exam_id ?? $request->exam_id);
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

            $result_data = $this->getResultData($id, $exam_id);
            if (!$result_data) {
                throw new \Exception('Failed to retrieve student records.');
            }

            $timeline = SmStudentTimeline::where('staff_student_id', $id)
                ->where('type', "exam-$exam_id")
                ->where('academic_id', getAcademicId())
                ->first();

            if (!$timeline) throw new \Exception('Timeline not found.');

            $filepath = $this->generatePDF($result_data, $id, $exam_id);
            $timeline->file = $filepath;
            $timeline->save();

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
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        } catch (\Exception $e) {
            Log::error('Failed to publish timeline: ' . $e->getMessage());
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        }
    }
}
