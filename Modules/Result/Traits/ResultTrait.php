<?php

namespace Modules\Result\Traits;

use App\Models\StudentRecord;
use App\SmAcademicYear;
use App\SmExamType;
use App\SmMarkStore;
use App\SmResultStore;
use Gotenberg\Stream;
use App\SmStudent;
use Brian2694\Toastr\Facades\Toastr;
use Gotenberg\Gotenberg;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Modules\Result\Entities\ClassAttendance;
use Modules\Result\Entities\StudentRating;
use Modules\Result\Entities\TeacherRemark;

trait ResultTrait
{
    public $maxAttempts = 5; // Default: 5 attempts
    public $decayMinutes = 1; // Default: 1 minute

    protected function login()
    {
        $url = env('LOCAL_BASE_URL', null);
        $url = $url . '/api/auth/login';
        $credentials = [
            "email" => "onosbrown.saved@gmail.com",
            "password" => "#1414bruno#"
        ];

        $response = Http::post($url, $credentials);
        if ($response->successful()) {
            $this->token = $response->json()['data']['token'];
        } else {
            return response()->json(['error' => 'Login failed'], 401);
        }
    }

    protected function fetchStudentRecords($id, $exam_id)
    {
        if (!$this->token)
            $this->login();

        $url = env('LOCAL_BASE_URL', null);
        $url = "$url/api/marks-grade?id=$id&exam_id=$exam_id";
        $response = Http::withHeaders([
            'Authorization' => $this->token, // Replace $token with your actual token
        ])->get($url);

        if ($response->successful()) {
            return $response->object()->data;
        } else {
            return null;
        }
    }

    public function getClassAverages($student_results)
    {
        if (!$student_results)
            return null;

        foreach ($student_results as $id => $subject_totals) {
            $total_marks = $subject_totals->sum('total_marks');
            $student_results['avarage'] = floor($total_marks / $subject_totals->count());
        }

        $ids = $student_results->pluck('student_id', 'avarage');
        $min = $student_results->min('avarage');
        $min_average = (object)[
            'student_id' => $ids[$min] ?? null,
            'value' => $min,
        ];

        $max = $student_results->max('avarage');
        $max_average = (object)[
            'student_id' => $ids[$max] ?? null,
            'value' => $max,
        ];

        return (object) [
            'min_average' => $min_average,
            'min_average' => $min_average,
        ];
    }

    public function getResultData($id, $type)
    {
        $result_data = $this->queryResultData($id, $type->id);

        $result = $result_data->result;
        $student_results = $result_data->results;
        if (!count($result) && !count($student_results))
            return null;

        $student_data = $result_data->student;
        $category = $student_data->category;

        $attendance = ClassAttendance::where('student_id', $id)
            ->where('exam_type_id', $type->id)
            ->first();

        $academic = SmAcademicYear::find(getAcademicId());
        $parent_name = $student_data->parents->fathers_name ?? $student_data->parents->mothers_name;
        $student = (object) [
            'id' => $student_data->id,
            'exam_id' => $type->id,
            'full_name' => $student_data->name,
            'gender' => $student_data->gender_id,
            'admin' => '',
            'support' => '',
            'parent_email' => $student_data->parents->guardians_email,
            'parent_name' => $parent_name,
            'term' => $type->title,
            'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
            'type' => $category->category_name,
            'class_name' => $student_data->class_name,
            'section_name' => $student_data->section_name,
            'admin_no' => $student_data->admission_no,
            'session_year' => $academic->title,
            'opened' => $attendance->days_opened ?? 0,
            'absent' => $attendance->days_opened ?? 0,
            'present' => $attendance->days_opened ?? 0,
            'student_photo' => $student_data->student_photo,
            'filepath' => ''
        ];

        $school_data = schoolConfig();
        $address = $this->parseAddress($school_data->address);
        $school = (object) [
            'name' => $school_data->school_name,
            'city' => $address->city,
            'state' => $address->state,
            'title' => $type->title,
            'vacation_date' => 'December 25, 2024',
        ];

        $rows = [];
        $over_all = 0;
        foreach ($result as $subject_name => $marks_data) {
            $sum = $marks_data->sum('total_marks');
            $marks = $marks_data->pluck('total_marks')->toArray();
            $grade = $this->getGrade($sum, $student->type);
            $rows[] = [
                'subject' => $subject_name,
                'marks' => $marks,
                'total_score' => $sum,
                'grade' => $grade->grade,
                'color' => $grade->color
            ];
            dd($rows);

            $over_all += $sum;
        }
        $class_average = $this->getClassAverages($student_results);
        $score = (object) [
            'total' => $over_all,
            'average' => $rows ? floor($over_all / count($rows)) : 0,
            'min_average' => $class_average->min_average ?? 0,
            'max_average' => $class_average->max_average ?? 0,
            'max_scores' => count($rows) * 100,
        ];
        $ratings = StudentRating::where('student_id', $id)
            ->where('exam_type_id', $type->id)
            ->first();

        $remark = TeacherRemark::where('student_id', $student->id)
            ->where('exam_type_id', $type->id)
            ->first();;

        $data =  (object) [
            'school' => $school,
            'student' => $student,
            'records' => $rows,
            'score' => $score,
            'ratings' => $ratings,
            'remark' => $remark,
            'exam_type' => $type,
        ];

        return $data;
    }

