<?php

namespace Modules\Result\Console;

use App\User;
use App\SmClass;
use App\SmStaff;
use App\SmParent;
use App\SmSubject;
use App\YearCheck;
use App\SmStudent;
use App\SmSection;
use App\SmDesignation;
use App\SmClassSection;
use App\SmClassTeacher;
use App\SmAssignSubject;
use App\SmStudentTimeline;
use App\SmStudentCategory;
use App\SmHumanDepartment;
use App\SmAssignClassTeacher;
use App\Models\StudentRecord;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Scopes\StatusAcademicSchoolScope;
use Modules\RolePermission\Entities\InfixRole;

class SeedApp extends Command
{
    protected $signature = 'result:seed';
    protected $description = 'Seeds data and deletes old result PDF files';
    protected $jsonFilePath = 'school_data.json';
    protected Collection $exam_types;

    // IDs arrays
    protected $user_ids = [];
    protected $parent_ids = [];
    protected $class_ids = [];
    protected $section_ids = [];
    protected $student_ids = [];
    protected $designation_ids = [];
    protected $department_ids = [];
    protected $subject_ids = [];
    protected $category_ids = [];

    protected $role_id;
    protected $dept_id;
    protected $desig_id;
    protected $category_id;
    protected $academic_id;
    protected $staff_ids;
    protected $dept_academic_id;
    protected $desig_hod_id;

