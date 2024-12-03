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

class SendResultEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

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

        Mail::send('result::mail', ['student' => $this->data], function (Message $message) {
            $formattedFullName = preg_replace('/\s+/', '_', $this->data->full_name);

            $message->subject($this->data['subject'])
                ->to($this->data['reciver_email'], $this->data->receiver_name)
                ->from($this->data['sender_email'], $this->data->sender_name);
            if (empty($this->data->links)) {
                $fileContents = $this->generatePdfAttachment();
                $message->attachData($fileContents, "$formattedFullName.pdf", ['mime' => 'application/pdf']);
            }
        });

        $msg = "Email sent to {$this->data['reciver_email']}";
        $title = '<span class="text-success">Success</span> ';
        logEmail($title, $msg, $this->data['reciver_email'], $this->data->exam_id);
        Log::info($msg);
    }


    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $e)
    {
        // Log or notify about the failure
        $msg = $e->exception->getMessage();
        Log::error("Job failed with exception: " . $msg, [
            'data' => $this->data,
            'trace' => $e->getTraceAsString(),
        ]);
        $title = '<span class="text-danger">Failed</span> ';
        logEmail($title, $msg, $this->data['reciver_email'], $this->data->exam_id);
    }


    private function generatePdfAttachment(): string
    {
        $cacheKey = "result_{$this->data->student_id}_{$this->data->exam_id}";
        $cachedResult = Cache::get($cacheKey);

        if (!$cachedResult) {
            throw new \Exception('No Result Data');
        }

        $resp = generatePDF($cachedResult, $this->data->student_id, $this->data->exam_id);
        return $resp->getBody()->getContents();
    }
}
