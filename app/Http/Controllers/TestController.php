<?php

namespace App\Http\Controllers;

use App\Services\ElasticEmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    protected $elasticEmailService;

    public function __construct(ElasticEmailService $elasticEmailService)
    {
        $this->elasticEmailService = $elasticEmailService;
    }

    public function testEmail(Request $request)
    {
        try {
            $validator = \Validator::make($request->all(), [
                'email' => 'required|email'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $testOtp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $emailSent = $this->elasticEmailService->sendOtpEmail($request->email, $testOtp);

            if ($emailSent) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Test email sent successfully',
                    'data' => [
                        'otp' => $testOtp // Remove in production
                    ]
                ]);
            }

            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to send test email'
            ], 500);

        } catch (\Exception $e) {
            Log::error('Test email failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to send test email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 