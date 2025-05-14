<?php

namespace App\Services;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Support\Facades\Log;

class UserRegistrationService
{
    protected $elasticEmailService;

    public function __construct(ElasticEmailService $elasticEmailService)
    {
        $this->elasticEmailService = $elasticEmailService;
    }

    public function initiateRegistration(string $email, ?string $mobileNumber = null): array
    {
        try {
            // Check if user already exists
            $existingUser = User::where('email', $email)
                              ->orWhere('mobile_number', $mobileNumber)
                              ->first();

            if ($existingUser) {
                if ($existingUser->status === 'A') {
                    return [
                        'status' => 'failed',
                        'message' => 'User already registered and active'
                    ];
                }
                $user = $existingUser;
            } else {
                // Create new user
                $user = new User();
                $user->email = $email;
                $user->mobile_number = $mobileNumber;
                $user->status = 'I'; // Inactive
                $user->save();
            }

            // Generate OTP
            $otp = new Otp();
            $otp->user_id = $user->user_id;
            $otp->email = $email;
            $otp->phone = $mobileNumber;
            $otp->otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp->expires_at = now()->addMinutes(5);
            $otp->attempt_count = 0;
            $otp->save();

            // Send OTP email
            if ($email) {
                $emailSent = $this->elasticEmailService->sendOtpEmail($email, $otp->otp_code);
                if (!$emailSent) {
                    Log::error('Failed to send registration OTP email', [
                        'user_id' => $user->user_id,
                        'email' => $email
                    ]);
                    return [
                        'status' => 'failed',
                        'message' => 'Failed to send OTP email'
                    ];
                }
            }

            return [
                'status' => 'success',
                'message' => 'Registration initiated successfully',
                'data' => [
                    'user_id' => $user->user_id,
                    'expires_at' => $otp->expires_at
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Registration initiation failed: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'message' => 'Failed to initiate registration'
            ];
        }
    }

    public function verifyRegistration(int $userId, string $otp, string $email, ?string $mobileNumber = null): array
    {
        try {
            $otp = Otp::where('user_id', $userId)
                     ->where(function($query) use ($email, $mobileNumber) {
                         $query->where('email', $email);
                         if ($mobileNumber) {
                             $query->orWhere('phone', $mobileNumber);
                         }
                     })
                     ->where('otp_code', $otp)
                     ->where('expires_at', '>', now())
                     ->first();

            if (!$otp) {
                return [
                    'status' => 'failed',
                    'message' => 'Invalid or expired OTP'
                ];
            }

            $user = User::find($userId);
            if (!$user) {
                return [
                    'status' => 'failed',
                    'message' => 'User not found'
                ];
            }

            // Update user status
            $user->status = 'A';
            $user->save();

            // Delete used OTP
            $otp->delete();

            return [
                'status' => 'success',
                'message' => 'Registration verified successfully',
                'data' => [
                    'user' => [
                        'user_id' => $user->user_id,
                        'email' => $user->email,
                        'mobile_number' => $user->mobile_number,
                        'status' => $user->status
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Registration verification failed: ' . $e->getMessage());
            return [
                'status' => 'failed',
                'message' => 'Failed to verify registration'
            ];
        }
    }
} 