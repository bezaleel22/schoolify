<?php

namespace Modules\Result\Http\Controllers;

use App\SmExam;
use App\SmStudent;
use App\YearCheck;
use App\SmMarkStore;
use App\SmMarksGrade;
use App\SmResultStore;
use Illuminate\Http\Request;
use App\Models\StudentRecord;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\SmSubject;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Modules\Result\Traits\MarkRegisterTrait;
use Modules\Result\Traits\ImageUploadTrait;

class MarkRegisterController extends Controller
{
    use MarkRegisterTrait, ImageUploadTrait;

    /**
     * Store exam marks for a student across multiple subjects (API endpoint)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Add logging for debugging upload type
        Log::info('MarkRegisterController::store - Request received', [
            'upload_type' => $request->input('upload_type'),
            'has_csv_data' => !empty($request->input('csv_data')),
            'has_image_file' => $request->hasFile('marks_image'),
            'student_id' => $request->input('student_id'),
            'exam_id' => $request->input('exam_id')
        ]);

        // Fetch student record to get actual class/section relationship
        $student_record = StudentRecord::where('student_id', $request->student_id)
            ->where('academic_id', getAcademicId())
            ->where('school_id', Auth::user()->school_id)
            ->first();

        if (!$student_record) {
            Log::error('Student record not found', ['student_id' => $request->student_id]);
            Toastr::error('Student record not found', 'Failed');
            return redirect()->back();
        }

        try {
            // Process upload input using trait method
            $csvData = $this->processUploadInput($request);
            Log::info('Upload processing completed', ['csv_length' => strlen($csvData)]);
            // dd($csvData);
            // Create a modified request with the processed CSV data
            $modifiedRequest = clone $request;
            $modifiedRequest->merge(['csv_data' => $csvData]);

            // Convert CSV to markStore format using trait
            $exam_setup = $this->getExamSetup($student_record, $request->exam_id);
            $csv_result = $this->convertCsvToMarkStore($modifiedRequest, $exam_setup);
            if (!$csv_result['success']) {
                Toastr::error('CSV conversion failed: ' . $csv_result['message'], 'Error');
                return redirect()->back();
            }
            $markStore = $csv_result['data']['markStore'];
        } catch (\Exception $e) {
            Log::error('Upload processing failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            Toastr::error('Upload processing failed: ' . $e->getMessage(), 'Error');
            return redirect()->back();
        }

        $student_id = $student_record->student_id;
        $class_id = $student_record->class_id;
        $section_id = $student_record->section_id;

        Log::info('MarkStore processed successfully', [
            'markStore_count' => count($markStore),
            'class_id' => $class_id,
            'section_id' => $section_id
        ]);
        // dd($csvData);
        DB::beginTransaction();
        try {
            $results = [];
            // Process each subject
            foreach ($markStore as $record_id => $record) {
                $subject_id = gv($record, 'subject_id');
                $marks = gv($record, 'marks', []);
                $exam_setup_ids = gv($record, 'exam_Sids', []);
                $absent_students = gv($record, 'absent_students', []);
                $teacher_remarks = gv($record, 'teacher_remarks');
                $is_absent = in_array($record_id, $absent_students);

                $exam_id = $request->exam_id;
                $total_marks_persubject = 0;

                if (!empty($marks)) {
                    // Process each mark part
                    foreach ($marks as $index => $part_mark) {
                        $mark_by_exam_part = ($part_mark == null) ? 0 : $part_mark;
                        $total_marks_persubject += $mark_by_exam_part;
                        $exam_setup_id = $exam_setup_ids[$index] ?? null;
                        $is_absent = $mark_by_exam_part == 0 ? true : false;

                        if (!$exam_setup_id) {
                            continue;
                        }

                        // Check if mark record already exists
                        $previous_record = SmMarkStore::where([
                            ['class_id', $class_id],
                            ['section_id', $section_id],
                            ['subject_id', $subject_id],
                            ['exam_term_id', $exam_id],
                            ['student_record_id', $student_record->id],
                            ['exam_setup_id', $exam_setup_id],
                            ['student_id', $student_id]
                        ])
                            ->where('academic_id', getAcademicId())
                            ->where('school_id', Auth::user()->school_id)
                            ->first();

                        if (!$previous_record) {
                            // Create new mark record
                            $marks_register = new SmMarkStore();
                            $marks_register->exam_term_id = $exam_id;
                            $marks_register->class_id = $class_id;
                            $marks_register->section_id = $section_id;
                            $marks_register->subject_id = $subject_id;
                            $marks_register->student_id = $student_id;
                            $marks_register->student_record_id = $student_record->id;
                            $marks_register->total_marks = $mark_by_exam_part;
                            $marks_register->exam_setup_id = $exam_setup_id;
                            $marks_register->is_absent = $is_absent ? 1 : 0;
                            $marks_register->teacher_remarks = $teacher_remarks;
                            $marks_register->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                            $marks_register->school_id = Auth::user()->school_id;
                            $marks_register->academic_id = getAcademicId();
                            $marks_register->save();
                        } else {
                            // Update existing mark record
                            $marks_register = SmMarkStore::find($previous_record->id);
                            $marks_register->total_marks = $mark_by_exam_part;
                            $marks_register->is_absent = $is_absent ? 1 : 0;
                            $marks_register->teacher_remarks = $teacher_remarks;
                            $marks_register->save();
                        }
                    }

                    // Calculate subject percentage and grade
                    $subject_full_mark = subjectFullMark($exam_id, $subject_id, $class_id, $section_id);
                    $student_obtained_mark = $total_marks_persubject;
                    $mark_by_percentage = subjectPercentageMark($student_obtained_mark, $subject_full_mark);

                    $mark_grade = SmMarksGrade::where([
                        ['percent_from', '<=', $mark_by_percentage],
                        ['percent_upto', '>=', $mark_by_percentage]
                    ])
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->first();

                    // Handle result store
                    $previous_result_record = SmResultStore::where([
                        ['class_id', $class_id],
                        ['section_id', $section_id],
                        ['subject_id', $subject_id],
                        ['exam_type_id', $exam_id],
                        ['student_record_id', $student_record->id],
                        ['student_id', $student_id]
                    ])
                        ->where('academic_id', getAcademicId())
                        ->where('school_id', Auth::user()->school_id)
                        ->first();

                    if (!$previous_result_record) {
                        // Create new result record
                        $result_record = new SmResultStore();
                        $result_record->class_id = $class_id;
                        $result_record->section_id = $section_id;
                        $result_record->subject_id = $subject_id;
                        $result_record->exam_type_id = $exam_id;
                        $result_record->student_id = $student_id;
                        $result_record->student_record_id = $student_record->id;
                        $result_record->is_absent = $is_absent ? 1 : 0;
                        $result_record->total_marks = $total_marks_persubject;
                        $result_record->total_gpa_point = @$mark_grade->gpa;
                        $result_record->total_gpa_grade = @$mark_grade->grade_name;
                        $result_record->teacher_remarks = $teacher_remarks;
                        $result_record->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $result_record->school_id = Auth::user()->school_id;
                        $result_record->academic_id = getAcademicId();
                        $result_record->save();
                    } else {
                        // Update existing result record
                        $result_record = SmResultStore::find($previous_result_record->id);
                        $result_record->total_marks = $total_marks_persubject;
                        $result_record->total_gpa_point = @$mark_grade->gpa;
                        $result_record->total_gpa_grade = @$mark_grade->grade_name;
                        $result_record->created_at = YearCheck::getYear() . '-' . date('m-d h:i:s');
                        $result_record->is_absent = $is_absent ? 1 : 0;
                        $result_record->teacher_remarks = $teacher_remarks;
                        $result_record->save();
                    }

                    // Add to results array
                    $results[] = [
                        'subject_id' => $subject_id,
                        'exam_id' => $exam_id,
                        'total_marks' => $total_marks_persubject,
                        'grade' => @$mark_grade->grade_name ?? null,
                        'gpa' => @$mark_grade->gpa ?? null,
                        'is_absent' => $is_absent
                    ];
                }
            }

            DB::commit();

            Log::info('Score book processing completed successfully', [
                'results_count' => count($results),
                'student_id' => $student_id,
                'exam_id' => $request->exam_id
            ]);

            Toastr::success('Score book processed successfully', 'Success');
            return redirect()->back()->with(['studentExam' => 'active']);
        } catch (\Exception $e) {
            DB::rollback();
            dd($e->getTraceAsString());
            Log::error('MarkRegisterController::store - Operation failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            Toastr::error('Processing failed: ' . $e->getMessage(), 'Failed');
            return redirect()->back();
        }
    }

    /**
     * Show the CSV upload modal form
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function showScoreBookModal(Request $request)
    {
        try {
            $student = $request->input('student', []);

            // Get student details for hidden fields
            $student_id = $student['id'] ?? null;
            $exam_id = $request->input('exam_id', 1); // Default exam_id

            $student_record = StudentRecord::where('student_id', $student_id)
                ->where('academic_id', getAcademicId())
                ->where('school_id', Auth::user()->school_id)
                ->first();

            $class_id = $student_record->class_id;
            $section_id = $student_record->section_id;

            $content = view('result::partials.score_book_modal', compact(
                'student_id',
                'exam_id',
                'class_id',
                'section_id'
            ))->render();
            
            return response()->json([
                'success' => true,
                'title' => 'Add Score Book',
                'content' => $content,
                'url' => route('score.book.store'),
                'preview' => false
            ]);
        } catch (\Exception $e) {
            Log::error('MarkRegisterController::showScoreBookModal - Failed to show modal', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load score book modal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check OpenRouter API key limits and usage
     *
     * Example request:
     * GET /openrouter/limits
     *
     * Example response:
     * {
     *   "success": true,
     *   "data": {
     *     "label": "My API Key",
     *     "usage": 150,
     *     "limit": 1000,
     *     "is_free_tier": false
     *   },
     *   "message": "API key information retrieved successfully"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkApiLimits()
    {
        try {
            // For API routes, use environment variable directly. For web routes, use user-specific key.
            if (!Auth::check()) {
                // Not authenticated - use environment variable (for API access)
                $apiKey = config('services.openrouter.api_key') ?? env('OPENROUTER_API_KEY');
                if (!$apiKey) {
                    return response()->json([
                        'success' => false,
                        'message' => 'OpenRouter API key is not configured in environment variables.'
                    ], 400);
                }
            } else {
                // Authenticated - use user-specific key or fallback to environment
                $apiKey = $this->getOpenRouterApiKey();
            }

            // Make request to OpenRouter API to check key info
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(30)->get('https://openrouter.ai/api/v1/auth/key');

            if (!$response->successful()) {
                Log::error('OpenRouter API key check failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Failed to check API key: HTTP ' . $response->status()
                ], $response->status());
            }

            $keyData = $response->json();

            Log::info('OpenRouter API key check successful', [
                'usage' => $keyData['data']['usage'] ?? 'unknown',
                'limit' => $keyData['data']['limit'] ?? 'unknown',
                'is_free_tier' => $keyData['data']['is_free_tier'] ?? 'unknown'
            ]);

            return response()->json([
                'success' => true,
                'data' => $keyData['data'] ?? null,
                'message' => 'API key information retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('OpenRouter API key check failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check API limits: ' . $e->getMessage()
            ], 500);
        }
    }
}