    public function handle()
    {
        DB::beginTransaction();
        try {
            if (!$this->checkJsonFileExists()) {
                return;
            }

            $json_data = $this->loadJsonData();
            if (!$json_data) {
                return;
            }

            $this->exam_types = collect($json_data['exam_types']);
            $this->academic_id = getAcademicId();

            $this->createDefaults();
            $this->seedClasses($json_data);
            $this->seedSections($json_data);
            $this->seedSubjects($json_data);
            $this->seedDepartments($json_data);
            $this->seedDesignations($json_data);
            $this->seedCategories($json_data);

            $this->seedUsers($json_data);
            $this->seedStaff($json_data);
            $this->seedParents($json_data);
            $this->seedStudents($json_data);

            $this->seedStudentRecords($json_data);
            $this->seedStudentTimelines($json_data);

            $this->seedAssignSubjects($json_data);
            $this->seedClassTeachers($json_data);
            DB::commit();

            $classes = SmClass::where('academic_id', $this->academic_id)->where('school_id', 1)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            $sections = SmSection::where('academic_id', $this->academic_id)->where('school_id', 1)->withoutGlobalScope(StatusAcademicSchoolScope::class)->get();
            foreach ($classes as $class) {
                foreach ($sections as $section) {
                    $class_section = new SmClassSection();
                    $class_section->class_id = $class->id;
                    $class_section->section_id = $section->id;
                    $class_section->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');;
                    $class_section->school_id = 1;
                    $class_section->academic_id = $this->academic_id;
                    $class_section->save();
                }
            }
            $this->info('Data import completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function checkJsonFileExists()
    {
        if (!Storage::exists($this->jsonFilePath)) {
            $this->error('JSON file does not exist.');
            return false;
        }
        return true;
    }

    protected function loadJsonData()
    {
        $jsonContent = Storage::get($this->jsonFilePath);
        $json_data = json_decode($jsonContent, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Failed to decode JSON.');
            return null;
        }
        return $json_data;
    }

    protected function createDefaults()
    {
        $role = new InfixRole();
        $role->name = 'Others';
        $role->type = 'User Defined';
        $role->school_id = 1;
        $role->save();
        $this->role_id = $role->id;

        $dept = new SmHumanDepartment();
        $dept->name = 'Others';
        $dept->school_id = 1;
        $dept->save();
        $this->dept_id = $dept->id;

        $desig = new SmDesignation();
        $desig->title = 'Others';
        $desig->school_id = 1;
        $desig->save();
        $this->desig_id = $desig->id;

        $student_cat = new SmStudentCategory();
        $student_cat->category_name = 'NONE';
        $student_cat->save();
        $this->category_id = $student_cat->id;
    }

    protected function seedClasses($json_data)
    {
        $class_data = collect($json_data['classes'])->map(function ($class) {
            return [
                'class_name' => $class['class_name'],
                'school_id' => 1,
                'academic_id' => $this->academic_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        SmClass::insert($class_data);
        $this->class_ids = SmClass::whereIn('class_name', collect($class_data)->pluck('class_name'))->pluck('id', 'class_name')->toArray();
        $this->info('Classes seeded successfully.');
    }

    protected function seedSections($json_data)
    {
        $section_data = collect($json_data['sections'])->map(function ($section) {
            return [
                'section_name' => $section['section_name'],
                'school_id' => 1,
                'academic_id' => $this->academic_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        SmSection::insert($section_data);
        $this->section_ids = SmSection::whereIn('section_name', collect($section_data)->pluck('section_name'))->pluck('id', 'section_name')->toArray();
        $this->info('Sections seeded successfully.');
    }


    protected function seedDepartments($json_data)
    {
        $dept = new SmHumanDepartment();
        $dept->name = 'Academic';
        $dept->school_id = 1;
        $dept->save();
        $this->dept_academic_id = $dept->id;

        $department_data = collect($json_data['departments'])
            ->filter(fn($department) => $department['id'] !== 2)
            ->map(function ($department) {
                return [
                    'name' => $department['name'],
                    'school_id' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

        SmHumanDepartment::insert($department_data);
        $this->department_ids = SmHumanDepartment::whereIn('name', collect($department_data)->pluck('name'))->pluck('id', 'name')->toArray();
        $this->info('Departments seeded successfully.');
    }

    protected function seedDesignations($json_data)
    {
        $desig = new SmDesignation();
        $desig->title = 'Head of Department';
        $desig->school_id = 1;
        $desig->save();
        $this->desig_hod_id = $desig->id;

        $designation_data = collect($json_data['designations'])->map(function ($designation) {
            return [
                'title' => $designation['title'],
                'school_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        SmDesignation::insert($designation_data);
        $this->designation_ids = SmDesignation::whereIn('title', collect($designation_data)->pluck('title'))->pluck('id', 'title')->toArray();
        $this->info('Designations seeded successfully.');
    }

    protected function seedCategories($json_data)
    {
        $category_data = collect($json_data['categories'])->map(function ($category) {
            return [
                'category_name' => $category['category_name'],
                'school_id' => 1,
                'academic_id' => $this->academic_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        $this->category_ids = SmStudentCategory::whereIn('category_name', collect($category_data)->pluck('category_name'))->pluck('id', 'category_name')->toArray();
        SmStudentCategory::insert($category_data);
        $this->info('Student categories seeded successfully.');
    }

    protected function seedSubjects($json_data)
    {
        $subject_data = collect($json_data['subjects'])->map(function ($subject) {
            return [
                'subject_name' => $subject['subject_name'],
                'subject_code' => $subject['subject_code'],
                'school_id' => 1,
                'academic_id' => $this->academic_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        SmSubject::insert($subject_data);
        $this->subject_ids = SmSubject::whereIn('subject_name', collect($subject_data)->pluck('subject_name'))->pluck('id', 'subject_name')->toArray();
        $this->info('Subjects seeded successfully.');
    }

    protected function seedUsers($json_data)
    {
        $user_data = collect($json_data['users'])->map(function ($user) {
            return [
                'role_id' => $user['role_id'] > 9 ? $this->role_id : $user['role_id'],
                'full_name' => $user['full_name'],
                'username' => $user['username'],
                'email' => $user['email'],
                'phone_number' => $user['phone_number'],
                'password' => Hash::make('123456'),
                'language' => 'en',
                'school_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        User::insert($user_data);
        $this->user_ids = User::whereIn('username', collect($user_data)->pluck('username'))->pluck('id', 'username')->toArray();
        $this->info('Users seeded successfully.');
    }

    protected function seedStaff($json_data)
    {

        $staff_data = collect($json_data['staff_data'])->map(function ($staff) {
            $username = $staff['staff_user']['username'];
            $designation = $staff['designations']['title'] ?? null;
            $department = $staff['departments']['name'] ?? null;

            // Determine designation_id and department_id
            $designation_id = in_array($this->designation_ids[$designation] ?? null, [2, 3, 8])
                ? $this->desig_hod_id
                : $this->designation_ids[$designation] ?? $this->desig_id;

            $department_id = in_array($this->department_ids[$department] ?? null, [2, 3])
                ? $this->dept_academic_id
                : $this->department_ids[$department] ?? $this->dept_id;

            return [
                'user_id' => $this->user_ids[$username] ?? null,
                'role_id' => $staff['role_id'] > 9 ? $this->role_id : $staff['role_id'],
                'staff_no' => $staff['staff_no'],
                'first_name' => $staff['first_name'],
                'last_name' => $staff['last_name'],
                'full_name' => $staff['full_name'],
                'email' => $staff['email'],
                'mobile' => $staff['mobile'],
                'emergency_mobile' => $staff['emergency_mobile'],
                'gender_id' => $staff['gender_id'],
                'designation_id' => $designation_id,
                'department_id' => $department_id,
                'marital_status' => $staff['marital_status'],
                'date_of_birth' => $staff['date_of_birth'],
                'date_of_joining' => $staff['date_of_joining'],
                'basic_salary' => $staff['basic_salary'],
                'current_address' => $staff['current_address'],
                'school_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        SmStaff::insert($staff_data);
        $this->staff_ids = SmStaff::whereIn('staff_no', collect($staff_data)->pluck('staff_no'))->pluck('id', 'staff_no')->toArray();
        $this->info('Staff members seeded successfully.');
    }


    protected function seedParents($json_data)
    {
        $parent_data = collect($json_data['parents'])
            ->filter(fn($parent) => $parent['parent_user'] !== null)
            ->map(function ($parent) {
                $username = $parent['parent_user']['username'];
                return [
                    'user_id' => $user_ids[$username] ?? null,
                    'fathers_name' => $parent['fathers_name'],
                    'fathers_mobile' => $parent['fathers_mobile'],
                    'mothers_name' => $parent['mothers_name'],
                    'mothers_mobile' => $parent['mothers_mobile'],
                    'guardians_name' => $parent['guardians_name'],
                    'guardians_mobile' => $parent['guardians_mobile'],
                    'guardians_email' => $parent['guardians_email'],
                    'guardians_relation' => $parent['guardians_relation'],
                    'relation' => $parent['relation'],
                    'is_guardian' => $parent['is_guardian'],
                    'school_id' => 1,
                    'academic_id' => $this->academic_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
        SmParent::insert($parent_data);
        $this->parent_ids = SmParent::whereIn('guardians_email', collect($parent_data)->pluck('guardians_email'))->pluck('id', 'guardians_email')->toArray();
        $this->info('Parents seeded successfully.');
    }

    protected function seedStudents($json_data)
    {
        $student_data = collect($json_data['students'])
            ->map(function ($student) {
                $username = $student['user']['username'];
                $guardians_email = $student['parents']['guardians_email'] ?? null;
                $category_id = $this->category_ids[$student['category']['category_name'] ?? null] ?? $this->category_id;
                return [
                    'user_id' => $this->user_ids[$username],
                    'parent_id' => $this->parent_ids[$guardians_email] ?? null,
                    'role_id' => 2,
                    'admission_no' => $student['id'],
                    'first_name' => $student['first_name'],
                    'last_name' => $student['last_name'],
                    'full_name' => $student['full_name'],
                    'gender_id' => $student['gender_id'],
                    'student_category_id' => $category_id,
                    'date_of_birth' => date('Y-m-d', strtotime($student['date_of_birth'])),
                    'mobile' => $student['mobile'],
                    'admission_date' => date('Y-m-d', strtotime($student['admission_date'])),
                    'student_photo' => $student['student_photo'],
                    'school_id' => 1,
                    'academic_id' => $this->academic_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
        SmStudent::insert($student_data);
        $this->student_ids = SmStudent::whereIn('admission_no', collect($student_data)->pluck('admission_no'))->pluck('id', 'admission_no')->toArray();
        $this->info('Students seeded successfully.');
    }

    protected function seedStudentRecords($json_data)
    {
        $student_records_data = collect($json_data['student_records'])
            ->filter(fn($record) => $record['section_id'] !== null && $record['class_id'] !== null)
            ->map(function ($record) {
                $student_id = $record['student_id'];
                $class_name = $record['class']['class_name'];
                $section_name = $record['section']['section_name'];
                return [
                    'student_id' => $this->student_ids[$student_id] ?? null,
                    'class_id' => $this->class_ids[$class_name] ?? null,
                    'section_id' => $this->section_ids[$section_name] ?? null,
                    'is_promote' => $record['is_promote'] ?? 0,
                    'is_default' => $record['is_default'],
                    'school_id' => 1,
                    'academic_id' => $this->academic_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();
        StudentRecord::insert($student_records_data);
        $this->info('Student records seeded successfully.');
    }

    protected function seedStudentTimelines($json_data)
    {
        $student_timeline_data = collect($json_data['timelines'])
            ->map(function ($timeline) {
                $student_id = $this->student_ids[$timeline['staff_student_id']];
                $is_url =  filter_var($timeline['file'], FILTER_VALIDATE_URL) !== false;
                $local_id = $timeline['staff_student_id'];
                $type = (object) $this->exam_types->firstWhere('title', $timeline['title']);
                if (!isset($type->id)) {
                    $this->info(print_r($timeline, true));
                    $this->info(print_r($this->exam_types, true));
                    $this->info(print_r($type, true));
                    throw new \Exception('Debug');
                }
                $route = "download-result/$student_id?local_stu_id=$local_id&exam_id=$type->id";

                return [
                    'staff_student_id' =>  $student_id,
                    'type' =>  "local-$type->id",
                    'title' => $timeline['title'],
                    'date' =>  date('Y-m-d', strtotime($timeline['date'])),
                    'description' => $timeline['description'],
                    'file' => $is_url ? $route : $timeline['file'],
                    'visible_to_student' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->filter(fn($timeline) => $timeline !== null)->toArray();
        SmStudentTimeline::insert($student_timeline_data);
        $this->info('Student timelines seeded successfully.');
    }

    protected function seedAssignSubjects($json_data)
    {
        $assign_subject_data = collect($json_data['assign_subjects'])->map(function ($assign_subject) {
            return [
                'class_id' => $this->class_ids[$assign_subject['class']['class_name']],
                'section_id' => $this->section_ids[$assign_subject['section']['section_name']],
                'subject_id' => $this->subject_ids[$assign_subject['subject']['subject_name']],
                'teacher_id' => $this->staff_ids[$assign_subject['teacher']['staff_no']],
                'school_id' => 1,
                'academic_id' => $this->academic_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        SmAssignSubject::insert($assign_subject_data);
        $this->info('Assigned subjects seeded successfully.');
    }

    protected function seedClassTeachers($json_data)
    {
        $class_teachers_data = collect($json_data['class_teachers'])->map(function ($class_teacher) use ($json_data) {
            $class_id = $this->class_ids[$class_teacher['class_name']];
            $section_id =  $this->section_ids[$class_teacher['section_name']];
            $teacher_id =  $this->staff_ids[$class_teacher['staff_no']];

            $assign_class_teacher = new SmAssignClassTeacher();
            $assign_class_teacher->class_id = $class_id;
            $assign_class_teacher->section_id = $section_id;
            $assign_class_teacher->school_id = 1;
            $assign_class_teacher->academic_id = $this->academic_id;
            $assign_class_teacher->save();

            return [
                'assign_class_teacher_id' => $assign_class_teacher->id,
                'teacher_id' => $teacher_id,
                'school_id' => 1,
                'academic_id' => $this->academic_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();

        SmClassTeacher::insert($class_teachers_data);
        $this->info('Assigned class teachers seeded successfully.');
    }
}
