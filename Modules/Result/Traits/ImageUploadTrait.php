<?php

namespace Modules\Result\Traits;

use App\SmStaff;
use App\SmSubject;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Client\RequestException;

trait ImageUploadTrait
{
    /**
     * Process upload input - either CSV string or image file
     * 
     * @param \Illuminate\Http\Request $request
     * @return string CSV data
     * @throws \Exception
     */
    protected function processUploadInput($request)
    {
        $uploadType = $request->input('upload_type', 'image');

        if ($uploadType === 'csv') {
            $csvData = $request->input('csv_data');
            if (empty($csvData)) {
                throw new \Exception('CSV data is required when upload type is CSV.');
            }

            return trim($csvData);
        } elseif ($uploadType === 'image') {
            if (!$request->hasFile('marks_image')) {
                throw new \Exception('Image file is required when upload type is Image.');
            }

            $forceReextraction = $request->input('force_reextraction', false);
            $examId = $request->input('exam_id');
            $studentId = $request->input('student_id');

            return $this->extractCsvFromImage($request->file('marks_image'), $forceReextraction, $examId, $studentId);
        }

        throw new \Exception('Invalid upload type specified.');
    }

    /**
     * Generate dynamic subject mapping from SmSubject model
     *
     * @return string
     */
    private function generateSubjectMapping()
    {
        try {
            $subjects = SmSubject::where('active_status', 1)
                ->where('school_id', Auth::user()->school_id)
                ->where('academic_id', getAcademicId())
                ->select('id', 'subject_name', 'subject_code')
                ->get();

            $subjectMapping = [];
            foreach ($subjects as $subject) {
                $subjectCode = $subject->subject_code ?: strtoupper(substr($subject->subject_name, 0, 3));
                $subjectMapping[] = "{$subject->id},{$subject->subject_name},{$subjectCode}";
            }

            return implode("  \n", $subjectMapping);
        } catch (\Exception $e) {
            // Fallback to a basic mapping if database query fails
            throw $e;
        }
    }

    /**
     * Extract CSV data from image using OpenRouter AI
     *
     * @param \Illuminate\Http\UploadedFile $imageFile
     * @return string CSV data
     * @throws \Exception
     */
    protected function extractCsvFromImage(UploadedFile $imageFile, $forceReextraction = false, $examId = null, $studentId = null)
    {
        try {
            // Validate image file
            if (!in_array($imageFile->getMimeType(), ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'])) {
                throw new \Exception('Invalid image format. Only JPEG, PNG, and GIF are supported.');
            }

            // Generate cache key using exam_id and student_id hash for better caching
            $subjectMapping = $this->generateSubjectMapping();
            $examStudentHash = md5($examId . '_' . $studentId);
            $cacheKey = "csv_extraction_{$examStudentHash}_" . md5($subjectMapping);

            // If force re-extraction is requested, forget the cache first
            if ($forceReextraction) {
                Cache::forget($cacheKey);
            }

            // Use Cache::remember pattern with 24-hour expiry
            return Cache::remember($cacheKey, now()->addDay(), function () use ($imageFile) {
                $imageContent = file_get_contents($imageFile->getPathname());
                return $this->performAiExtraction($imageContent, $imageFile->getMimeType());
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Perform the actual AI extraction
     *
     * @param string $imageContent
     * @param string $mimeType
     * @return string
     * @throws \Exception
     */
    private function performAiExtraction($imageContent, $mimeType)
    {
        try {
            // Convert image to base64
            $imageData = base64_encode($imageContent);
            $imageUrl = "data:{$mimeType};base64,{$imageData}";

            // Get OpenRouter API key using helper method
            $apiKey = $this->getOpenRouterApiKey();

            // Generate dynamic subject mapping
            $subjectMapping = $this->generateSubjectMapping();

            // Build the AI prompt
            $promptText = "Extract all visible structured data from this student report card image. Return only the CSV content and nothing else—no explanations, descriptions, or additional text before or after the CSV.

Determine the headers based on the presence of the word **\"Areas\"** in the image:

- If the image **contains the word \"Areas\"**, use the following CSV headers: `subject_id`, `subject_code`, `CA`, ``ORAL`,`, `PSYCHOMOTTOR`, `HOMEWORK`, `EXAM`.
- If the image **does not contains the word \"Areas\"**, use the following CSV headers: `subject_id`, `subject_code`, `MT1`, `MT2`, `CA`, `EXAM`.

Ignore all metadata or student information such as name, admission number, class, attendance, and term. Also ignore `TOTAL` and `GRADE`.

Match subjects using this subject map and use only the `subject_code` in the output:

{$subjectMapping}

Only include subjects that exist in the map.
Convert any fractional scores (like 29½) to decimals (e.g., 29.5).
Return clean, valid CSV format only. No surrounding text, markdown, or commentary.";

            // Send request to OpenRouter API with optimized parameters
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ])->timeout(60)->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'qwen/qwen2.5-vl-72b-instruct:free',
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => $promptText
                            ],
                            [
                                'type' => 'image_url',
                                'image_url' => [
                                    'url' => $imageUrl
                                ]
                            ]
                        ]
                    ]
                ],
                'stream' => false,     // Disable streaming
                // 'temperature' => 0.1,  // Low temperature for more predictable responses
                // 'max_tokens' => 2000,  // Limit response length
                // 'top_p' => 0.9,        // Focus on most likely tokens
                // 'frequency_penalty' => 0.3,  // Reduce repetition
                // 'presence_penalty' => 0.2,   // Encourage staying on topic
                // 'stop' => ['```', 'Note:', 'Example:', 'Here', 'The']  // Stop on explanatory words
            ]);

            if (!$response->successful()) {
                Log::error('OpenRouter API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to process image with AI: HTTP ' . $response->status());
            }

            $responseData = $response->json();
            if (!isset($responseData['choices'][0]['message']['content'])) {
                throw new \Exception('Invalid response format from OpenRouter API');
            }

            $csvContent = trim($responseData['choices'][0]['message']['content']);

            // Clean up the response to extract only CSV data
            $csvContent = $this->cleanCsvResponse($csvContent);
            if (empty($csvContent)) {
                throw new \Exception('No valid CSV data could be extracted from the image');
            }
