<?php

use App\Http\Controllers\RelativeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\ElasticController;
use App\Http\Controllers\ElasticEmailService;
use App\Http\Controllers\ElasticMailController;


Route::get('/user/{id}', [UserController::class, 'getUser']);
Route::get('/user', [UserController::class, 'getAllUsers']);

Route::post('/user', [UserController::class, 'postUser']);
Route::post('/user{id}', [UserController::class, 'postUser']);

Route::get('/dropdowns', [UserController::class, 'getMasterData']);
Route::get('/dropdowns-data/{key}', [UserController::class, 'getDropdownData']);

Route::post('/relative', [RelativeController::class, 'postFamilyRelation']);
// Route::post('/', [RelativeController::class, 'postFamilyRelation']);

// OTP Routes
Route::post('/otp/send', [OtpController::class, 'sendOtp']);
Route::post('/otp/verify', [OtpController::class, 'verifyOtp']);
Route::post('/verify-registration-otp', [OtpController::class, 'verifyRegistrationOtp']);
Route::post('/request-login-otp', [OtpController::class, 'requestLoginOtp']);
Route::post('/verify-login-otp', [OtpController::class, 'verifyLoginOtp']);

// Protected routes that require authentication
// Route::middleware(['auth:sanctum'])->group(function () {
//     // User routes
//     Route::post('/user', [UserController::class, 'postUser']);
//     Route::post('/user/{id}', [UserController::class, 'postUser']);
// });

// Test routes
Route::post('/test/email', [TestController::class, 'testEmail']);