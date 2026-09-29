<?php

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authentication endpoints reject unexpected top-level fields', function () {
    $response = $this
        ->withHeader('Accept', 'application/json')
        ->post('/login', [
            'email' => 'security-test@example.com',
            'password' => 'NotThePassword123!',
            'unexpected' => 'must-be-rejected',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('request');
});

test('registration rejects unexpected top-level fields', function () {
    $response = $this
        ->withHeader('Accept', 'application/json')
        ->post('/register', [
            'name' => 'Security Test',
            'email' => 'security-test@example.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'unexpected' => 'must-be-rejected',
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('request');
});