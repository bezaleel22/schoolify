<?php

namespace Modules\Result\Console;

use App\User;
use App\SmClass;
use App\SmParent;
use App\SmStudent;
use App\SmSection;
use App\YearCheck;
use Gotenberg\Stream;
use Gotenberg\Gotenberg;
use App\SmLeaveDefine;
use App\SmAcademicYear;
use App\SmStudentTimeline;
use App\SmStudentCategory;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;


class SeedStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:students';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get student data from JSON file and save to database';

    /**
     * Path to the JSON file where students will be saved.
     * 
     * @var string
     */
    protected $jsonFilePath = 'students.json';

    /**
     * Path to the file where the last processed ID will be saved.
     *
     * @var string
     */

    protected $failed_students_json = 'unprocessd_students.json';
    protected $failed_students = [];
    protected $totalStudents = 0;
    protected Collection $examt_types;
    protected  $token = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->shutdown());
        pcntl_signal(SIGTERM, $this->shutdown());

        $this->login();
        if (!Storage::exists($this->jsonFilePath)) {
            $this->error('JSON file does not exist.');
            return;
        }

        $jsonContent = Storage::get($this->jsonFilePath);
        $json_data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to decode JSON.');
            return;
        }

        $this->examt_types = collect($json_data['exam_types']);
        $this->totalStudents = count($json_data['students']);

        // $this->saveStudent($json_data['students'][545], 545);
        // return;

        $progressBar = $this->output->createProgressBar($this->totalStudents);
        $progressBar->start();

        foreach ($json_data['students'] as $idx => $studentData) {
            $this->saveStudent($studentData, $idx);
            $progressBar->advance();
        }

        $progressBar->finish();
        $unprocessed = json_encode($this->failed_students, JSON_PRETTY_PRINT);
        Storage::put($this->failed_students_json, $unprocessed);
        $this->info("\nImport completed.");
    }

    /**
     * Save a single student to the database.
     *
     * @param Collection $studentCollection
     */
    protected function saveStudent($student_data, $idx)
    {
        $studentData = (object) $student_data;
        $exist_student = SmStudent::where('admission_no', $studentData->id)->first();
        if ($exist_student) {
            return;
        }

        $timelines = (object) $studentData->timeline;
        $category = (object) $studentData->category;
        $parentData = (object) $studentData->parents;

        $guardians_phone = $parentData->fathers_mobile ?: $parentData->mothers_mobile;
        $photo_url =  $studentData->student_photo;
        $timeline_uploade_path = 'public/uploads/student/timeline/';

        DB::beginTransaction();
        try {
            $session_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $academic_year = SmAcademicYear::find($session_id);

            $user_stu = new User();
            $user_stu->role_id = 2;
            $user_stu->full_name = $studentData->full_name;
            $user_stu->username = $guardians_phone ?: ($studentData->email ?: $studentData->admission_no);
            $user_stu->email = $studentData->email;
            $user_stu->phone_number = $parentData->guardians_mobile;
            $user_stu->password = Hash::make(123456);
            $user_stu->language = 'en';
            $user_stu->school_id = 1;
            $user_stu->created_at = $academic_year->year . '-01-01 12:00:00';
            $user_stu->save();

            $parent_id = $this->upsertParent($parentData, $academic_year);
            $student = new SmStudent();
            $student->user_id = $user_stu->id;
            $student->parent_id = $parent_id;
            $student->role_id = 2;
            $student->admission_no = $studentData->id;
            $student->first_name = $studentData->first_name;
            $student->last_name = $studentData->last_name;
            $student->full_name = $studentData->full_name;
            $student->gender_id = $studentData->gender_id;
            $student->date_of_birth = date('Y-m-d', strtotime($studentData->date_of_birth));
            $student->mobile = $studentData->mobile;
            $student->admission_date = date('Y-m-d', strtotime($studentData->admission_date));
            $student->student_photo = $this->fileUpload($photo_url, $studentData->student_photo);
            $student->bloodgroup_id = $studentData->bloodgroup_id;
            $student->religion_id = $studentData->religion_id;
            $student->current_address = $studentData->current_address;
            $student->permanent_address = $studentData->permanent_address;
            $student->school_id = 1;
            $student->academic_id = getAcademicId();

            $category_id = $this->upsertStudentCategory($category);
            $student->student_category_id = $category_id;
            $student->student_group_id = $studentData->student_group_id;
            $student->created_at = $academic_year->year . '-01-01 12:00:00';
            $student->save();

            foreach ($timelines as $i => $timelineData) {
                $type = (object) $this->examt_types->firstWhere('title', $timelineData['title']);
                $is_url =  filter_var($timelineData['file'], FILTER_VALIDATE_URL) !== false;
                $url = 'api/download-result?id=' . $student->id . '&lstd_id=' . $studentData->id . '&type_id=' . $type->id;

                $timeline = new SmStudentTimeline();
                $timeline->staff_student_id = $student->id;
                $timeline->type = "local-$type->id";
                $timeline->title = $timelineData['title'];
                $timeline->date = date('Y-m-d', strtotime($timelineData['date']));
                $timeline->description = $timelineData['description'];
                $timeline->visible_to_student = $timelineData['visible_to_student'];
                $timeline->file = $is_url ? $url : $this->fileUpload($timelineData['file'], $timeline_uploade_path);
                $timeline->school_id = 1;
                $timeline->academic_id = getAcademicId();
                $timeline->save();
            }

            $studentData->is_default = 1;
            $studentData->student_id = $student->id;
            $this->insertStudentRecord($studentData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            array_push($this->failed_students, $student_data);
            $this->error('Failed to process student at: ' . $idx . ' with ID: ' . ($studentData->id) . ': ' . $e->getMessage());
            // throw $e;
        }
    }

    public function insertStudentRecord($studentData)
    {
        $ids =  $this->upsertClassSection($studentData->class_name, $studentData->section_name);
        $studentRecord = new StudentRecord;
        $studentRecord->student_id = $studentData->student_id;
        $studentRecord->is_promote = $studentData->is_promote ?? 0;
        $studentRecord->is_default = $studentData->is_default;
        $studentRecord->class_id = $ids->class_id;
        $studentRecord->section_id = $ids->section_id;
        $studentRecord->session_id = getAcademicId();
        $studentRecord->school_id = 1;
        $studentRecord->academic_id = getAcademicId();
        $studentRecord->save();
    }

    protected function upsertClassSection($class_name, $section_name)
    {
        $class = SmClass::where('class_name', $class_name)->first();
        $section = SmSection::where('section_name', $section_name)->first();
        if ($class && $section) {
            return (object) [
                'class_id' => $class->id,
                'section_id' => $section->id,
            ];
        }

        $class = new SmClass();
        $class->class_name = $class_name;
        $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
        $class->school_id = 1;
        $class->academic_id = getAcademicId();
        $class->save();

        $section = new SmSection();
        $section->section_name = $section_name;
        $section->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
        $section->school_id = 1;
        $section->academic_id = getAcademicId();
        $section->save();

        return (object) [
            'class_id' => $class->id,
            'section_id' => $section->id,
        ];
    }

    protected function shutdown()
    {
        return function ($signo) {
            Storage::put($this->failed_students_json, json_encode($this->failed_students, JSON_PRETTY_PRINT));
            $this->info("\nProccessed: ' . $this->totalStudents - count($this->failed_students) . '/' . $this->totalStudents");
            $this->info('Terminated by system. Exiting...');
            exit(0);
        };
    }

    public function login()
    {
        $url = 'http://localhost:9000/api/auth/login';
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

    protected function getExamType($title, $id)
    {
        $exam_title = str_replace('\/', '/', $title);
        // $this->info($id . ' - ' . $exam_title);
        $url = 'http://localhost:9000/api/marks-grade?id=339&title=' . $exam_title; // Replace with your actual endpoint
        $response = Http::withHeaders([
            'Authorization' => $this->token, // Replace $token with your actual token
        ])->get($url);

        if ($response->successful()) {
            $data = $response->object()->data;
            return $data;
        } else {
            return null;
        }
    }

    protected function fileUpload($url, $destination)
    {
        $base_url = 'http://localhost:9000/';
        $fileContent = null;

        try {
            $fileContent = file_get_contents($base_url . $url);
            if ($fileContent === false) {
                throw new \Exception("Failed to download file from URL.");
            }


            $fileInfo = pathinfo($url);
            if (!$fileInfo['basename']) return null;

            $fileName = $fileInfo['filename'];
            $ext = $fileInfo['extension'];

            $tempFilePath = tempnam(sys_get_temp_dir(), 'downloaded_file_');
            file_put_contents($tempFilePath, $fileContent);

            if (!file_exists($destination)) {
                mkdir($destination, 0777, true);
            }

            rename($tempFilePath, $destination . $fileName);
            return $destination . $fileInfo['basename'];
        } catch (\Exception $e) {
            if (strpos($e->getMessage(), '404 Not Found') !== false) {
                return null;
            }
            throw $e;
        }
    }

    protected function upsertStudentCategory($category)
    {
        if ($category && isset($category->category_name)) {
            $student_cat = SmStudentCategory::where('category_name', $category->category_name)->first();
            if ($student_cat) return $student_cat->id;

            $student_cat = new SmStudentCategory();
            $student_cat->category_name = $category->category_name;
            $student_cat->save();
            return $student_cat->id;
        }

        $student_cat = new SmStudentCategory();
        $student_cat->category_name = 'NONE';
        $student_cat->save();
        return $student_cat->id;
    }

    protected function upsertParent($parent_data, $academic_year)
    {
        $guardians_phone = $parent_data->fathers_mobile ?: $parent_data->mothers_mobile;
        $guardians_email = $parent_data->guardians_email;

        $parent = SmParent::where('guardians_email', $guardians_email)->first();
        if ($parent) {
            return $parent->id;
        }

        if ($parent_data->mothers_mobile || $parent_data->guardians_email) {
            $user_parent = new User();
            $user_parent->role_id = 3;
            $user_parent->username = $guardians_phone ?: $guardians_email;
            $user_parent->full_name = $parent_data->fathers_name;
            $user_parent->email = $guardians_email;
            $user_parent->phone_number = $guardians_phone;
            $user_parent->password = Hash::make(123456);
            $user_parent->language = 'en';
            $user_parent->school_id = 1;
            $user_parent->created_at = $academic_year->year . '-01-01 12:00:00';
            $user_parent->save();

            $parent = new SmParent();
            $parent->user_id = $user_parent->id;
            $parent->fathers_name = $parent_data->fathers_name;
            $parent->fathers_mobile = $parent_data->fathers_mobile;
            $parent->mothers_name = $parent_data->mothers_name;
            $parent->mothers_mobile = $parent_data->mothers_mobile;
            $parent->guardians_name = $parent_data->guardians_name;
            $parent->guardians_mobile = $parent_data->guardians_mobile;
            $parent->guardians_email = $parent_data->guardians_email;
            $parent->guardians_relation = $parent_data->guardians_relation;
            $parent->relation = $parent_data->relation;
            $parent->is_guardian = $parent_data->is_guardian;
            $parent->school_id = 1;
            $parent->academic_id = getAcademicId();
            $parent->created_at = $academic_year->year . '-01-01 12:00:00';
            $parent->save();
        }
    }
}