    public static function queryResultData($id, $exam_type_id)
    {
        $student = SmStudent::where('sm_students.active_status', 1)
            ->where('sm_students.id', $id)
            ->where('student_records.academic_id', getAcademicId())
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
                'sm_classes.class_name',
                'sm_sections.section_name',
                'student_records.class_id',
                'student_records.section_id',
            )
            ->with([
                'category',
                'parents' => function ($query) {
                    $query->select('id', 'fathers_name', 'mothers_name', 'guardians_email', 'guardians_mobile');
                }
            ])
            ->first();

        $result = SmMarkStore::where('sm_mark_stores.academic_id', getAcademicId())
            ->join('sm_subjects', 'sm_subjects.id', '=', 'sm_mark_stores.subject_id')
            ->where('sm_mark_stores.student_id', $id)
            ->where('sm_mark_stores.class_id', $student->class_id)
            ->where('sm_mark_stores.section_id', $student->section_id)
            ->where('sm_mark_stores.exam_term_id', $exam_type_id)
            ->where('sm_mark_stores.is_absent', 0)
            ->where('sm_mark_stores.total_marks', '!=', 0)
            ->select('sm_mark_stores.*', 'sm_subjects.subject_name')
            ->get()
            ->groupBy('subject_name');

        $results = SmResultStore::where('academic_id', getAcademicId())
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('academic_id', getAcademicId())
            ->where('is_absent', 0)
            ->where('total_marks', '!=', 0)
            ->where('exam_type_id', $exam_type_id)
            ->select('total_marks', 'student_id')
            ->get()
            ->groupBy('student_id');

