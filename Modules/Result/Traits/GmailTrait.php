<?php

namespace Modules\Result\Traits;

use Google\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

trait GmailTrait
{
    protected $client;

    /**
     * Initialize Gmail client with OAuth2 authentication
     */
    public function initializeGmailClient()
    {
        try {
            $this->client = new Client();
            $this->client->setApplicationName(config('app.name', 'Laravel Gmail Integration'));
            $this->client->setScopes([
                'https://www.googleapis.com/auth/gmail.send',
                'https://www.googleapis.com/auth/gmail.readonly',
                'https://www.googleapis.com/auth/gmail.modify'
            ]);
            $this->client->setAuthConfig([
                'client_id' => env('GMAIL_CLIENT_ID'),
                'client_secret' => env('GMAIL_CLIENT_SECRET'),
                'redirect_uris' => [env('APP_URL') . '/gmail/callback']
            ]);
            $this->client->setAccessType('offline');
            $this->client->setPrompt('consent');

            // Load access token
            $this->loadAccessToken();
            
            return true;
        } catch (\Exception $e) {
            Log::error('Gmail client initialization failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Load and refresh access token
     */
    protected function loadAccessToken()
    {
        $tokenPath = storage_path('app/gmail_token.json');
        
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $this->client->setAccessToken($accessToken);

            // Refresh token if expired
            if ($this->client->isAccessTokenExpired()) {
                if ($this->client->getRefreshToken()) {
                    $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());
                    $newToken = $this->client->getAccessToken();
                    file_put_contents($tokenPath, json_encode($newToken));
                    Log::info('Gmail access token refreshed');
                } else {
                    throw new \Exception('Gmail refresh token not available. Re-authorization required.');
                }
            }
        } else {
            throw new \Exception('Gmail access token not found. Authorization required.');
        }
    }

    /**
     * Get Gmail authorization URL
     */
    public function getGmailAuthUrl()
    {
        if (!$this->client) {
            $this->initializeGmailClient();
        }
        
        return $this->client->createAuthUrl();
    }

    /**
     * Handle OAuth callback and store tokens
     */
    public function handleGmailCallback($authCode)
    {
        try {
            if (!$this->client) {
                $this->initializeGmailClient();
            }

            $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);
            
            if (array_key_exists('error', $accessToken)) {
                throw new \Exception('Gmail OAuth error: ' . $accessToken['error']);
            }

            // Store token
            $tokenPath = storage_path('app/gmail_token.json');
            file_put_contents($tokenPath, json_encode($accessToken));
            
            Log::info('Gmail authorization successful');
            return true;
        } catch (\Exception $e) {
            Log::error('Gmail OAuth callback failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email via Gmail API with custom FROM address
     */
    public function sendGmailEmail($data)
    {
        try {
            if (!$this->initializeGmailClient()) {
                throw new \Exception('Failed to initialize Gmail client');
            }

            // Prepare email content
            $subject = $data->subject;
            $to = $data->reciver_email;
            $fromEmail = $data->sender_email ?? 'reports@llacademy.ng';
            $fromName = $data->sender_name ?? 'Lighthouse Leading Academy';
            
            // Create message
            $messageBody = $this->createEmailMessage($data, $to, $fromEmail, $fromName, $subject);
            
            // Send email using HTTP request
            $accessToken = $this->client->getAccessToken()['access_token'];
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $messageBody
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                $messageId = $responseData['id'];
                $threadId = $responseData['threadId'];
                
                // Log success with thread ID
                $this->logGmailDelivery($data, $messageId, 'sent', null, $threadId);
                
                Log::info("Gmail email sent successfully. Message ID: {$messageId}, Thread ID: {$threadId}");
                
                return [
                    'success' => true,
                    'message_id' => $messageId,
                    'thread_id' => $threadId,
                    'status' => 'sent'
                ];
            } else {
                throw new \Exception('Gmail API request failed: ' . $response->body());
            }
            
        } catch (\Exception $e) {
            Log::error('Gmail send failed: ' . $e->getMessage());
            
            $this->logGmailDelivery($data, null, 'failed', $e->getMessage());
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Create RFC 2822 compliant email message
     */
    protected function createEmailMessage($data, $to, $fromEmail, $fromName, $subject)
    {
        // Generate email content from template
        $htmlContent = view('result::mail', ['student' => $data])->render();
        
        // Create message headers
        $headers = [
            "From: {$fromName} <{$fromEmail}>",
            "To: {$to}",
            "Subject: {$subject}",
            "MIME-Version: 1.0",
            "Content-Type: multipart/mixed; boundary=\"boundary123\""
        ];
        
        // Create message body
        $messageBody = implode("\r\n", $headers) . "\r\n\r\n";
        
        // Add HTML content
        $messageBody .= "--boundary123\r\n";
        $messageBody .= "Content-Type: text/html; charset=UTF-8\r\n";
        $messageBody .= "Content-Transfer-Encoding: quoted-printable\r\n\r\n";
        $messageBody .= quoted_printable_encode($htmlContent) . "\r\n\r\n";
        
        // Add PDF attachment if needed
        if (empty($data->links)) {
            $pdfContent = $this->generatePdfAttachment($data);
            $fileName = str_replace(' ', '_', $data->full_name) . '.pdf';
            
            $messageBody .= "--boundary123\r\n";
            $messageBody .= "Content-Type: application/pdf; name=\"{$fileName}\"\r\n";
            $messageBody .= "Content-Disposition: attachment; filename=\"{$fileName}\"\r\n";
            $messageBody .= "Content-Transfer-Encoding: base64\r\n\r\n";
            $messageBody .= chunk_split(base64_encode($pdfContent)) . "\r\n";
        }
        
        $messageBody .= "--boundary123--";
        
        return $this->base64url_encode($messageBody);
    }

    /**
     * Generate PDF attachment (reuse existing logic)
     */
    protected function generatePdfAttachment($data)
    {
        $fileName = md5("{$data->student_id}_{$data->exam_id}");
        $filePath = "result/$fileName.pdf";

        if (file_exists(storage_path("app/$filePath"))) {
            return file_get_contents(storage_path("app/$filePath"));
        }

        $cacheKey = "result_{$data->student_id}_{$data->exam_id}";
        $cachedResult = Cache::get($cacheKey);
        $result_data = $cachedResult ?? $this->getResultData($data->student_id, $data->exam_id);

        $resp = generatePDF($result_data, $data->student_id, $data->exam_id);
        return $resp->getBody()->getContents();
    }

    /**
     * Log Gmail delivery status
     */
    protected function logGmailDelivery($data, $messageId, $status, $error = null, $threadId = null)
    {
        try {
            $stu_exam = "{$data->student_id}-{$data->exam_id}";
            $message = $status === 'sent'
                ? "Gmail email sent successfully to {$data->reciver_email}. Message ID: {$messageId}, Thread ID: {$threadId}"
                : "Gmail email failed: {$error}";
            
            // Use the enhanced logEmail function with Gmail-specific parameters
            logEmail(
                $status === 'sent' ? 'Gmail-Success' : 'Gmail-Failed',
                $message,
                $data->reciver_email,
                $stu_exam,
                $messageId, // Gmail message ID
                $status === 'sent' ? 'sent' : 'failed', // Delivery status
                $threadId // Gmail thread ID
            );
        } catch (\Exception $e) {
            Log::error('Failed to log Gmail delivery: ' . $e->getMessage());
        }
    }

    /**
     * Check if Gmail is properly configured
     */
    public function isGmailConfigured()
    {
        return env('GMAIL_CLIENT_ID') && 
               env('GMAIL_CLIENT_SECRET') && 
               file_exists(storage_path('app/gmail_token.json'));
    }

    /**
     * Base64 URL encode (Gmail API requirement)
     */
    protected function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}