<?php

namespace Modules\Result\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\Result\Traits\GmailTrait;
use Modules\Result\Traits\ResultTrait;
use Modules\Result\Jobs\SendResultEmail;
use Throwable;

class SendGmailResultEmail implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels, GmailTrait, ResultTrait;

    public object $data;
    public $tries = 3;
    public $timeout = 300;

    /**
     * Create a new job instance.
     *
     * @param object $data
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
            // Apply email configuration
            $this->data = emailConfig($this->data);
            
            // Check if Gmail is configured and available
            if ($this->isGmailConfigured()) {
                $result = $this->sendGmailEmail($this->data);
                
                if ($result['success']) {
                    $stu_exam = "{$this->data->student_id}-{$this->data->exam_id}";
                    $msg = "Gmail email for {$this->data->full_name} sent successfully to {$this->data->reciver_email}. Message ID: {$result['message_id']}";
                    
                    Log::info($msg);
                    return;
                } else {
                    // Gmail failed, fallback to Laravel Mail
                    Log::warning("Gmail send failed, falling back to Laravel Mail: " . $result['error']);
                    $this->fallbackToLaravelMail();
                }
            } else {
                // Gmail not configured, use Laravel Mail
                Log::info("Gmail not configured, using Laravel Mail");
                $this->fallbackToLaravelMail();
            }
            
        } catch (\Exception $e) {
            Log::error("Gmail job failed: " . $e->getMessage());
            
            // Try fallback to Laravel Mail on any error
            try {
                $this->fallbackToLaravelMail();
            } catch (\Exception $fallbackError) {
                Log::error("Both Gmail and Laravel Mail failed: " . $fallbackError->getMessage());
                throw $e; // Re-throw original exception
            }
        }
    }

    /**
     * Fallback to original Laravel Mail implementation
     */
    protected function fallbackToLaravelMail()
    {
        // Dispatch the original email job as fallback
        $originalJob = new SendResultEmail($this->data);
        $originalJob->handle();
        
        Log::info("Fallback email sent successfully via Laravel Mail for {$this->data->full_name}");
    }

    /**
     * Handle a job failure.
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(Throwable $e)
    {
        $stu_exam = "{$this->data->student_id}-{$this->data->exam_id}";
        $msg = "Gmail job failed completely: " . $e->getMessage();
        
        Log::error($msg, [
            'data' => $this->data,
            'trace' => $e->getTraceAsString(),
        ]);
        
        logEmail('Gmail-Failed', $msg, $this->data->reciver_email, $stu_exam);
    }
}