<?php

namespace App\Http\Controllers;

use App\Models\Otp;
use App\Models\User;
use App\Services\ElasticEmailService;
use App\Services\UserRegistrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OtpController extends Controller
{
    protected $maxAttempts = 4;
    protected $decayMinutes = 1;
    protected $elasticEmailService;
    protected $userRegistrationService;

    public function __construct(
        ElasticEmailService $elasticEmailService,
        UserRegistrationService $userRegistrationService
    ) {
        $this->elasticEmailService = $elasticEmailService;
        $this->userRegistrationService = $userRegistrationService;
    }

    public function sendOtp(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'mobile_number' => 'required_without:email|string|max:15',
                'email' => 'required_without:mobile_number|email',
                'is_registration' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            if ($request->is_registration) {
                $result = $this->userRegistrationService->initiateRegistration(
                    $request->email,
                    $request->mobile_number
                );

                return response()->json($result, $result['status'] === 'success' ? 200 : 400);
            }

            // Check for existing valid OTP
            $existingOtp = Otp::where(function($query) use ($request) {
                $query->where('phone', $request->mobile_number)
                      ->orWhere('email', $request->email);
            })
            ->where('expires_at', '>', now())
            ->first();

            if ($existingOtp) {
                return response()->json([
                    'message' => 'An active OTP already exists',
                    'expires_at' => $existingOtp->expires_at
                ]);
            }

            // Start database transaction
            DB::beginTransaction();

            // Generate new OTP
            $otp = new Otp();
            $otp->otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp->expires_at = now()->addMinutes(1);
            $otp->attempt_count = 0;

            if ($request->mobile_number) {
                $otp->phone = $request->mobile_number;
            }
            if ($request->email) {
                $otp->email = $request->email;
            }

            // Handle user association
            if ($request->is_registration) {
                $user = User::where('email', $request->email)
                           ->orWhere('mobile_number', $request->mobile_number)
                           ->first();

                if (!$user) {
                    $user = new User();
                    $user->email = $request->email;
                    $user->mobile_number = $request->mobile_number;
                    $user->status = 'I'; // Initial status
                    $user->save();
                }

                $otp->user_id = $user->user_id;
            } else {
                // For login, find existing user
                $user = User::where('email', $request->email)
                           ->orWhere('mobile_number', $request->mobile_number)
                           ->first();

                if ($user) {
                    $otp->user_id = $user->user_id;
                }
            }

            // Save OTP to database
            $otp->save();

            // Log OTP generation
            Log::info('OTP generated and saved:', [
                'user_id' => $otp->user_id,
                'email' => $otp->email,
                'phone' => $otp->phone,
                'otp_code' => $otp->otp_code,
                'expires_at' => $otp->expires_at
            ]);

            // Send OTP via email if email is provided
            if ($request->has('email')) {
                $emailSent = $this->elasticEmailService->sendOtpEmail($request->email, $otp->otp_code);
                
                if (!$emailSent) {
                    // Rollback transaction if email sending fails
                    DB::rollBack();
                    Log::error('Failed to send OTP email', [
                        'user_id' => $otp->user_id,
                        'email' => $request->email
                    ]);
                    return response()->json([
                        'message' => 'Failed to send OTP email',
                        'expires_at' => $otp->expires_at
                    ], 500);
                }
            }

            // Commit transaction
            DB::commit();

            // TODO: Send OTP via SMS if mobile_number is provided

            return response()->json([
                'message' => 'OTP sent successfully',
                'expires_at' => $otp->expires_at,
                // 'otp' => $otp->otp_code // Remove in production
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OTP Generation Failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Failed to generate OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function requestLoginOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required_without:mobile_number|email',
                'mobile_number' => 'required_without:email|string|max:15'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Log the request data
            Log::info('Login OTP Request:', [
                'email' => $request->email,
                'mobile_number' => $request->mobile_number
            ]);

            // Find user by email or mobile_number
            $user = User::where(function($query) use ($request) {
                if ($request->has('email')) {
                    $query->where('email', $request->email);
                }
                if ($request->has('mobile_number')) {
                    $query->orWhere('mobile_number', $request->mobile_number);
                }
            })->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], 404);
            }

            // Check for existing valid OTP
            $existingOtp = Otp::where('user_id', $user->user_id)
                            ->where(function($query) use ($request) {
                                if ($request->has('email')) {
                                    $query->where('email', $request->email);
                                }
                                if ($request->has('mobile_number')) {
                                    $query->orWhere('phone', $request->mobile_number);
                                }
                            })
                            ->where('expires_at', '>', now())
                            ->first();

            if ($existingOtp) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'An active OTP already exists',
                    'data' => [
                        'expires_at' => $existingOtp->expires_at,
                        // 'otp' => $existingOtp->otp_code // Remove in production
                    ]
                ]);
            }

            // Start database transaction
            DB::beginTransaction();

            // Generate new OTP
            $otp = new Otp();
            $otp->user_id = $user->user_id;
            $otp->email = $request->email;
            $otp->phone = $request->mobile_number;
            $otp->otp_code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $otp->expires_at = now()->addMinutes(5);
            $otp->attempt_count = 0;

            // Save OTP to database
            $otp->save();

            // Log OTP generation
            Log::info('OTP generated and saved:', [
                'user_id' => $otp->user_id,
                'email' => $otp->email,
                'phone' => $otp->phone,
                'otp_code' => $otp->otp_code,
                'expires_at' => $otp->expires_at
            ]);

            // Send OTP via email if email is provided
            if ($request->has('email')) {
                $emailSent = $this->elasticEmailService->sendOtpEmail($request->email, $otp->otp_code);
                
                if (!$emailSent) {
                    // Rollback transaction if email sending fails
                    DB::rollBack();
                    Log::error('Failed to send OTP email', [
                        'user_id' => $user->user_id,
                        'email' => $request->email
                    ]);
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'Failed to send OTP email'
                    ], 500);
                }
            }

            // Commit transaction
            DB::commit();

            // TODO: Send OTP via SMS if mobile_number is provided

            return response()->json([
                'status' => 'success',
                'message' => 'Login OTP sent successfully',
                'data' => [
                    'expires_at' => $otp->expires_at,
                    // 'otp' => $otp->otp_code // Remove in production
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Login OTP Request Failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to generate login OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'mobile_number' => 'required_without:email|string|max:15',
                'email' => 'required_without:mobile_number|email',
                'otp' => 'required|string|size:6'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $otp = Otp::where(function($query) use ($request) {
                $query->where('phone', $request->mobile_number)
                      ->orWhere('email', $request->email);
            })
            ->where('otp_code', $request->otp)
            ->where('expires_at', '>', now())
            ->first();

            if (!$otp) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired OTP'
                ], 400);
            }

            // Increment attempt count
            $otp->attempt_count++;
            $otp->save();

            if ($otp->attempt_count > 3) {
                $otp->delete();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Too many failed attempts. Please request a new OTP.'
                ], 400);
            }

            // If this is a registration, update user status
            if ($request->is_registration && $otp->user_id) {
                $user = User::find($otp->user_id);
                if ($user) {
                    $user->status = 'A'; // Active
                    $user->save();
                }
            }

            // Generate authentication token
            if ($otp->user_id) {
                $user = User::find($otp->user_id);
                if ($user) {
                    $token = $user->createToken('otp-auth')->plainTextToken;
                    
                    // Delete used OTP
                    $otp->delete();

                    // Format user data to include only necessary fields
                    $userData = [
                        'user_id' => $user->user_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'mobile_number' => $user->mobile_number,
                        'status' => $user->status,
                        'approval_status' => $user->approval_status
                    ];

                    return response()->json([
                        'status' => 'success',
                        'message' => 'OTP verified successfully',
                        'data' => [
                            'token' => $token,
                            'user' => $userData
                        ]
                    ]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'OTP verified successfully',
                'data' => []
            ]);

        } catch (\Exception $e) {
            Log::error('OTP Verification Failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to verify OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyRegistrationOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'otp' => 'required|string|size:6',
                'email' => 'required_without:mobile_number|email',
                'mobile_number' => 'required_without:email|string|max:15'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $result = $this->userRegistrationService->verifyRegistration(
                $request->user_id,
                $request->otp,
                $request->email,
                $request->mobile_number
            );

            return response()->json($result, $result['status'] === 'success' ? 200 : 400);

        } catch (\Exception $e) {
            Log::error('Registration OTP Verification Failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to verify registration OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function verifyLoginOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'otp' => 'required|string|size:6',
                'email' => 'required_without:mobile_number|email',
                'mobile_number' => 'required_without:email|string|max:15'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Find user by email or mobile_number
            $user = User::where(function($query) use ($request) {
                if ($request->has('email')) {
                    $query->where('email', $request->email);
                }
                if ($request->has('mobile_number')) {
                    $query->orWhere('phone', $request->mobile_number);
                }
            })->first();

            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], 404);
            }

            // Log the search parameters
            Log::info('OTP Verification Search:', [
                'user_id' => $user->user_id,
                'email' => $request->email,
                'mobile_number' => $request->mobile_number,
                'otp' => $request->otp
            ]);

            // Find and verify OTP
            $otp = Otp::where('user_id', $user->user_id)
                     ->where(function($query) use ($request) {
                         if ($request->has('email')) {
                             $query->where('email', $request->email);
                         }
                         if ($request->has('mobile_number')) {
                             $query->orWhere('phone', $request->mobile_number);
                         }
                     })
                     ->where('otp_code', $request->otp)
                     ->where('expires_at', '>', now())
                     ->first();

            // Log the OTP query result
            Log::info('OTP Query Result:', [
                'otp_found' => $otp ? true : false,
                'otp_details' => $otp ? [
                    'user_id' => $otp->user_id,
                    'email' => $otp->email,
                    'phone' => $otp->phone,
                    'expires_at' => $otp->expires_at
                ] : null
            ]);

            if (!$otp) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired OTP'
                ], 400);
            }

            // Increment attempt count
            $otp->attempt_count++;
            $otp->save();

            if ($otp->attempt_count > 3) {
                $otp->delete();
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Too many failed attempts. Please request a new OTP.'
                ], 400);
            }

            // Update user status to active
            $user->status = 'A';
            $user->save();

            // Generate authentication token
            $token = $user->createToken('login-token')->plainTextToken;

            // Delete used OTP
            $otp->delete();

            // Log successful login
            Log::info('User logged in successfully:', [
                'user_id' => $user->user_id,
                'email' => $user->email,
                'mobile_number' => $user->phone,
                'status' => $user->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'token' => $token,
                    'user' => [
                        'user_id' => $user->user_id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'mobile_number' => $user->phone,
                        'status' => $user->status
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Login OTP Verification Failed: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to verify login OTP',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
    // Other methods (verifyOtp, verifyRegistrationOtp, verifyLoginOtp) remain unchanged
