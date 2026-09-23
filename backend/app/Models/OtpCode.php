<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    use HasFactory;

    protected $table = 'otp_codes';

    protected $fillable = [
        'phone_number',
        'code_hash',
        'attempts',
        'consecutive_failures',
        'expires_at',
        'last_sent_at',
        'blocked_until',
        'verified_at',
    ];

    protected $hidden = ['code_hash'];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'consecutive_failures' => 'integer',
            'expires_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'blocked_until' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}