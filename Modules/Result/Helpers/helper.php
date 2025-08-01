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
    function logEmail($title, $dsc, $send_to, $stu_exam, $gmail_message_id = null, $delivery_status = 'sent', $gmail_thread_id = null)
    {

        $emailSmsData = SmEmailSmsLog::where('academic_id', getAcademicId())
            ->where('send_through', $stu_exam)
            ->first();

        if (!$emailSmsData) {
            $emailSmsData = new SmEmailSmsLog();
        }

        $emailSmsData->title = $title;
        $emailSmsData->description = $dsc;
        $emailSmsData->send_through = $stu_exam;
        $emailSmsData->send_date = date('Y-m-d');
        $emailSmsData->send_to = $send_to;
        $emailSmsData->school_id = 1;
        $emailSmsData->academic_id = getAcademicId();

        // Add Gmail-specific fields if provided
        if ($gmail_message_id) {
            $emailSmsData->gmail_message_id = $gmail_message_id;
        }
        if ($gmail_thread_id) {
            $emailSmsData->gmail_thread_id = $gmail_thread_id;
        }
        // $emailSmsData->delivery_status = $delivery_status;

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
            ->margins('4mm', '2mm', '2mm', '2mm')
            ->html(Stream::string('index.html', $result));

        $response = Gotenberg::send($req);
        return $response->withHeader('Content-Disposition', "inline; filename='$fileName.pdf'");
    }
}

if (!function_exists('generatePDFWithPrince')) {
    function generatePDFWithPrince($result_data, $id, $exam_id)
    {
        $school = $result_data->school;
        $student = $result_data->student;
        $records = $result_data->records;
        $score = $result_data->score;
        $ratings = $result_data->ratings;
        $remark = $result_data->remark;

        $compact = compact('student', 'school', 'ratings', 'records', 'score', 'remark');
        $html = view('result::template.result', $compact)->render();

        $fileName = md5($id . $exam_id);

        // Create temporary HTML file
        $tempHtmlPath = storage_path('app/temp/' . $fileName . '.html');
        $tempPdfPath = storage_path('app/temp/' . $fileName . '.pdf');

        // Ensure temp directory exists
        if (!file_exists(dirname($tempHtmlPath))) {
            mkdir(dirname($tempHtmlPath), 0755, true);
        }

        // Write HTML to temporary file
        file_put_contents($tempHtmlPath, $html);

        // Use PrinceXML to convert HTML to PDF - call the binary directly
        // Determine the correct path based on environment (development vs production)
        $appPath = env('APP_ENV') === 'production' ? '/app' : '/var/www';
        $princeBinaryPath = $appPath . '/prince/bin/prince';
        $princePrefixPath = $appPath . '/prince';

        // Check if Prince binary exists
        if (!file_exists($princeBinaryPath)) {
            \Illuminate\Support\Facades\Log::error('PrinceXML binary not found at: ' . $princeBinaryPath);

            // Clean up temp files
            if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);
            if (file_exists($tempPdfPath)) unlink($tempPdfPath);

            return generatePDF($result_data, $id, $exam_id);
        }

        // Build the Prince command with proper escaping and prefix
        $princeCommand = escapeshellarg($princeBinaryPath) . ' --prefix=' . escapeshellarg($princePrefixPath) . ' --page-margin=' . escapeshellarg('4mm 2mm 2mm 2mm') . ' --verbose ' . escapeshellarg($tempHtmlPath) . ' -o ' . escapeshellarg($tempPdfPath);

        $output = [];
        $return_code = 0;
        exec($princeCommand, $output, $return_code);

        if ($return_code !== 0) {
            // Log the error with command details for debugging
            \Illuminate\Support\Facades\Log::error('PrinceXML failed', [
                'command' => $princeCommand,
                'output' => $output,
                'return_code' => $return_code,
                'prince_binary_path' => $princeBinaryPath,
                'prince_exists' => file_exists($princeBinaryPath),
                'prince_executable' => is_executable($princeBinaryPath)
            ]);

            // Clean up temp files
            if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);
            if (file_exists($tempPdfPath)) unlink($tempPdfPath);

            return generatePDF($result_data, $id, $exam_id);
        }

        // Check if PDF was actually created
        if (!file_exists($tempPdfPath) || filesize($tempPdfPath) === 0) {
            \Illuminate\Support\Facades\Log::error('PrinceXML did not create PDF file or file is empty', [
                'temp_pdf_path' => $tempPdfPath,
                'file_exists' => file_exists($tempPdfPath),
                'file_size' => file_exists($tempPdfPath) ? filesize($tempPdfPath) : 'N/A'
            ]);

            // Clean up temp files
            if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);
            if (file_exists($tempPdfPath)) unlink($tempPdfPath);

            return generatePDF($result_data, $id, $exam_id);
        }
        // Read the generated PDF
        $pdfContent = file_get_contents($tempPdfPath);

        // Clean up temporary files
        if (file_exists($tempHtmlPath)) unlink($tempHtmlPath);
        if (file_exists($tempPdfPath)) unlink($tempPdfPath);

        // Create a PSR-7 compatible response similar to Gotenberg
        $stream = \GuzzleHttp\Psr7\Utils::streamFor($pdfContent);
        $response = new \GuzzleHttp\Psr7\Response(200, [
            'Content-Type' => 'application/pdf'
        ], $stream);

        return $response->withHeader('Content-Disposition', "inline; filename='{$fileName}.pdf'");
    }
}
if (!function_exists('emailConfig')) {
    function emailConfig(object $data)
    {
        $setting = SmEmailSetting::where('active_status', 1)
            ->where('school_id', 1)
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
