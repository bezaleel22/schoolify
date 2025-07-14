<?php

namespace Modules\Result\Traits;

use App\SmExamSetup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

trait MarkRegisterTrait
{
    /**
     * Get exam setup data for a student and exam
     */
    public function getExamSetup($student, $exam_id)
    {
        $academic_id = getAcademicId();
        $school_id = Auth::user()->school_id;

        return SmExamSetup::where('exam_term_id', $exam_id)
            ->where('class_id', $student->class_id)
            ->where('section_id', $student->section_id)
            ->where('academic_id', $academic_id)
            ->where('school_id', $school_id)
            ->get();
    }

    /**
     * Convert CSV data to markStore format for existing store method processing
     */
    public function convertCsvToMarkStore($request, $examSetups)
    {
        try {
            $csvData = $this->parseCsvData($request->csv_data);
            if (!$csvData['success']) {
                return $csvData;
            }

            $csvFormat = $this->detectCsvFormat($csvData['headers']);
            $processedMarks = [];
            $currentIndex = 1;

            foreach ($csvData['rows'] as $rowNumber => $rowData) {
                $rowResult = $this->processDataRow($rowData, $csvFormat, $examSetups, $rowNumber);

                if ($rowResult['success']) {
                    $processedMarks[$currentIndex] = $rowResult['data'];
                    $currentIndex++;
                }
            }

            return $this->buildSuccessResponse($processedMarks, $csvFormat);
        } catch (\Exception $e) {
            return $this->buildErrorResponse($e);
        }
    }

    /**
     * Parse CSV string into structured data
     */
    private function parseCsvData($csvString)
    {
        $lines = explode("\n", trim($csvString));

        if (count($lines) < 2) {
            return [
                'success' => false,
                'message' => 'CSV data must contain header and at least one data row'
            ];
        }

        $headers = array_map('trim', array_filter(str_getcsv($lines[0])));
        $dataRows = [];

        for ($i = 1; $i < count($lines); $i++) {
            $line = trim($lines[$i]);
            if (empty($line)) continue;

            $rowData = str_getcsv($line);
            if (empty(array_filter($rowData))) continue;

            // Validate column count
            if (count($rowData) === count($headers)) {
                $dataRows[$i] = $this->createAssociativeRow($rowData, $headers);
            } else {
                Log::warning('CSV row column count mismatch', [
                    'row' => $i + 1,
                    'expected' => count($headers),
                    'actual' => count($rowData)
                ]);
            }
        }

        return [
            'success' => true,
            'headers' => $headers,
            'rows' => $dataRows
        ];
    }

    /**
     * Create associative array from row data
     */
    private function createAssociativeRow($rowData, $headers)
    {
        $associativeRow = [];
        for ($j = 0; $j < count($headers); $j++) {
            $associativeRow[$headers[$j]] = isset($rowData[$j]) ? trim($rowData[$j]) : '';
        }
        return $associativeRow;
    }

    /**
     * Detect CSV format based on headers
     */
    private function detectCsvFormat($headers)
    {
        if (!in_array('subject_id', $headers)) {
            throw new \Exception('Missing required column: subject_id');
        }

        if (in_array('MT1', $headers) && in_array('MT2', $headers) && in_array('CA', $headers)) {
            return 'GRADERS';
        }

        if (in_array('EXAM', $headers) && !in_array('MT1', $headers)) {
            return 'EYFS';
        }

        return 'GENERIC';
    }

    /**
     * Process a single data row
     */
    private function processDataRow($rowData, $csvFormat, $examSetups, $rowNumber)
    {
        $subjectId = trim($rowData['subject_id'] ?? '');

        if (empty($subjectId) || !is_numeric($subjectId)) {
            return ['success' => false];
        }

        $marksData = $this->extractMarksData($rowData, $csvFormat, $subjectId, $examSetups);

        if (empty($marksData['marks'])) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'data' => [
                'subject_id' => $subjectId,
                'marks' => $marksData['marks'],
                'exam_Sids' => array_filter($marksData['exam_setup_ids']),
                'absent_students' => [],
                'teacher_remarks' => null
            ]
        ];
    }

    /**
     * Extract marks data based on CSV format
     */
    private function extractMarksData($rowData, $csvFormat, $subjectId, $examSetups)
    {
        $marks = [];
        $examSetupIds = [];

        switch ($csvFormat) {
            case 'GRADERS':
                $examTypes = ['MT1' => 'MTA', 'MT2' => 'CA', 'CA' => 'REPORT', 'EXAM' => 'EXAM'];
                foreach ($examTypes as $key => $examType) {
                    if (isset($rowData[$key]) && is_numeric($rowData[$key])) {
                        $marks[] = (float)$rowData[$key];
                        $examSetupIds[] = $this->findExamSetupId($examSetups, $subjectId, $examType);
                    }
                }
                break;

            case 'EYFS':
                if (isset($rowData['EXAM']) && is_numeric($rowData['EXAM'])) {
                    $marks[] = (float)$rowData['EXAM'];
                    $examSetupIds[] = $this->findExamSetupId($examSetups, $subjectId, 'EXAM');
                }
                break;

            default:
                $excludeColumns = ['subject_id', 'subject_code'];
                foreach ($rowData as $column => $value) {
                    if (!in_array($column, $excludeColumns) && is_numeric($value)) {
                        $marks[] = (float)$value;
                        $examSetupIds[] = $this->findExamSetupId($examSetups, $subjectId, $column);
                    }
                }
                break;
        }

        return [
            'marks' => $marks,
            'exam_setup_ids' => $examSetupIds
        ];
    }

    /**
     * Find exam setup ID for subject and exam type
     */
    private function findExamSetupId($examSetups, $subjectId, $examType = null)
    {
        $subjectSetups = $examSetups->where('subject_id', $subjectId);

        if (!$examType) {
            return $subjectSetups->first()?->id ?? 0;
        }

        // Enhanced exam type matching
        $matchedSetup = $subjectSetups->first(function ($setup) use ($examType) {
            if (stripos($setup->exam_title, $examType) !== false) {
                return true;
            }

            return false;
        });

        return $matchedSetup ? $matchedSetup->id : 0;
    }


    /**
     * Build success response
     */
    private function buildSuccessResponse($processedMarks, $format, $studentData = null)
    {
        $message = 'CSV converted to markStore format successfully';

        Log::info('Data conversion completed', [
            'format_detected' => $format,
            'subjects_processed' => count($processedMarks),
            'data_type' => $studentData ? 'JSON' : 'CSV'
        ]);

        $response = [
            'success' => true,
            'message' => $message,
            'data' => [
                'markStore' => $processedMarks,
                'subjects_processed' => count($processedMarks),
                'format_detected' => $format
            ]
        ];

        // Add student metadata for JSON responses
        if ($studentData) {
            $response['data']['student_info'] = [
                'name' => $studentData['name'] ?? '',
                'admission_number' => $studentData['admission_number'] ?? '',
                'grade' => $studentData['grade'] ?? '',
                'term' => $studentData['term'] ?? '',
                'days_present' => $studentData['days_present'] ?? 0,
                'days_absent' => $studentData['days_absent'] ?? 0
            ];
        }

        return $response;
    }

    /**
     * Build error response
     */
    private function buildErrorResponse(\Exception $e)
    {
        Log::error('CSV conversion failed', [
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);

        return [
            'success' => false,
            'message' => 'CSV conversion failed: ' . $e->getMessage()
        ];
    }
}
