<?php

namespace Modules\Result\Jobs;

use App\SmStudentTimeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Storage;

class SendResultEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $data;
    public object $student;
    public $timelineIds = []; // Store the timeline IDs

    /**
     * The number of times the job should be tried before failing.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying a failed job.
     *
     * @var int
     */
    public $retryAfter = 60;

    /**
     * Create a new job instance.
     *
     * @param string $body
     * @param array $data
     */
    public function __construct(object $student, $data)
    {
        $this->student = $student;
        $this->data = $data;

        // Store the timeline IDs upfront
        $this->timelineIds = $data['attachments']->keys()->toArray(); // The keys are the timeline IDs
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            // Send the email with attachments
            Mail::send('result::mail', ['student' => $this->student], function (Message $message) {
                $formattedFullName = preg_replace('/\s+/', '_', $this->student->full_name);

                $message->subject($this->data['subject'])
                    ->to($this->data['reciver_email'], $this->data['receiver_name'])
                    ->from($this->data['sender_email'], $this->data['sender_name']);

                // Attach each file based on the data
                foreach ($this->data['attachments'] as $id => $filepath) {
                    $resolvedPath = $this->resolveFilePath($filepath);
                    if ($resolvedPath) {
                        $message->attach($resolvedPath, [
                            'mime' => 'application/pdf',
                            'as' => "$formattedFullName.pdf",
                        ]);
                    } else {
                        Log::warning("File not found for attachment: $filepath");
                    }
                }
            });

            SmStudentTimeline::whereIn('id', $this->timelineIds)->update(['visible_to_student' => 2]);

            $filePaths = $this->data['attachments']->toArray(); // Get the file paths
            Storage::delete($filePaths); // Delete the files from storage

            Log::info("Email successfully sent to {$this->data['reciver_email']} for student ID: {$this->data['student_id']}");
        } catch (Exception $e) {
            Log::error("Error sending email: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * The job has failed after all retries.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Ensure that even if the job fails early, the timeline visibility is updated
        if (!empty($this->timelineIds)) {
            SmStudentTimeline::whereIn('id', $this->timelineIds)->update(['visible_to_student' => 1]);
        }

        Log::error("Job failed for student ID: {$this->data['student_id']}. Error: " . $exception->getMessage());
    }

    /**
     * Resolve the full storage path for a file.
     *
     * @param string $filePath
     * @return string|null
     */
    private function resolveFilePath($filePath)
    {
        if (Storage::exists($filePath)) {
            return Storage::path($filePath);
        }

        if (file_exists($filePath)) {
            return $filePath;
        }
        
        return null;
    }
}
