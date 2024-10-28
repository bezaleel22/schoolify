<?php

namespace Modules\Result\Http\Controllers\Api;

use App\ApiBaseMethod;
use Gotenberg\Stream;
use Gotenberg\Gotenberg;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Http;

class GenerateController extends Controller
{
    protected $token = '';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $this->validate($request, [
        //     'id' => 'required',
        // ]);

        try {
            if (!$this->token)
                $this->login();

            $result_data = $this->fetchStudentRecords();
            $view = $this->getView($result_data);

            $result = $view->render();
            $student = $result_data->student;
            $fileName = md5($student->full_name . time());

            $url = env('GOTENBERG_URL', null);
            $req = Gotenberg::chromium($url)
                ->pdf()
                ->skipNetworkIdleEvent()
                ->preferCssPageSize()
                ->outputFilename($fileName)
                ->margins('2mm', '2mm', '2mm', '2mm')
                ->html(Stream::string('index.html', $result));
            $filename = Gotenberg::save($req, 'public/uploads/student/timeline/');

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse([
                    'student_id' => $result_data->student->id,
                    'exam_type_id' => $result_data->student->exam_type_id,
                    'result_file' => 'public/uploads/student/timeline/' . $filename,
                ], null);
            }
        } catch (\Exception $e) {
            ApiBaseMethod::sendResponse(null, $e->getMessage());
        }
    }

    public function getView($result)
    {
        $school = $result->school;
        $student = $result->student;
        $records = $result->records;
        $score = $result->score;
        $ratings = $result->ratings;
        $remark = $result->remark;

        return  view('template.result', compact('student', 'school', 'ratings', 'records', 'score', 'remark'));
    }

    protected function login()
    {
        $url = env('LOCAL_BASE_URL', null);
        $url = $url . '/api/auth/login';
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

    protected function fetchStudentRecords()
    {
        $url = env('LOCAL_BASE_URL', null);
        $url = $url . '/api/marks-grade?id=339&exam_id=5';
        $response = Http::withHeaders([
            'Authorization' => $this->token, // Replace $token with your actual token
        ])->get($url);

        if ($response->successful()) {
            return $response->object()->data;
        } else {
            return null;
        }
    }
}