// dd($csvContent);
            return $csvContent;
        } catch (RequestException $e) {
            throw new \Exception('Network error while processing image: ' . $e->getMessage());
        }
    }

    /**
     * Clean and validate the CSV response from AI
     *
     * @param string $response
     * @return string
     */
    private function cleanCsvResponse($response)
    {

        // Remove any markdown formatting
        $response = preg_replace('/```csv\s*\n?/', '', $response);
        $response = preg_replace('/```\s*$/', '', $response);

        // Remove any explanatory text before/after CSV
        $lines = explode("\n", $response);
        $csvLines = [];
        $inCsv = false;

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip empty lines
            if (empty($line)) continue;

            // Look for CSV header patterns (subject_id,subject_code...)
            if (preg_match('/^subject_id\s*,\s*subject_code/i', $line)) {
                $inCsv = true;
                $csvLines[] = $line;
                continue;
            }

            // If we've found the header, continue collecting CSV data
            if ($inCsv) {
                // Stop if we hit explanatory text or non-CSV content
                if (preg_match('/^(note|example|format|explanation|here|the|this)/i', $line)) {
                    break;
                }

                // Check if line looks like CSV data (starts with number, contains commas)
                if (preg_match('/^\d+\s*,/', $line) && strpos($line, ',') !== false) {
                    // Convert fractional scores to decimals (e.g., 29½ to 29.5)
                    $line = preg_replace('/(\d+)½/', '$1.5', $line);
                    $line = preg_replace('/(\d+)¼/', '$1.25', $line);
                    $line = preg_replace('/(\d+)¾/', '$1.75', $line);

                    $csvLines[] = $line;
                }
            }
        }

        $cleanedCsv = implode("\n", $csvLines);
        return $cleanedCsv;
    }

    /**
     * Get OpenRouter API key from SmStaff experience field or environment
     *
     * @return string
     * @throws \Exception
     */
    protected function getOpenRouterApiKey()
    {
        // Get OpenRouter API key from SmStaff experience field
        $apiKey = SmStaff::where('user_id', Auth::user()->id)
            ->where('school_id', Auth::user()->school_id)
            ->select('experience')
            ->value('experience');

        if (!$apiKey) {
            // Fallback to environment variable
            $apiKey = config('services.openrouter.api_key') ?? env('OPENROUTER_API_KEY');
            if (!$apiKey) {
                throw new \Exception('OpenRouter API key is not configured. Please set the API key in your staff experience field or OPENROUTER_API_KEY in your .env file.');
            }
        }

        return $apiKey;
    }
}
