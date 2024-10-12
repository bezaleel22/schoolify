<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ImportStudents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:students';

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
    protected $jsonFilePath = 'student_data/students.json';

    /**
     * Path to the file where the last processed ID will be saved.
     *
     * @var string
     */

    protected $progressFilePath = 'import_progress';


    protected $token = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->login();
        return

            // Retrieve the last processed index from the progress file
            $lastProcessedIndex = $this->getLastProcessedIndex();
        if (Storage::exists($this->jsonFilePath)) {
            $jsonContent = Storage::get($this->jsonFilePath);
            $studentsArray = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $batchSize = 100;
                $totalStudents = count($studentsArray);
                $globalIndex = 0;
                $chunks = array_chunk($studentsArray, $batchSize);

                $progressBar = $this->output->createProgressBar($totalStudents);
                $progressBar->start();
                $this->info('');

                foreach ($chunks as $chunkIndex => $chunk) {
                    DB::beginTransaction();
                    try {
                        foreach ($chunk as $studentIndex => $studentData) {
                            $globalIndex = ($chunkIndex * $batchSize) + $studentIndex;

                            if ($globalIndex <= $lastProcessedIndex) {
                                continue; // Skip already processed records
                            }

                            $student = (object) $studentData;
                            $this->saveStudent($student);

                            $lastProcessedIndex = $globalIndex;
                            $this->updateProgress($lastProcessedIndex);

                            $progressBar->advance();
                        }

                        DB::commit();
                        $this->warn("Batch " . ($chunkIndex + 1) . " processed successfully.\n");
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $this->error('Failed to process batch ' . ($chunkIndex + 1) . ': ' . $e->getMessage());
                    }
                }
                $progressBar->finish();
                if ($globalIndex >= $totalStudents) {
                    Cache::flush();
                }
                $this->info("\nImport completed.");
            } else {
                $this->error('Failed to decode JSON.');
            }
        } else {
            $this->error('JSON file does not exist.');
        }
    }

    /**
     * Save a single student to the database.
     *
     * @param Collection $studentCollection
     */
    protected function saveStudent($studentData)
    {

        $school = $studentData->shcool;
        $academic = $studentData->academic;
        $timelines = $studentData->timeline;
        $category = $studentData->category;
        $parentData = $studentData->parents;

        $parentInfo = ($parentData->fathers_name || $parentData->fathers_phone || $parentData->mothers_name || $parentData->mothers_phone || $parentData->guardians_email || $parentData->guardians_phone)  ? true : false;
        $guardians_phone = $parentData->guardians_phone;
        $guardians_email = $parentData->guardians_email;

        $photo_url = 'https://llacademy.ng/' . $studentData->student_photo;
        $student_file_destination = 'public/uploads/student/';

        try {
            $session_id = SmAcademicYear::SINGLE_SCHOOL_API_ACADEMIC_YEAR();
            $academic_year = SmAcademicYear::find($session_id);
            $currentLanguage = userLanguage();

            $user_stu = new User();
            $user_stu->role_id = 2;
            $user_stu->full_name = $studentData->full_name;
            $user_stu->username = $guardians_phone ?: ($studentData->email_address ?: $studentData->admission_number);
            $user_stu->email = $studentData->email_address;
            $user_stu->phone_number = $parentData->guardians_mobile;
            $user_stu->password = Hash::make(123456);
            $user_stu->language = $currentLanguage;
            $user_stu->school_id = Auth::user()->school_id;
            $user_stu->created_at = $academic_year->year . '-01-01 12:00:00';
            $user_stu->save();
            $user_stu->toArray();

            $hasParent = null;
            if (!cache($parentData->id)) {
                $userIdParent = null;
                if ($parentData->guardians_phone || $parentData->guardians_email) {
                    $user_parent = new User();
                    $user_parent->role_id = 3;
                    $user_parent->username = $guardians_phone ? $guardians_phone : $guardians_email;
                    $user_parent->full_name = $parentData->fathers_name;
                    if (!empty($guardians_email)) {
                        $user_parent->username = $guardians_phone ? $guardians_phone : $guardians_email;
                    }
                    $user_parent->email = $guardians_email;
                    $user_parent->phone_number = $guardians_phone;
                    $user_parent->password = Hash::make(123456);
                    $user_parent->language = $currentLanguage;
                    $user_parent->school_id = Auth::user()->school_id;
                    $user_parent->created_at = $academic_year->year . '-01-01 12:00:00';
                    $user_parent->save();
                    $user_parent->toArray();
                    $userIdParent = $user_parent->id;
                }

                if ($parentInfo) {
                    $parent = new SmParent();
                    $parent->user_id = $userIdParent;
                    $parent->fathers_name = $parent->fathers_name;
                    $parent->fathers_mobile = $parent->fathers_mobile;
                    $parent->mothers_name = $parent->mothers_name;
                    $parent->mothers_mobile = $parent->mothers_mobile;
                    $parent->guardians_name = $parent->guardians_name;
                    $parent->guardians_mobile = $parent->guardians_mobile;
                    $parent->guardians_email = $parent->guardians_email;
                    $parent->guardians_relation = $parent->guardians_relation;
                    $parent->relation = $parent->relation;
                    $parent->is_guardian = $parent->is_guardian;
                    $parent->school_id = Auth::user()->school_id;
                    $parent->academic_id = getAcademicId();
                    $parent->created_at = $academic_year->year . '-01-01 12:00:00';
                    $parent->save();
                    $parent->toArray();
                    $hasParent = $parent->id;
                    cache([$parentData->id => $parent->id]);
                }
            }

            $parent_id = cache($parentData->id);
            $student = new SmStudent();
            $student->user_id = $user_stu->id;
            $student->parent_id = $hasParent ? $hasParent : cache($parentData->id);
            $student->role_id = 2;
            $student->admission_no = $studentData->id;
            $student->first_name = $studentData->first_name;
            $student->last_name = $studentData->last_name;
            $student->full_name = $studentData->full_name;
            $student->gender_id = $studentData->gender_id;
            $student->date_of_birth = date('Y-m-d', strtotime($studentData->date_of_birth));
            $student->mobile = $studentData->mobile;
            $student->admission_date = date('Y-m-d', strtotime($studentData->admission_date));
            $student->student_photo = $this->fileUpload($photo_url, $student_file_destination);
            $student->bloodgroup_id = $studentData->bloodgroup_id;
            $student->religion_id = $studentData->religion_id;
            $student->current_address = $studentData->current_address;
            $student->permanent_address = $studentData->permanent_address;
            $student->school_id = Auth::user()->school_id;
            $student->academic_id = getAcademicId();

            $student_category_id = cache($category->id) ?: null;
            if (!$student_category_id) {
                $student_type = new SmStudentCategory();
                $student_type->category_name = $category->category_name;
                $student_type->save();
                $student_category_id = $student_type->id;
                cache([$category->id => $student_type->id]);
            }
            $student->student_category_id = $student_category_id;
            $student->student_group_id = $studentData->student_group_id;
            $student->created_at = $academic_year->year . '-01-01 12:00:00';

            if ($studentData->customF) {
                $student->custom_field_form_name = "student_registration";
                $student->custom_field = $studentData->custom_field;
            }
            $student->save();

            foreach ($timelines as $i => $timelineData) {
                $timeline = new SmStudentTimeline();
                $timeline->staff_student_id = $student->id;
                $timeline->type = 'local-';
                $timeline->title = $timelineData->title;
                $timeline->date = date('Y-m-d', strtotime($timelineData->date));
                $timeline->description = $timelineData->description;
                if (isset($request->visible_to_student)) {
                    $timeline->visible_to_student = $timelineData->visible_to_student;
                }

                $timeline->file = $timelineData->file;
                $timeline->school_id = Auth::user()->school_id;
                $timeline->academic_id = getAcademicId();
                $timeline->save();
            }

            // instert into student define leave
            $st_role_id = 2;
            $school_id = Auth::user()->school_id;
            $academic_id = getAcademicId();
            $user_id = $user_stu->id;

            $existingLeaveDefines = SmLeaveDefine::where('role_id', $st_role_id)
                ->where('school_id', $school_id)
                ->where('academic_id', $academic_id)
                ->get();

            $existingTypes = [];

            foreach ($existingLeaveDefines as $leaveDefine) {
                if (!isset($existingTypes[$leaveDefine->type_id])) {
                    $leaveDefineInstance = new SmLeaveDefine();
                    $leaveDefineInstance->role_id = $st_role_id;
                    $leaveDefineInstance->type_id = $leaveDefine->type_id;
                    $leaveDefineInstance->days = $leaveDefine->days;
                    $leaveDefineInstance->school_id = $school_id;
                    $leaveDefineInstance->user_id = $user_id;
                    $leaveDefineInstance->academic_id = $academic_id;
                    $leaveDefineInstance->save();
                    $existingTypes[$leaveDefine->type_id] = true;
                }
            }

            $student->toArray();
            $this->insertStudentRecord($studentData->merge([
                'student_id' => $student->id,
                'is_default' => 1,
                'session' => $academic_id
            ]));
            //end lead convert to student
            DB::commit();
        } catch (\Exception $e) {
            $this->error('Failed to save student: ' . $e->getMessage());
        }
    }

    public function insertStudentRecord($studentData, $pre_record = null)
    {
        $studentRecord = new StudentRecord;
        $studentRecord->student_id = $studentData->student_id;
        $studentRecord->is_promote = $studentData->is_promote ?? 0;
        $studentRecord->is_default = $studentData->is_default;

        $class_id = cache($studentData->class_id) ?: null;
        $section_id = cache($studentData->section_id) ?: null;
        if (!$class_id && !$section_id) {
            $class = new SmClass();
            $class->class_name = $studentData->class_name;
            $class->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $class->save();
            $class_id = $class->id;
            cache([$studentData->class_id => $class->id]);

            $section = new SmSection();
            $section->section_name = $studentData->section_name;
            $section->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
            $section->save();
            $section_id = $section->id;
            cache([$studentData->section_id => $section->id]);
        }

        $studentRecord->class_id = $class_id;
        $studentRecord->section_id = $section_id;

        $studentRecord->session_id = $studentData->session;
        $studentRecord->school_id = Auth::user()->school_id;
        $studentRecord->academic_id = $studentData->session;
        $studentRecord->save();

        if (directFees()) {
            $this->assignDirectFees($studentRecord->id, $studentRecord->class_id, $studentRecord->section_id, null);
        }

        $groups = \Modules\Chat\Entities\Group::where([
            'class_id' => $studentData->class,
            'section_id' => $studentData->section,
            'academic_id' => $studentData->session,
            'school_id' => auth()->user()->school_id
        ])->get();

        $student = SmStudent::where('school_id', auth()->user()->school_id)->find($studentData->student_id);
        if ($student) {
            $student->roll_no = $studentData->roll_no;
            $student->save();
            $user = $student->user;
            foreach ($groups as $group) {
                createGroupUser($group, $user->id, 2, auth()->id());
            }
        }
    }

    protected function getLastProcessedIndex()
    {
        if (Storage::exists($this->progressFilePath)) {
            return (int) Storage::get($this->progressFilePath);
        }
        return -1; // Start from the beginning if no progress file exists
    }

    protected function updateProgress($lastProcessedId)
    {
        Storage::put($this->progressFilePath, $lastProcessedId);
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

    protected function getExamType($title)
    {
        $url = 'http://localhost:9000/api/marks-grade?id=' . $title; // Replace with your actual endpoint
        $response = Http::withHeaders([
            'Authorization' => $this->token, // Replace $token with your actual token
        ])->get($url);

        if ($response->successful()) {
            return $response->object()->data;
        } else {
            return null;
        }
    }

    protected function fileUpload($url, $destination)
    {
        $fileName = "";
        $fileContent = file_get_contents($url);
        if ($fileContent === false) {
            throw new \Exception("Failed to download file from URL.");
        }

        $fileInfo = pathinfo($url);
        $originalName = $fileInfo['basename'];
        $ext = $fileInfo['extension'];

        $tempFilePath = tempnam(sys_get_temp_dir(), 'downloaded_file_');
        file_put_contents($tempFilePath, $fileContent);

        $fileName = md5($originalName . time()) . "." . $ext;
        if (!file_exists($destination)) {
            mkdir($destination, 0777, true);
        }

        rename($tempFilePath, $destination . $fileName);
        unlink($tempFilePath);
        return $destination . $fileName;
    }

    protected function generatePdf($id)
    {
        if (!$this->token)
            $this->login();

        $result_data = $this->fetchStudentRecords($id);
        $view = $this->getView($result_data);

        $result = $view->render();
        $student = $result_data->student;
        $fileName = md5($student->full_name . time());

        $url = env('GOTENBERG_URL', null);
        $request = Gotenberg::chromium($url)
            ->pdf()
            ->skipNetworkIdleEvent()
            ->preferCssPageSize()
            ->outputFilename($fileName)
            ->margins('2mm', '2mm', '2mm', '2mm')
            ->html(Stream::string('index.html', $result));
        $filename = Gotenberg::save($request, 'public/uploads/student/timeline/');

        return (object)[
            'student_id' => $id,
            'exam_type' => $result->student->exam_type,
            'result_file' => 'public/uploads/student/timeline/' . $filename,
        ];
    }
}
