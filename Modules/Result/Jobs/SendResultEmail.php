<?php

namespace Modules\Result\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Modules\Result\Traits\ResultTrait;
use Throwable;

class SendResultEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, ResultTrait;
    public object $data;

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
    public function __construct(object $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->data = emailConfig($this->data);
            Mail::send('result::mail', ['student' => $this->data], function (Message $message) {
                $formattedFullName = str_replace(' ', '_', $this->data->full_name);

                $message->subject($this->data->subject)
                    ->to($this->data->reciver_email, $this->data->receiver_name)
                    ->from($this->data->sender_email, $this->data->sender_name);
                if (empty($this->data->links)) {
                    $fileContents = $this->generatePdfAttachment();
                    $message->attachData($fileContents, "$formattedFullName.pdf", ['mime' => 'application/pdf']);
                }
            });

            $msg = "Email sent to {$this->data->reciver_email} successfully";
            logEmail('Success', $msg, $this->data->reciver_email);
            Log::info($msg);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $e)
    {
        $msg = $e->getMessage();
        Log::error("Job failed with exception: " . $msg, [
            'data' => $this->data,
            'trace' => $e->getMessage(),
        ]);
        logEmail('Failed', $msg, $this->data->reciver_email);
    }

    private function generatePdfAttachment(): string
    {
        $cacheKey = "result_{$this->data->student_id}_{$this->data->exam_id}";
        $cachedResult = Cache::get($cacheKey);
        $result_data =  $cachedResult
            ?? $this->getResultData($this->data->student_id, $this->data->exam_id);


        if (!$cachedResult) {
            throw new \Exception('No cache available for Result Data');
        }

        $resp = generatePDF($result_data, $this->data->student_id, $this->data->exam_id);
        return $resp->getBody()->getContents();
    }

    private function getPdfAttachment(): string
    {
        $fileName = md5("{$this->data->student_id}-{$this->data->exam_id}");
        $filePath = "result/$fileName.pdf";

        if (!Storage::exists($filePath)) {
            throw new \Exception("No cached Result PDF found for: result/$fileName.pdf");
        }

        $fileContents = Storage::get($filePath);
        Storage::delete($filePath);

        return $fileContents;
    }
}
