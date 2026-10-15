<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendingRegistration extends Model
{
    protected $fillable = [
        'name',
        'email',
        'password',
        'verification_code_hash',
        'verification_code_expires_at',
        'verification_attempts',
        'last_code_sent_at',
    ];

    protected $hidden = [
        'password',
        'verification_code_hash',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'verification_code_expires_at' => 'datetime',
            'last_code_sent_at' => 'datetime',
            'verification_attempts' => 'integer',
        ];
    }
}