        return (object) ['student' => $student, 'result' => $result, 'results' => $results];
    }

    private function removeDate($string)
    {
        $pattern = '/\s*-\s*[A-Za-z]{3}\/\d{4}/';
        return preg_replace($pattern, '', $string);
    }

    public function getGrade($score, $arm)
    {
        $eyfs = [
            ['min' => 0, 'max' => 80, 'grade' => 'EMERGING', 'color' => 'bg-purple-200'],
            ['min' => 81, 'max' => 90, 'grade' => 'EXPECTED', 'color' => 'bg-blue-200'],
            ['min' => 91, 'max' => 100, 'grade' => 'EXCEEDING', 'color' => 'bg-red-200'],
        ];

        $graders = [
            ['min' => 0, 'max' => 69, 'grade' => 'E', 'color' => 'bg-red-200'],
            ['min' => 70, 'max' => 76, 'grade' => 'D', 'color' => 'bg-orange-200'],
            ['min' => 77, 'max' => 85, 'grade' => 'C', 'color' => 'bg-yellow-200'],
            ['min' => 86, 'max' => 93, 'grade' => 'B', 'color' => 'bg-blue-200'],
            ['min' => 94, 'max' => 100, 'grade' => 'A', 'color' => 'bg-purple-200'],
        ];

        $grades = $arm === "GRADERS" ? $graders : $eyfs;
        foreach ($grades as $range) {
            if ($score >= $range['min'] && $score <= $range['max']) {
                return (object) ['grade' => $range['grade'], 'color' => $range['color']];
            }
        }

        return ["Outstanding", "bg-red-200"];
    }

    private function parseAddress($address)
    {
        $addressComponents = [
            'street_number' => null,
            'street_name' => null,
            'city' => null,
            'state' => null,
        ];

        $parts = array_map('trim', explode(',', $address));
        $addressComponents['state'] = array_pop($parts);
        $addressComponents['city'] = array_pop($parts);

        $streetAddress = implode(', ', $parts);

        $regex = '/No\.\s*(\d+)\s*(.+)/i';
        $matches = [];

        if (preg_match($regex, $streetAddress, $matches)) {
            $addressComponents['street_number'] = $matches[1]; // First capture group (street number)
            $addressComponents['street_name'] = $matches[2];   // Second capture group (street name)
        } else {
            $addressComponents['street_name'] = $streetAddress;
        }

        return (object) $addressComponents;
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

        if ($rate == 0) return $map;

        return $map[$rate] ?? ['remark' => 'Not Rated', 'color' => 'range-default'];
    }

    public function getView($result)
    {
        $school = $result->school;
        $student = $result->student;
        $records = $result->records;
        $score = $result->score;
        $ratings = $result->ratings;
        $remark = $result->remark;

        return  view('result::template.result', compact('student', 'school', 'ratings', 'records', 'score', 'remark'));
    }

    public function generatePDF($result_data, $id, $exam_id)
    {

        $view = $this->getView($result_data);
        $result = $view->render();
        $fileName = md5($id . $exam_id);

        $url = env('GOTENBERG_URL');
        if (!$url) {
            return response()->json([
                'error' => 1,
                'message' => 'PDF generation service URL is not configured.',
            ]);
        }

        $directory = 'uploads/student/timeline';
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
        $storage_path = Storage::path($directory);

        $req = Gotenberg::chromium($url)
            ->pdf()
            ->skipNetworkIdleEvent()
            ->preferCssPageSize()
            ->outputFilename($fileName)
            ->margins('2mm', '2mm', '2mm', '2mm')
            ->html(Stream::string('index.html', $result));

        $filename = Gotenberg::save($req, $storage_path);
        return $directory . '/' . $filename;
    }

    public function transformComment($comment, $student)
    {
        $first_name = explode(' ', $student->full_name)[0];

        $comment = str_replace('STUDENT_NAME', $first_name, $comment);
        $comment = str_replace('CLASS_NAME', $student->class_name, $comment);
        $replacements = [
            'Male' => [
                'His/Him' => 'His',
                'his/him' => 'his',
                'his/hers' => 'his',
                'His/Hers' => 'His',
                'him/her' => 'him',
                'Him/Her' => 'Him',
                'she' => 'he',
                'her' => 'him',
                'hers' => 'his',
                'herself' => 'himself',
                'girl' => 'boy',
                'She' => 'He',
                'Her' => 'Him',
                'Hers' => 'His',
                'Herself' => 'Himself',
                'Girl' => 'Boy',
            ],
            'Female' => [
                'His/Him' => 'Her',
                'his/him' => 'her',
                'his/hers' => 'hers',
                'him/her' => 'her',
                'Him/Her' => 'Her',
                'His/Hers' => 'Hers',
                'he' => 'she',
                'his' => 'her',
                'him' => 'her',
                'hers' => 'hers',
                'himself' => 'herself',
                'boy' => 'girl',
                'He' => 'She',
                'His' => 'Her',
                'Him' => 'Her',
                'His' => 'Hers',
                'Himself' => 'Herself',
                'Boy' => 'Girl',
            ],
        ];

        $gender = $student->gender;
        if (isset($replacements[$gender])) {
            foreach ($replacements[$student->gender] as $pattern => $replacement) {
                $comment = preg_replace('/\b' . preg_quote($pattern, '/') . '\b/i', $replacement, $comment);
            }
        }

        return $comment;
    }
}
