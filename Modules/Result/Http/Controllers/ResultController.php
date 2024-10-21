<?php

namespace Modules\Result\Http\Controllers;

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
use Gotenberg\Gotenberg;
use Illuminate\Support\Facades\Http;
use Modules\University\Entities\UnSemesterLabel;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
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
                ->where('type', 'stu')->where('academic_id', getAcademicId())
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

            $tags = CommentTag::all();
            $results = [];
            if ($exam_terms) {
                foreach ($exam_terms as $term) {
                    $results[] = $this->getResultData($id, $term->id);
                }
            }

            return view('result::student_view', compact('tags', 'results', 'timelines', 'student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type', 'result_setting', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting'));
        } catch (\Exception $e) {
            dd($e->getMessage());
            Toastr::error($e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    public function remark(Request $request)
    {
        $request->validate([
            'remark' => 'required',
            'exam_type_id' => 'required',
            'student_id' => 'required',
        ]);

        try {
            $teachar_id = null;
            $user_id = Auth::user()->id;
            $teacher = SmStaff::where('user_id', $user_id)->first();
            $remark = new TeacherRemark();
            if ($teacher->id == 3) {
                $remark->teachar_id = $teacher->id;
            }

            $remark->remark = $request->remark;
            $remark->exam_type_id = $request->exam_type_id;
            $remark->student_id = $request->student_id;
            $remark->save();

            Toastr::success('Remark added successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation failed', 'Failed');
            return redirect()->back();
        }
    }

    public function rating(Request $request)
    {
        $request->validate([
            'ratings' => 'required',
            'exam_type_id' => 'required',
            'student_id' => 'required',
        ]);

        try {
            foreach ($request->ratings as $rating) {
                $map = $this->mapRating((int)$rating->rate);

                $stu_rating = new StudentRating();
                $stu_rating->rate = $rating->rate;
                $stu_rating->attribute = $rating->attribute;
                $stu_rating->color = $map['color'];
                $stu_rating->remark = $map['remark'];
                $stu_rating->exam_type_id = $request->exam_type_id;
                $stu_rating->student_id = $request->student_id;
                $rating->save();
            }

            Toastr::success('Rating added successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation failed', 'Failed');
            return redirect()->back();
        }
    }

    public function publish(Request $request, $id, $type_id)
    {
        dd($request->all());
        try {

            $result_data = $this->getResultData($id, $type_id);
            $view = $this->getView($result_data);

            $result = $view->render();
            $student = $result_data->student;
            $fileName = md5($student->full_name . time());

            $url = env('GOTENBERG_URL', null);
            $req = Gotenberg::chromium($url)
                ->pdf()
                ->skipNetworkIdleEvent()
                ->preferCssPageSize()
                ->outputFilename($fileName)
                ->margins('2mm', '2mm', '2mm', '2mm')
                ->html(Stream::string('index.html', $result));
            Gotenberg::save($req, 'public/uploads/student/timeline/');


            if ($request->title != "") {
                $document_photo = "";
                if ($request->file('document_file_4') != "") {
                    $maxFileSize = SmGeneralSettings::first('file_size')->file_size;
                    $file = $request->file('document_file_4');
                    $fileSize =  filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size ' . $maxFileSize . ' Mb is set in system', 'Failed');
                        return redirect()->back();
                    }
                    $file = $request->file('document_file_4');
                    $document_photo = 'stu-' . md5($file->getClientOriginalName() . time()) . "." . $file->getClientOriginalExtension();
                    $file->move('public/uploads/student/timeline/', $document_photo);
                    $document_photo =  'public/uploads/student/timeline/' . $document_photo;
                }

                $timeline = new SmStudentTimeline();
                $timeline->staff_student_id = $request->student_id;
                $timeline->type = 'stu';
                $timeline->title = $request->title;
                $timeline->date = date('Y-m-d', strtotime($request->date));
                $timeline->description = $request->description;
                if (isset($request->visible_to_student)) {
                    $timeline->visible_to_student = $request->visible_to_student;
                }
                $timeline->file = $document_photo;
                $timeline->school_id = Auth::user()->school_id;
                $timeline->academic_id = getAcademicId();
                $timeline->save();
            }

            Toastr::success('Operation successful', 'Success');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back()->with(['studentTimeline' => 'active']);
        }
    }

    public function filter(Request $request)
    {
        $query = Comment::with('tags');

        // Filter by type if provided
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by tags if provided
        if ($request->filled('tags')) {
            $tags = $request->input('tags');
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tag_id', $tags);
            });
        }

        $comments = $query->get();

        return view('result::comments.index', compact('comments'));
    }
}
