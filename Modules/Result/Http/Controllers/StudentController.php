<?php

namespace Modules\Result\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use App\SmStaff;
use App\SmStudent;
use App\SmVehicle;
use Carbon\Carbon;
use App\SmExamType;
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
use App\SmParent;
use Illuminate\Support\Facades\Cache;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\BehaviourRecords\Entities\BehaviourRecordSetting;
use Modules\Result\Traits\ResultTrait;

class StudentController extends Controller
{
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
            $parents = SmParent::all();
            $results = [];
            foreach ($exam_terms as $term) {
                $cacheKey = "result_{$id}_{$term->id}";
                Cache::forget($cacheKey);
                $results[] = Cache::remember($cacheKey, now()->addDay(), function () use ($id, $term) {
                    return $this->getResultData($id, $term->id);
                });
            }

            $student_info = $results[0]->student ?? null;
            return view('result::student_view', compact('parents', 'results', 'student_info', 'timelines', 'student_detail', 'driver_info', 'exams', 'siblings', 'grades', 'academic_year', 'exam_terms', 'max_gpa', 'fail_gpa_name', 'custom_field_values', 'sessions', 'records', 'next_labels', 'type', 'result_setting', 'attendance', 'subjectAttendance', 'days', 'year', 'month', 'studentBehaviourRecords', 'behaviourRecordSetting'));
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }
}
