<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class ElasticEmailService
{
    protected $apiKey='22CD6B00F206AF0912597F55AA8D7CE608DD7E899F88B9B18E2EE59CACC114ED0EFD3AF8DA80F8D1583F79A71252BB2D';
    protected $fromEmail='info@digitaledu.in';
    protected $fromName='DigitalEdu';
    protected $apiUrl = 'https://api.elasticemail.com/v4/emails';

    public function __construct()
    {
        $this->apiKey = config('services.elasticemail.api_key');
        $this->fromEmail = config('services.elasticemail.from_email');
        $this->fromName = config('services.elasticemail.from_name');

        // Log configuration
        Log::info('ElasticEmail Service Initialized', [
            'from_email' => $this->fromEmail,
            'from_name' => $this->fromName,
            'api_key_length' => strlen($this->apiKey),
            'api_key_first_chars' => substr($this->apiKey, 0, 10) . '...'
        ]);

        // Validate configuration
        if (empty($this->apiKey)) {
            Log::error('ElasticEmail API key is not configured');
        }
        if (empty($this->fromEmail)) {
            Log::error('ElasticEmail from email is not configured');
        }
    }

    /**
     * Send a transactional email
     *
     * @param string $toEmail
     * @param string $subject
     * @param string $htmlBody
     * @param array $attachments
     * @return bool
     */
    public function sendEmail(string $toEmail, string $subject, string $htmlBody, array $attachments = []): bool
    {
        try {
            Log::info('Attempting to send email', [
                'to' => $toEmail,
                'subject' => $subject,
                'from_email' => $this->fromEmail,
                'from_name' => $this->fromName  
            ]);

            if (empty($this->apiKey)) {
                Log::error('Cannot send email: API key is empty');
                return false;
            }

            $emailData = [
                'Recipients' => [
                    [
                        'Email' => $toEmail
                    ]
                ],
                'Content' => [
                    'From' => $this->fromEmail,
                    'Subject' => $subject,
                    'Attachments' => $attachments,
                    'Body' => [
                        [
                            'ContentType' => 'HTML',
                            'Content' => $htmlBody,
                            'Charset' => 'utf-8'
                        ]
                    ]
                ]
            ];

            Log::info('Prepared email data', [
                'to' => $toEmail,
                'from' => $this->fromEmail,
                'subject' => $subject,
                'request_data' => json_encode($emailData)
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-ElasticEmail-ApiKey: ' . $this->apiKey
            ]);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);

            Log::info('Sending request to ElasticEmail API', [
                'url' => $this->apiUrl,
                'headers' => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                    'X-ElasticEmail-ApiKey: ' . substr($this->apiKey, 0, 10) . '...'
                ]
            ]);

            $response = curl_exec(handle: $ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if (curl_errno($ch)) {
                Log::error('Curl error', [
                    'error' => curl_error($ch),
                    'errno' => curl_errno($ch)
                ]);
            }
            
            curl_close($ch);

            Log::info('Received response from ElasticEmail', [
                'http_code' => $httpCode,
                'response' => $response
            ]);

            if ($response && is_string($response)) {
                $responseData = json_decode($response, true);
                
                if (is_array($responseData)) {
                    if (isset($responseData['TransactionID']) && !empty($responseData['TransactionID'])) {
                        Log::info('Email sent successfully', [
                            'to' => $toEmail,
                            'subject' => $subject,
                            'transaction_id' => $responseData['TransactionID']
                        ]);
                        return true;
                    } else {
                        Log::error('Invalid response from ElasticEmail', [
                            'response' => $responseData
                        ]);
                    }
                } else {
                    Log::error('Failed to decode response', [
                        'response' => $response
                    ]);
                }
            }

            Log::error('Failed to send email', [
                'to' => $toEmail,
                'subject' => $subject,
                'http_code' => $httpCode,
                'response' => $response
            ]);

            return false;

        } catch (\Exception $e) {
            Log::error('Exception while sending email', [
                'to' => $toEmail,
                'subject' => $subject,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    /**
     * Send an OTP email
     *
     * @param string $toEmail
     * @param string $otp
     * @return bool
     */
    public function sendOtpEmail(string $toEmail, string $otp): bool
    {
        $subject = 'Your OTP Code';
        $htmlBody = $this->getOtpEmailTemplate($otp);

        return $this->sendEmail($toEmail, $subject, $htmlBody);
    }

    /**
     * Get the OTP email template
     *
     * @param string $otp
     * @return string
     */
    protected function getOtpEmailTemplate(string $otp): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .otp-code { font-size: 24px; font-weight: bold; color: #007bff; text-align: center; padding: 20px; }
                .footer { margin-top: 30px; font-size: 12px; color: #666; text-align: center; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>Your OTP Code</h2>
                <p>Please use the following OTP code to verify your account:</p>
                <div class="otp-code">{$otp}</div>
                <p>This OTP will expire in 5 minutes.</p>
                <div class="footer">
                    <p>If you didn't request this OTP, please ignore this email.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
} 