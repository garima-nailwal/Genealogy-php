<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            if ($user->status !== 'A') {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Account is not active. Please verify your email.'
                ], 403);
            }
        }

        return $next($request);
    }
} 