<?php

namespace Modules\Result\Traits;

use App\SmAcademicYear;
use App\SmDesignation;
use App\SmExamType;
use App\SmMarkStore;
use App\SmParent;
use App\SmResultStore;
use App\SmStaff;
use App\SmStudent;
use App\User;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Modules\Result\Entities\ClassAttendance;
use Modules\Result\Entities\SmOldResult;
use Modules\Result\Entities\StudentRating;
use Modules\Result\Entities\TeacherRemark;

trait ResultTrait
{
    public $maxAttempts = 5; // Default: 5 attempts
    public $decayMinutes = 1; // Default: 1 minute

    public function getClassAverages($student_results)
    {
        if (empty($student_results)) {
            return null;
        }

        $student_averages = [];

        foreach ($student_results as $id => $subject_totals) {
            $student_averages[] = [
                'student_id' => $id,
                'average' => floor($subject_totals->sum('total_marks') / $subject_totals->count()),
            ];
        }

        $averages = collect($student_averages);

        // Find min and max averages
        $min_average_value = $averages->min('average');
        $max_average_value = $averages->max('average');

        // Find the corresponding students
        $min_average = (object) [
            'student_id' => $averages->firstWhere('average', $min_average_value)['student_id'] ?? null,
            'value' => $min_average_value ?? 0,
        ];

        $max_average = (object) [
            'student_id' => $averages->firstWhere('average', $max_average_value)['student_id'] ?? null,
            'value' => $max_average_value ?? 0,
        ];

        return (object) [
            'min_average' => $min_average,
            'max_average' => $max_average,
        ];
    }

    function getObjectives($class_name)
    {
        $jsonContent = Storage::get('uploaded_files/objectives.json');
        $json_data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        return collect($json_data)->where('class_name', $class_name);
    }

    private function generateLinks($timelines)
    {
        return $timelines->map(function ($timeline) {
            $params = ['id' => $timeline->staff_student_id, 'exam_id' => $timeline->type];
            $link = route('result.download', $params);
            return ['label' => $timeline->title, 'url' => $link];
        });
    }

    function getContacts($category)
    {
        $principal_desig = SmDesignation::where('title', 'principal')->first();
        $supports_desig = SmDesignation::where('title', 'LIKE', "$category%")->first();
        $staffMembers = SmStaff::whereIn('designation_id', [$principal_desig->id, $supports_desig->id])
            ->get()->keyBy('designation_id');
        $principal = $staffMembers->get($principal_desig->id);
        $support = $staffMembers->get($supports_desig->id);
        return [
            'principal' => $principal->full_name,
            'contact' => $principal->mobile,
            'support' => $support->mobile,
        ];
    }

    public function getResultData($id, $exam_id, $db = null)
    {
        $result_data = !$db ? $this->queryResultData($id, $exam_id)
            : SmOldResult::queryResultData($id, $exam_id);

        $result = $result_data->result;
        if (!count($result)) return null;

        $student_data = $result_data->student;
        $category = $student_data->category;
        $type = $result_data->type;
        $academic = $result_data->academic;
        $attendance = $result_data->attendance;

        $parent_name = $student_data->parents->fathers_name ?? $student_data->parents->mothers_name;
        $student = (object) [
            'id' => $student_data->id,
            'exam_id' => $type->id,
            'full_name' => $student_data->name,
            'gender' => $student_data->gender_id,
            'parent_email' => $student_data->parents->guardians_email,
            'parent_name' => $parent_name,
            'term' => $type->title,
            'title' => 'TERMLY SUMMARY OF PROGRESS REPORT',
            'type' => $category->category_name,
            'class_name' => $student_data->class_name,
            'section_name' => $student_data->section_name,
            'admin_no' => $student_data->admission_no,
            'session_year' => "$academic->year-[$academic->title]",
            'opened' => $attendance->days_opened ?? 0,
            'absent' => $attendance->days_opened ?? 0,
            'present' => $attendance->days_opened ?? 0,
            'student_photo' => $student_data->student_photo,
            'filepath' => ''
        ];

        $objectives = $this->getObjectives($student->class_name);
        $school_data = generalSetting();
        $address = $this->parseAddress($school_data->address);
        $school = (object) [
            'name' => $school_data->site_title,
            'logo' => $school_data->favicon,
            'city' => $address->city,
            'state' => $address->state,
            'title' => $type->title,
            'vacation_date' => 'December 25, 2024',
        ];

        $rows = [];
        $over_all = 0;
        foreach ($result as $subject_name => $marks_data) {
            $sum = $marks_data->sum('total_marks');
            $marks = $marks_data->pluck('total_marks', 'exam_title')->toArray();
            $grade = $this->getGrade($sum, $student->type);
            $obj = $objectives->firstWhere('subject_code', $marks_data[0]->subject_code);
            $rows[] = (object)[
                'subject' => $subject_name,
                'objectives' => array_map('trim', explode('|', $obj['text'] ?? '')),
                'marks' => $marks,
                'total_score' => $sum,
                'grade' => $grade->grade,
                'color' => $grade->color,
            ];
            $over_all += $sum;
            if ($subject_name == "BIBLE" && $db)
                $result_data->remark = (object)[
                    'name' => "Teacher's Remarks",
                    'remark' => $marks_data[0]->teacher_remarks
                ];
        }

        $class_average = $this->getClassAverages($result_data->results);
        $score = (object) [
            'total' => $over_all,
            'average' => $rows ? floor($over_all / count($rows)) : 0,
            'min_average' => $class_average->min_average,
            'max_average' => $class_average->max_average,
            'max_scores' => count($rows) * 100,
        ];

        $data =  (object) [
            'school' => $school,
            'student' => $student,
            'records' => $rows,
            'score' => $score,
            'ratings' => $result_data->ratings,
            'remark' => $result_data->remark,
            'exam_type' => $type,
        ];

        return $data;
    }

