<?php

namespace Modules\Result\Repositories;

use App\Models\StudentRecord;
use App\Models\User;
use App\SmExamType;
use App\SmMarkStore;
use App\SmStudent;
use Illuminate\Support\Facades\Auth;

class ResultRepository
{
    static function getResultData($id, $exam_id)
    {
        $student = SmStudent::where('sm_students.active_status', 1)->where('sm_students.id', $id)
            ->join('student_records', 'student_records.student_id', '=', 'sm_students.id')
            ->join('sm_classes', 'sm_classes.id', '=', 'student_records.class_id')
            ->join('sm_sections', 'sm_sections.id', '=', 'student_records.section_id')
            ->select('sm_students.id', 'sm_students.full_name', 'sm_students.student_photo', 'sm_students.admission_no', 'sm_students.custom_field', 'sm_students.parent_id', 'sm_students.student_category_id', 'student_records.class_id', 'student_records.section_id', 'student_records.school_id', 'sm_classes.class_name', 'sm_sections.section_name', 'student_records.academic_id', 'student_records.id As record_id')
            ->with('school', 'academic', 'timeline', 'category')
            ->where('student_records.academic_id', getAcademicId())
            ->with(array('parents' => function ($query) {
                $query->select('id', 'fathers_name', 'mothers_name', 'guardians_email', 'guardians_mobile');
            }))->first();

        $custom_field_data = $student->custom_field;
        if (is_null($custom_field_data)) {
            return null;
        }

        $fields = json_decode($custom_field_data, true);
        $custom_field = [];
        foreach ($fields as $key => $value) {
            $new_key = strtolower(str_replace([' ', '-'], '_', $key));
            $custom_field[$new_key] = $value;
        }
        $student['custom_field'] = $custom_field;

        $result = SmMarkStore::where('sm_mark_stores.section_id', $student->section_id)
            ->join('sm_subjects', 'sm_subjects.id', '=', 'sm_mark_stores.subject_id')
            ->where('sm_mark_stores.class_id', $student->class_id)
            ->where('sm_mark_stores.student_id', $student->id)
            ->where('sm_mark_stores.academic_id', getAcademicId())
            ->where('sm_mark_stores.is_absent', 0)
            ->where('sm_mark_stores.total_marks', '!=', 0)
            ->where('sm_mark_stores.exam_term_id', $exam_id)
            ->select('sm_mark_stores.*', 'sm_subjects.subject_name')
            ->get()
            ->groupBy('subject_id');
        $student['result'] = $result;

        $records = StudentRecord::where('student_records.academic_id', getAcademicId())
            ->join('sm_students', 'sm_students.id', '=', 'student_records.student_id')
            ->where('student_records.is_promote', 0)
            ->where('student_records.class_id', $student->class_id)
            ->where('student_records.section_id', $student->section_id)
            ->where('sm_students.active_status', 1)
            ->select('student_records.class_id', 'student_records.section_id', 'student_records.student_id')
            ->with(['results' => function ($query) use ($student, $exam_id) {
                $query->where('section_id', $student->section_id)
                    ->where('class_id', $student->class_id)
                    ->where('academic_id', getAcademicId())
                    ->where('is_absent', 0)
                    ->where('total_marks', '!=', 0)
                    ->where('exam_type_id', $exam_id);
            }])
            ->get();

        return (object) ['student_data' => $student, 'records' => $records];
    }
}
