<?php

// Get File Path From HELPER

use App\SmEmailSetting;
use App\SmEmailSmsLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Gotenberg\Stream;
use Gotenberg\Gotenberg;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

if (!function_exists('logEmail')) {
    function logEmail($title, $dsc, $send_to)
    {
        $emailSmsData = new SmEmailSmsLog();
        $emailSmsData->title = $title;
        $emailSmsData->description = $dsc;
        $emailSmsData->send_through = 'result-notice';
        $emailSmsData->send_date = date('Y-m-d');
        $emailSmsData->send_to = $send_to;
        $emailSmsData->school_id = 1;
        $emailSmsData->academic_id = getAcademicId();
        $success = $emailSmsData->save();

        return $success;
    }
}

if (!function_exists('mapRating')) {
    function mapRating($rate = 0)
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
}

if (!function_exists('contactsForMail')) {
    function contactsForMail($data)
    {
        if ($data) {
            $name = explode('/', $data);
            return $name[4] ?? $name[0];
        } else {
            return '';
        }
    }
}

if (!function_exists('generatePDF')) {
    function generatePDF($result_data, $id, $exam_id)
    {
        $school = $result_data->school;
        $student = $result_data->student;
        $records = $result_data->records;
        $score = $result_data->score;
        $ratings = $result_data->ratings;
        $remark = $result_data->remark;

        $compact = compact('student', 'school', 'ratings', 'records', 'score', 'remark');
        $result = view('result::template.result', $compact)->render();

        $fileName = md5($id . $exam_id);
        $url = env('GOTENBERG_URL');
        $req = Gotenberg::chromium($url)
            ->pdf()
            ->skipNetworkIdleEvent()
            ->preferCssPageSize()
            ->outputFilename($fileName)
            ->margins('2mm', '2mm', '2mm', '2mm')
            ->html(Stream::string('index.html', $result));

        $response = Gotenberg::send($req);
        return $response->withHeader('Content-Disposition', "inline; filename='$fileName.pdf'");
    }
}


if (!function_exists('emailConfig')) {
    function emailConfig(object $data)
    {
        $setting = SmEmailSetting::where('active_status', 1)
            ->where('school_id', Auth::user()->school_id)
            ->first();

        if (!$setting) {
            throw new \Exception('');
        }

        Config::set('mail.default', $setting->mail_driver);
        Config::set('mail.from.from', $setting->mail_username);
        Config::set('mail.from.name', $setting->from_name);
        Config::set('mail.mailers.smtp.host', $setting->mail_host);
        Config::set('mail.mailers.smtp.port', $setting->mail_port);
        Config::set('mail.mailers.smtp.username', $setting->mail_username);
        Config::set('mail.mailers.smtp.password', $setting->mail_password);
        Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption);

        $data->sender_name = $setting->from_name;
        $data->sender_email = $setting->from_email;
        return $data;
    }
}
