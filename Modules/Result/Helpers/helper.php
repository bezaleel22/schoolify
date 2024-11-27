<?php

// Get File Path From HELPER

use App\SmEmailSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Modules\Result\Jobs\SendResultEmail;

if (!function_exists('showTimelineDocName')) {
    function showTimelineDocName($data)
    {
        $name = explode('/', $data);
        $number = count($name);
        return $name[$number - 1];
    }
}

if (!function_exists('showDocumentName')) {
    function showDocumentName($data)
    {
        $name = explode('/', $data);
        $number = count($name);
        return $name[$number - 1];
    }
}

if (!function_exists('getResulteData')) {
    function getFileName($data)
    {
        if ($data) {
            $name = explode('/', $data);
            return $name[4] ?? $name[0];
        } else {
            return '';
        }
    }
}

if (!function_exists('post_mail')) {
    function post_mail($student, $data = [])
    {
        $setting = SmEmailSetting::where('active_status', 1)->where('school_id', Auth::user()->school_id)->first();
        if (!$setting) {
            throw new \Exception('');
        }

        $sender_email = $setting->from_email;
        $sender_name = $setting->from_name;
        $email_driver = $setting->mail_driver;

        Config::set('mail.default', $setting->mail_driver);
        Config::set('mail.from.from', $setting->mail_username);
        Config::set('mail.from.name', $setting->from_name);
        Config::set('mail.mailers.smtp.host', $setting->mail_host);
        Config::set('mail.mailers.smtp.port', $setting->mail_port);
        Config::set('mail.mailers.smtp.username', $setting->mail_username);
        Config::set('mail.mailers.smtp.password', $setting->mail_password);
        Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);

        $data['driver'] = $email_driver;
        $data['sender_name'] = $sender_name;
        $data['sender_email'] = $sender_email;

        dispatch(new SendResultEmail($student, $data));
    }
}
