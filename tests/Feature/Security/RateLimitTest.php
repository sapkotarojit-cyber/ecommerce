<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;

uses(RefreshDatabase::class);

test('login rate limiting uses configurable thresholds and returns 429', function () {
    config([
        'security.rate_limits.auth.ip' => [2, 60],
        'security.rate_limits.auth.account' => [100, 60],
        'security.rate_limits.auth.backoff' => [
            'base' => 2,
            'max' => 8,
            'violation_window' => 60,
        ],
    ]);

    $email = 'rate-limit-' . uniqid() . '@example.com';

    $this->post('/login', [
        'email' => $email,
        'password' => 'WrongPassword123!',
    ])->assertStatus(302);

    $this->post('/login', [
        'email' => $email,
        'password' => 'WrongPassword123!',
    ])->assertStatus(302);

    $this->post('/login', [
        'email' => $email,
        'password' => 'WrongPassword123!',
    ])->assertStatus(429);
});

test('login rate limiting also applies per account identifier', function () {
    config([
        'security.rate_limits.auth.ip' => [100, 60],
        'security.rate_limits.auth.account' => [2, 60],
        'security.rate_limits.auth.backoff' => [
            'base' => 2,
            'max' => 8,
            'violation_window' => 60,
        ],
    ]);

    $email = 'account-limit-' . uniqid() . '@example.com';

    $payload = [
        'email' => $email,
        'password' => 'WrongPassword123!',
    ];

    $this->post('/login', $payload)->assertStatus(302);
    $this->post('/login', $payload)->assertStatus(302);
    $this->post('/login', $payload)->assertStatus(429);
});