    public static function queryResultData($id, $exam_type_id)
    {
        $academic_id = getAcademicId();
        $student = SmStudent::where('sm_students.active_status', 1)
            ->where('sm_students.id', $id)
            ->where('student_records.academic_id', $academic_id)
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

        $result = SmMarkStore::where('sm_mark_stores.academic_id', $academic_id)
            ->join('sm_subjects', 'sm_subjects.id', '=', 'sm_mark_stores.subject_id')
            ->join('sm_exam_setups', 'sm_exam_setups.id', '=', 'sm_mark_stores.exam_setup_id')
            ->where('sm_mark_stores.student_id', $id)
            ->where('sm_mark_stores.class_id', $student->class_id)
            ->where('sm_mark_stores.section_id', $student->section_id)
            ->where('sm_mark_stores.exam_term_id', $exam_type_id)
            ->where('sm_mark_stores.is_absent', 0)
            ->select('sm_mark_stores.*', 'sm_subjects.subject_name', 'sm_subjects.subject_code', 'sm_exam_setups.exam_title')
            ->get()
            ->groupBy('subject_name');

        $results = SmResultStore::where('academic_id', $academic_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('academic_id', $academic_id)
            ->where('is_absent', 0)
            ->where('total_marks', '!=', 0)
            ->where('exam_type_id', $exam_type_id)
            ->select('total_marks', 'student_id')
            ->get()
            ->groupBy('student_id');

        $type = SmExamType::find($exam_type_id);
        $academic = SmAcademicYear::find($academic_id);
        $attendance = ClassAttendance::where('student_id', $id)
            ->where('exam_type_id', $exam_type_id)
            ->first();

        $ratings = StudentRating::where('student_id', $id)
            ->where('exam_type_id', $type->id)
            ->get();

        $remark = TeacherRemark::where('student_id', $student->id)
            ->where('exam_type_id', $type->id)
            ->first();

        return (object) [
            'student' => $student,
            'result' => $result,
            'results' => $results,
            'type' => $type,
            'academic' => $academic,
            'attendance' => $attendance,
            'ratings' => $ratings,
            'remark' => $remark,
        ];
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

    private function createOrUpdateUser($parent)
    {
        $phone_number = $parent->guardians_mobile ?? $parent->fathers_mobile ?? $parent->mothers_mobile;
        $full_name = $parent->guardians_name ?? $parent->fathers_name ?? $parent->mothers_name;

        // Check for existing users with the same email
        $existingUsers = User::where('email', $parent->guardians_email)->get();

        if ($existingUsers->count() > 1) {
            // Resolve duplicates by keeping the most recent and removing others
            $primaryUser = $existingUsers->sortByDesc('id')->first();
            User::where('email', $parent->guardians_email)
                ->where('id', '!=', $primaryUser->id)
                ->delete();
            $user = $primaryUser;
        } else {
            // Use the existing user if available
            $user = $existingUsers->first();
        }

        // Create a new user if none exists
        if (!$user) {
            $user = new User();
            $user->role_id = 3;
            $user->password = Hash::make(config('app.default_password', '123456'));
        }

        // Update or assign user details
        $user->full_name = $full_name;
        $user->username = $phone_number ?? $parent->guardians_email;
        $user->phone_number = $phone_number;
        $user->email = $parent->guardians_email;
        $user->save();

        // Link the user to the parent if not already linked
        if (!$parent->user_id || $parent->user_id !== $user->id) {
            $parent->user_id = $user->id;
            $parent->save();
        }
    }

    public function updateRelation($student_id, $parent_id, $email)
    {
        $stu = SmStudent::findOrFail($student_id);
        if ($stu->getOriginal('parent_id') !== (int)$parent_id) {
            $stu->parent_id = (int)$parent_id;
            $stu->save();
        }

        $parent = SmParent::findOrFail($parent_id);
        if ($email && $parent->getOriginal('guardians_email') !== $email) {
            dd($email, $parent->guardians_email);
            $parent->guardians_email = $email;
            $parent->save();
        }

        $this->createOrUpdateUser($parent);
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

    function isParent($childName, $parentName)
    {
        $childParts = explode(' ', strtoupper($childName));
        $parentParts = explode(' ', strtoupper($parentName));

        if (count($childParts) < 1 || count($parentParts) < 1) {
            return false;
        }

        foreach ($parentParts as $parentPart) {
            if (in_array($parentPart, $childParts)) {
                return true;
            }
        }
        return false;
    }

    private function optimizeImage(string $student_photo)
    {
        try {
            $filePath = str_starts_with($student_photo, 'public/')
                ? base_path($student_photo)
                : public_path($student_photo);

            if (!file_exists($filePath)) {
                return;
            }

            $fileSizeBytes = filesize($filePath);
            $fileSizeKb = $fileSizeBytes / 1024;

            if ($fileSizeKb <= 70) {
                return;
            }

            $image = Image::make($filePath);
            $image->save($filePath, 15);
        } catch (\Exception $e) {
            Log::error("Failed to optimize $student_photo: " . $e->getMessage());
            return null;
        }
    }
}
