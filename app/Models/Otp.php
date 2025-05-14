<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Otp extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'email',
        'otp_code',
        'attempt_count',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'attempt_count' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
} 