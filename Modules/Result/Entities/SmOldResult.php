<?php

namespace Modules\Result\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SmOldResult extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $table = 'sm_students';
    protected $connection = 'mysql_edusms';

    /**
     * Relationship to fetch timeline data
     */
    public function parents($db)
    {
        return $db->table('sm_parents')->where('id', $this->parent_id)->first();
    }

    /**
     * Relationship to fetch category data
     */
    public function category($db)
    {
        return $db->table('sm_student_categories')->where('id', $this->student_category_id)->first();
    }

    /**
     * Relationship to fetch academic details
     */
    public function academic($db)
    {
        return $db->table('sm_academic_years')->where('id', $this->academic_id)->first();
    }

    /**
     * Fetch result data for a specific student.
     */
    public static function queryResultData($id, $exam_id)
    {
        return DB::connection('mysql_edusms')->transaction(
            function ($db) use ($id, $exam_id) {
                dd($id, $exam_id);

                $student = SmOldResult::where('sm_students.active_status', 1)
                    ->where('sm_students.id', $id)
                    ->where('student_records.academic_id', 4)
                    ->join('student_records', 'student_records.student_id', '=', 'sm_students.id')
                    ->join('sm_classes', 'sm_classes.id', '=', 'student_records.class_id')
                    ->join('sm_sections', 'sm_sections.id', '=', 'student_records.section_id')
                    ->select(
                        'sm_students.id',
                        'sm_students.full_name as name',
                        'sm_students.student_category_id',
                        'sm_students.gender_id',
                        'sm_students.parent_id',
                        'sm_students.student_photo',
                        'sm_students.admission_no',
                        'sm_students.academic_id',
                        'sm_students.custom_field',
                        'sm_classes.class_name',
                        'sm_sections.section_name',
                        'student_records.class_id',
                        'student_records.section_id',
                    )
                    ->first();

                $student->parents = $student->parents($db);
                $student->category = $student->category($db);

                $result = $db->table('sm_mark_stores')
                    ->where('sm_mark_stores.academic_id', 4)
                    ->join('sm_subjects', 'sm_subjects.id', '=', 'sm_mark_stores.subject_id')
                    ->join('sm_exam_setups', 'sm_exam_setups.id', '=', 'sm_mark_stores.exam_setup_id')
                    ->where('sm_mark_stores.student_id', $id)
                    ->where('sm_mark_stores.class_id', $student->class_id)
                    ->where('sm_mark_stores.section_id', $student->section_id)
                    ->where('sm_mark_stores.exam_term_id', $exam_id)
                    ->where('sm_mark_stores.is_absent', 0)
                    ->select('sm_mark_stores.*', 'sm_subjects.subject_name', 'sm_subjects.subject_code', 'sm_exam_setups.exam_title')
                    ->get()
                    ->groupBy('subject_name');

                $results = $db->table('sm_result_stores')
                    ->where('academic_id', 4)
                    ->where('class_id', $student->class_id)
                    ->where('section_id', $student->section_id)
                    ->where('academic_id', 4)
                    ->where('is_absent', 0)
                    ->where('total_marks', '!=', 0)
                    ->where('exam_type_id', $exam_id)
                    ->select('total_marks', 'student_id')
                    ->get()
                    ->groupBy('student_id');


                $type = $db->table('sm_exam_types')->find($exam_id);
                $academic = $student->academic($db);;

                $cfield = json_decode($student->custom_field, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return null;
                }

                $attd = [];
                $ratings = [];
                $filter = ['days_school_opened', 'days_absent', 'days_present'];
                foreach ($cfield as $key => $value) {
                    if ($key == 'Exam Type') continue;
                    $new_key = strtolower(str_replace([' ', '-'], '_', $key));
                    if (in_array($new_key, $filter)) {
                        $attd[$new_key] = $value;
                        continue;
                    }
                    $map = @mapRating($value);
                    $ratings[] = (object)[
                        'rate' => floor(((int)$value / 5) * 100),
                        'attribute' => $key,
                        'remark' => $map['remark'],
                        'color' => $map['color']
                    ];
                }
                $attendance = (object)[
                    'days_opened' => $attd['days_school_opened'],
                    'days_absent' => $attd['days_absent'],
                    'days_present' => $attd['days_present']
                ];

                return (object) [
                    'student' => $student,
                    'result' => $result,
                    'results' => $results,
                    'type' => $type,
                    'academic' => $academic,
                    'attendance' => $attendance,
                    'ratings' => $ratings,
                    'remark' => '',
                ];
            }
        );
    }

    protected function mapRating($rate = 0)
    {
        $map = [
            '5' => ['remark' => 'Excellent', 'color' => 'range-success'],
            '4' => ['remark' => 'Good', 'color' => 'range-error'],
            '3' => ['remark' => 'Average', 'color' => 'range-info'],
            '2' => ['remark' => 'Below Average', 'color' => 'range-accent'],
            '1' => ['remark' => 'Poor', 'color' => 'range-warning'],
        ];

        if ($rate == 0) return $map['1'];

        return $map[$rate] ?? ['remark' => 'Not Rated', 'color' => 'range-default'];
    }
}
