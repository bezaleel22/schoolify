<?php

namespace Modules\Result\Http\Controllers;

use App\SmStudent;
use App\SmStudentTimeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Brian2694\Toastr\Facades\Toastr;
use App\Http\Controllers\Controller;

class ImportController extends Controller
{
    protected $message;

    public function upload()
    {

        try {
            SmStudent::all()->map(function ($student) {
                $this->fileUpload($student->student_photo);
            });

            SmStudentTimeline::all()->map(function ($timeline) {
                $this->fileUpload($timeline->file);
            });

            $this->message = 'Data import completed successfully.';
            Toastr::success($this->message, 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    protected function fileUpload($file)
    {
        $fileContent = null;
        $base_url = env('LOCAL_BASE_URL');

        $fileInfo = pathinfo($file);
        if (!isset($fileInfo['extension'])) return;

        $temp = tempnam(sys_get_temp_dir(), 'downloaded_');
        try {
            $fileContent = file_get_contents("$base_url/$file");
            file_put_contents($temp, $fileContent);
            $dirname = $fileInfo['dirname'];
            if (!file_exists($dirname)) mkdir($dirname, 0775, true);

            rename($temp, $file);
        } catch (\Exception $e) {
            if ($fileContent !== null) unlink($temp);
            if (strpos($e->getMessage(), '404') !== false) return;
            throw $e;
        }
    }
}
