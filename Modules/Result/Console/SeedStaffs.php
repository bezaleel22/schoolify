<?php

namespace Modules\Result\Console;

use App\User;
use App\SmClass;
use App\SmParent;
use App\SmStaff;
use App\SmSection;
use App\SmAssignClassTeacher;
use App\SmAssignSubject;
use App\SmClassTeacher;
use App\SmDesignation;
use App\SmHumanDepartment;
use App\SmSubject;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Modules\RolePermission\Entities\InfixRole;

class SeedStaffs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:staffs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get staff data from JSON file and save to database';

    /**
     * Path to the JSON file where staffs will be saved.
     * 
     * @var string
     */
    protected $jsonFilePath = 'staff_data/staffs.json';

    /**
     * Path to the file where the last processed ID will be saved.
     *
     * @var string
     */

    protected $failed_staffs_json = 'unprocessd_staff.json';
    protected $failed_staffs = [];
    protected $totalstaffs = 0;
    protected  $token = '';


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        pcntl_async_signals(true);
        pcntl_signal(SIGINT, $this->shutdown());
        pcntl_signal(SIGTERM, $this->shutdown());

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

        $this->totalstaffs = count($json_data);

        $progressBar = $this->output->createProgressBar($this->totalstaffs);
        $progressBar->start();

        foreach ($json_data as $idx => $staffData) {
            $this->saveStaff($staffData, $idx);
            $progressBar->advance();
        }

        $progressBar->finish();
        $unprocessed = json_encode($this->failed_staffs, JSON_PRETTY_PRINT);
        Storage::put($this->failed_staffs_json, $unprocessed);
        $this->info("\nImport completed.");
    }

    protected function saveStaff($staff_data, $idx)
    {
        $staffData = (object) $staff_data;
        $exist_staff = SmStaff::where('staff_no', $staffData->staff_no)->first();
        if ($exist_staff) {
            return;
        }

        $subjects = collect($staffData->subjects);
        $role = (object) $staffData->roles;
        $department = (object) $staffData->departments;
        $designation = (object) $staffData->designations;
        $gender = (object) $staffData->genders;


        DB::beginTransaction();
        try {
            $user = new User();
            $user->role_id = $this->getRole($role);;
            $user->username = $staffData->mobile ?: $staffData->email;
            $user->email = $staffData->email;
            $user->full_name = $staffData->full_name;
            $user->password = Hash::make(123456);
            $user->school_id = 1;
            $user->save();

            $basic_salary = !empty($staffData->basic_salary) ? $staffData->basic_salary : 0;
            $staff = new SmStaff();
            $staff->staff_no = $staffData->staff_no;
            $staff->role_id = $this->getRole($role);
            $staff->department_id = $this->createDepartment($department);
            $staff->designation_id = $this->createDesignation($designation);
            $staff->first_name = $staffData->first_name;
            $staff->last_name = $staffData->last_name;
            $staff->full_name = $staffData->full_name;
            $staff->email = $staffData->email;
            $staff->school_id = 1;
            $staff->gender_id = $staffData->gender_id;
            $staff->marital_status = $staffData->marital_status;
            $staff->date_of_birth = date('Y-m-d', strtotime($staffData->date_of_birth));
            $staff->date_of_joining = date('Y-m-d', strtotime($staffData->date_of_joining));
            $staff->mobile = $staffData->mobile ?? null;
            $staff->emergency_mobile = $staffData->emergency_mobile;
            $staff->current_address = $staffData->current_address;
            $staff->permanent_address = $staffData->permanent_address;
            $staff->qualification = $staffData->qualification;
            $staff->experience = $staffData->experience;
            $staff->epf_no = $staffData->epf_no;
            $staff->basic_salary = $basic_salary;
            $staff->contract_type = $staffData->contract_type;
            $staff->location = $staffData->location;
            $staff->bank_account_name = $staffData->bank_account_name;
            $staff->bank_account_no = $staffData->bank_account_no;
            $staff->bank_name = $staffData->bank_name;
            $staff->bank_brach = $staffData->bank_brach;
            $staff->facebook_url = $staffData->facebook_url;
            $staff->twiteer_url = $staffData->twiteer_url;
            $staff->linkedin_url = $staffData->linkedin_url;
            $staff->instragram_url = $staffData->instragram_url;
            $staff->user_id = $user->id;
            $staff->save();

            if (!$subjects->empty() && $staffData->role_id === 4) {
                foreach ($subjects as $subject_data) {
                    $this->createSubject($subject_data, $staff->id);
                    $this->assignTeacher($subject_data, $staff->id);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            array_push($this->failed_staffs, $staff_data);
            $this->error('Failed to process student at: ' . $idx . ' with ID: ' . ($staffData->id) . ': ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getRole($role_data)
    {
        if (in_array($role_data->id, [13, 14])) {
            return 5;
        }

        if ($role_data->id === 10) {
            $role = new InfixRole();
            $role->name = 'Others';
            $role->type = 'User Defined';
            $role->school_id = 1;
            $role->save();
            return $role->id;
        }

        return $role_data->id;
    }
    protected function createDepartment($department)
    {
        $dept = SmHumanDepartment::where('name', $department->name)->first();
        if ($dept) {
            return $dept->id;
        }

        if (in_array($department->id, [2, 3])) {
            $dept = new SmHumanDepartment();
            $dept->name = 'Academic';
            $dept->school_id = 1;
            $dept->save();
        }

        $dept = new SmHumanDepartment();
        $dept->name = $department->name;
        $dept->school_id = 1;
        $dept->save();

        return $dept->id;
    }
    protected function createDesignation($designation_data)
    {
        $desig = SmDesignation::where('title', $designation_data->title)->first();
        if ($desig) {
            return $desig->id;
        }

        if (in_array($designation_data->id, [2, 3, 8])) {
            $desig = new SmDesignation();
            $desig->title = 'Head of Department';
            $desig->school_id = 1;
            $desig->save();
            return $desig->id;
        }

        $desig = new SmDesignation();
        $desig->title = $designation_data->title;
        $desig->school_id = 1;
        $desig->save();
        return $desig->id;
    }

    protected function createSubject($subject_data, $staff_id)
    {
        $class = SmClass::where('class_name', $subject_data->class_name)->first();
        $section = SmSection::where('section_name', $subject_data->section_name)->first();

        $subject = SmSubject::where('subject_name', $subject_data->subject_name)->first();
        if (!$subject) {
            $subject = new SmSubject();
            $subject->subject_name = $subject_data->subject_name;
            $subject->subject_type = $subject_data->subject_type;
            $subject->subject_code = $subject_data->subject_code;
            $subject->school_id = 1;
            $subject->save();
        }

        $assign_subject = SmAssignSubject::where('class_id', $class->id)->where('section_id', $section->section_id)->where('subject_id', $subject->id)->first();
        if (!$assign_subject) {
            $assign_subject = new SmAssignSubject();
            $assign_subject->class_id = $class->id;
            $assign_subject->section_id = $section->section_id;
            $assign_subject->subject_id = $subject->id;
            $assign_subject->teacher_id = $staff_id;
            $assign_subject->school_id = 1;
            $assign_subject->academic_id = getAcademicId();
            $assign_subject->save();
        }
    }

    protected function assignTeacher($subject_data, $staff_id)
    {
        $class = SmClass::where('class_name', $subject_data->class_name)->first();
        $section = SmSection::where('section_name', $subject_data->section_name)->first();

        $assign_class_teacher = SmAssignClassTeacher::where('class_id', $class->id)->where('section_id', $section->id)->first();
        if (!$assign_class_teacher) {
            $assign_class_teacher = new SmAssignClassTeacher();
            $assign_class_teacher->class_id = $class->id;
            $assign_class_teacher->section_id = $section->id;
            $assign_class_teacher->school_id = 1;
            $assign_class_teacher->academic_id = getAcademicId();
            $assign_class_teacher->save();
        }

        $class_teacher = SmClassTeacher::where('assign_class_teacher_id', $assign_class_teacher->id)->first();
        if (!$class_teacher) {
            $class_teacher = new SmClassTeacher();
            $class_teacher->assign_class_teacher_id = $assign_class_teacher->id;
            $class_teacher->teacher_id = $staff_id;
            $class_teacher->school_id = 1;
            $class_teacher->academic_id = getAcademicId();
            $class_teacher->save();
        }
    }

    protected function shutdown()
    {
        return function ($signo) {
            Storage::put($this->failed_staffs_json, json_encode($this->failed_staffs, JSON_PRETTY_PRINT));
            $this->info("\nProccessed: ' . $this->totalstaffs - count($this->failed_staffs) . '/' . $this->totalstaffs");
            $this->info('Terminated by system. Exiting...');
            exit(0);
        };
    }
}
