<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use App\SmStaff;
use App\SmSchool;
use App\SmGeneralSettings;
use Illuminate\Support\Facades\Storage;

class MakeAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email} {password?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user with provided email and password';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD', 'abcd1234'); // Default password if not set in .env

        if (!$email) {
            $this->error('Environment variable ADMIN_EMAIL is not set.');
            return 1;
        }

        $params = [
            'email' => $email,
            'password' => $password,
        ];

        try {
            $this->makeAdmin($params);
            $this->info('Admin user created successfully.');
        } catch (\Exception $e) {
            $this->error('Failed to create admin user: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Function to create an admin user.
     *
     * @param array $params
     * @return void
     * @throws ValidationException
     */
    public function makeAdmin($params)
    {
        try {
            $user_model_name = config('spondonit.user_model');
            $user_class = new $user_model_name;
            $user = $user_class->find(1);
            if (!$user) {
                $user = new $user_model_name;
            }

            $user->email = $params['email'];
            if (Schema::hasColumn('users', 'role_id')) {
                $user->role_id = 1;
            }

            $user->password = bcrypt($params['password']);
            $user->save();

            $staff = SmStaff::first();
            if (empty($staff)) {
                $staff = new SmStaff();
            }
            $staff->user_id = $user->id;
            $staff->first_name = 'System';
            $staff->last_name = 'Administrator';
            $staff->full_name = 'System Administrator';
            $staff->email = $user->email;
            $staff->save();

            $setting = SmGeneralSettings::first();
            $setting->email = $user->email;
            $setting->system_purchase_code = Storage::get('.access_code');
            $setting->system_activated_date = date('Y-m-d');
            $setting->system_domain = app_url();
            $setting->save();

            $school = SmSchool::first();
            $school->email = $user->email;
            $school->save();
        } catch (\Exception $e) {
            throw ValidationException::withMessages(['message' => $e->getMessage()]);
        }
    }
}
