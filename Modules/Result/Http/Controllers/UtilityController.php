<?php

namespace Modules\Result\Http\Controllers;

use App\SmSmsGateway;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Models\MaintenanceSetting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Modules\RolePermission\Entities\InfixRole;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class UtilityController extends Controller
{

    public function index()
    {
        try {
            if (auth()->user()->school_id == 1) {
                $roles = InfixRole::where('is_saas', 0)->where('id', '!=', 1)->get();
                $setting = MaintenanceSetting::where('school_id', auth()->user()->school_id)->first();
                return view('result::utilityView', compact('setting', 'roles'));
            } else {
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->route('admin-dashboard');
            }
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function action($action)
    {

        if (config('app.app_sync')) {
            Toastr::error('Restricted in demo mode');
            return back();
        }
        try {
            $message = "";
            if ($action == "optimize_clear") {

                Artisan::call('optimize:clear');

                $message = "Your System Optimization Successfully Complete";
            } elseif ($action == "clear_log") {
                file_put_contents(storage_path('logs/laravel.log'), '');

                $message = "Your System Log File Is Cleared";
            } elseif ($action == "change_debug") {
                if (env('APP_DEBUG')) {
                    envu([
                        'APP_ENV' => 'Production',
                        'APP_DEBUG'     => 'false',
                    ]);

                    $message = "Debug Mode Disable Successfully ";
                } else {
                    envu([
                        'APP_ENV' => 'Production',
                        'APP_DEBUG'     =>  'true',
                    ]);

                    $message = "Debug Mode Enable Successfully";
                }
            } elseif ($action == "force_https") {
                if (env('FORCE_HTTPS')) {
                    envu([
                        'FORCE_HTTPS'     =>  'false',
                    ]);
                    $message = "HTTPS Mode Disable Successfully";
                } else {
                    envu([
                        'FORCE_HTTPS'     =>  'true',
                    ]);
                    $message = "HTTPS Mode Enable Successfully ";
                }
            }
            Toastr::success($message, 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    public function testup()
    {
        try {

            $gateway = SmSmsGateway::where('gateway_name', 'Himalayasms')->first();
            $client = new Client();
            $request = $client->get("https://sms.techhimalaya.com/base/smsapi/index.php", [
                'query' => [
                    'key' => $gateway->himalayasms_key,
                    'senderid' => $gateway->himalayasms_senderId,
                    'campaign' => $gateway->himalayasms_campaign,
                    'routeid' => $gateway->himalayasms_routeId,
                    'contacts' => "+9779865383233",
                    'msg' => "Hello I am from infixedu, It Is test example sms",
                    'type' => "text"
                ],
                'http_errors' => false
            ]);

            $response = $request->getBody();
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }

    public function updateMaintenance(Request $request)
    {
        if (config('app.app_sync')) {
            Toastr::error('Restricted in demo mode');
            return back();
        }
        try {
            if (config('app.app_sync')) {
                Toastr::error('Restricted in demo mode');
                return back();
            }
            $setting = MaintenanceSetting::first();
            $destination = "public/uploads/settings/";
            if (!$setting) {
                $setting = new MaintenanceSetting();
            }
            $setting->maintenance_mode = $request->maintenance_mode;
            $setting->title = $request->title;
            $setting->sub_title = $request->sub_title;
            $setting->applicable_for = $request->applicable_for ? $request->applicable_for : [];
            $setting->image = $request->image ? fileUpload($request->image, $destination) : $setting->image;
            $setting->school_id = auth()->user()->school_id;
            $setting->save();
            Toastr::success('Operation Success', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            Toastr::error('Operation Failed', 'Failed');
            return redirect()->back();
        }
    }

    /**
     * Stream a zip archive of the public/uploads/student folder for download
     */
    public function downloadStudentUploads()
    {
        $folder = public_path('uploads/student');
        $zipFile = storage_path('app/student_uploads_' . date('Ymd_His') . '.zip');

        if (!is_dir($folder)) {
            Toastr::error('Student uploads folder not found.');
            return redirect()->back();
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Toastr::error('Could not create zip file.');
            return redirect()->back();
        }

        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($folder),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($folder) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();

        return response()->download($zipFile)->deleteFileAfterSend(true);
    }
}
