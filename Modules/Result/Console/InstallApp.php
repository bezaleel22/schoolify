<?php

namespace Modules\Result\Console;

use Throwable;
// use Config;
use App\User;
use App\SmStaff;
use App\SmSchool;
use App\SmGeneralSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;


class InstallApp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'app:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Intsall Schoolify Application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            if (!$this->checkDatabaseConnection()) {
                return;
            }

            $email = env('ADMIN_EMAIL', 'onosbrown.saved@gmail.com');
            $password = env('ADMIN_PASSWORD', '#1414bruno#'); // Default password if not set in .env
            $this->info(sprintf('Installing Schoolify Application...'));

            if (!$email) {
                $this->error('Environment variable ADMIN_EMAIL is not set.');
                return 1;
            }

            $this->info('Migrating Database...');
            if ($this->migrateDB()) {
                $this->info('Done Migrating Database.');
                $ac = Storage::exists('.access_code') ? Storage::get('.access_code') : null;
                Storage::put('.app_installed', $ac);
                Storage::put('.user_email', $email);
                Storage::put('.user_pass', $password);
            } else {
                throw ValidationException::withMessages(['message' => 'There is something wrong in migration. Please contact with script author.']);
            }

            $this->info('Creating the admin usuer...');
            $admin = $this->makeAdmin($email, $password);

            // $this->info('Seeding Database...');
            // $this->seed(true);
            $this->postInstallScript($admin, $email, $password);

            Artisan::call('key:generate', ['--force' => true]);

            $this->info('Done Intsalling');
        } catch (\Exception $e) {
            Storage::delete(['.user_email', '.user_pass']);
            throw ValidationException::withMessages(['message' => $e->getMessage()]);
        }
    }

    protected function postInstallScript($admin, $params) {}

    protected function checkDatabaseConnection()
    {
        if (config('spondonit.support_multi_connection', false)) {
            $db_connection = env('DB_CONNECTION', 'mysql');
        } else {
            $db_connection = 'mysql';
        }
        $db_host = env('DB_HOST', '127.0.0.1');
        $db_port = env('DB_PORT', 3306);
        $db_username = env('DB_USERNAME');
        $db_password = env('DB_PASSWORD');
        $db_database = env('DB_DATABASE');

        try {
            if ($db_connection == 'pgsql') {
                $link = @pg_connect("host=" . $db_host . " dbname=" . $db_database . " user=" . $db_username . " password=" . $db_password . " port=" . $db_port);
            } else {
                $link = @mysqli_connect($db_host, $db_username, $db_password, $db_database, (int)$db_port);
            }
        } catch (\Exception $e) {
            if (mysqli_connect_errno()) {
                $this->error("Failed to connect to MySQL: " . mysqli_connect_error());
            }
            return false;
        }

        if (!$link) {
            return false;
        }


        if ($db_connection != 'pgsql') {
            $count_table_query = mysqli_query($link, "show tables");
            $count_table = mysqli_num_rows($count_table_query);

            if ($count_table) {
                return false;
            }
        }


        return true;
    }

    /**
     * Mirage tables to database
     */
    public function migrateDB()
    {
        $this->rollbackDb();
        try {
            Artisan::call('migrate:fresh', array('--force' => true));
            return true;
        } catch (Throwable $e) {
            $this->rollbackDb();
            Log::error($e);
            return false;
            $sql = base_path('database/infix_edu.sql');
            if (File::exists($sql)) {
                DB::unprepared(file_get_contents($sql));
            }
        }
    }

    /**
     * Seed tables to database
     */
    protected function seed($seed = false)
    {
        if (!$seed) {
            return;
        }

        Artisan::call('db:seed', array('--force' => true));
    }

    /**
     * Insert default admin details
     */
    protected function makeAdmin($email, $password)
    {
        try {
            $user_class = new User();
            $user = $user_class->find(1);
            if (!$user) {
                $user = new User();
            }

            $user->email = $email;
            if (Schema::hasColumn('users', 'role_id')) {
                $user->role_id = 1;
            }

            $user->password = bcrypt($password);
            $user->save();

            $staff = SmStaff::first();
            if (empty($staff)) {
                $staff = new SmStaff();
            }
            $staff->user_id  = $user->id;
            $staff->first_name  = 'System';
            $staff->last_name  = 'Administrator';
            $staff->full_name  = 'System Administrator';
            $staff->email  = $user->email;
            $staff->save();

            $setting =  SmGeneralSettings::first();
            $setting->email = $user->email;
            $setting->system_purchase_code = Storage::get('.access_code');
            $setting->system_activated_date = date('Y-m-d');
            $setting->system_domain = app_url();
            $setting->save();

            $school = SmSchool::first();
            $school->email = $user->email;
            $school->save();
        } catch (\Exception $e) {
            $this->rollbackDb();
            throw ValidationException::withMessages(['message' => $e->getMessage()]);
        }
    }

    protected function rollbackDb()
    {
        Artisan::call('db:wipe', array('--force' => true));
    }
}
