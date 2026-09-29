<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Adaptive Rate Limiting
    |--------------------------------------------------------------------------
    |
    | All values are configurable through environment variables.
    |
    | Each limiter contains:
    |
    | ip      => [maximum attempts, decay seconds]
    | account => [maximum attempts, decay seconds]
    |
    | "account" is normally based on a hashed email/account identifier.
    |
    */

    'rate_limits' => [

        /*
        |--------------------------------------------------------------------------
        | Authentication
        |--------------------------------------------------------------------------
        |
        | Used for login attempts.
        |
        */
        'auth' => [

            'ip' => [
                (int) env('SECURITY_AUTH_IP_MAX', 10),
                (int) env('SECURITY_AUTH_IP_DECAY', 60),
            ],

            'account' => [
                (int) env('SECURITY_AUTH_ACCOUNT_MAX', 5),
                (int) env('SECURITY_AUTH_ACCOUNT_DECAY', 300),
            ],

            'backoff' => [

                'base' => (int) env(
                    'SECURITY_AUTH_BACKOFF_BASE',
                    5
                ),

                'max' => (int) env(
                    'SECURITY_AUTH_BACKOFF_MAX',
                    900
                ),

                'violation_window' => (int) env(
                    'SECURITY_AUTH_VIOLATION_WINDOW',
                    3600
                ),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Registration
        |--------------------------------------------------------------------------
        */
        'registration' => [

            'ip' => [
                (int) env('SECURITY_REGISTER_IP_MAX', 5),
                (int) env('SECURITY_REGISTER_IP_DECAY', 3600),
            ],

            'account' => [
                (int) env('SECURITY_REGISTER_ACCOUNT_MAX', 3),
                (int) env('SECURITY_REGISTER_ACCOUNT_DECAY', 3600),
            ],

            'backoff' => [

                'base' => (int) env(
                    'SECURITY_REGISTER_BACKOFF_BASE',
                    30
                ),

                'max' => (int) env(
                    'SECURITY_REGISTER_BACKOFF_MAX',
                    3600
                ),

                'violation_window' => (int) env(
                    'SECURITY_REGISTER_VIOLATION_WINDOW',
                    86400
                ),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Password Reset
        |--------------------------------------------------------------------------
        */
        'password_reset' => [

            'ip' => [
                (int) env('SECURITY_RESET_IP_MAX', 5),
                (int) env('SECURITY_RESET_IP_DECAY', 900),
            ],

            'account' => [
                (int) env('SECURITY_RESET_ACCOUNT_MAX', 3),
                (int) env('SECURITY_RESET_ACCOUNT_DECAY', 1800),
            ],

            'backoff' => [

                'base' => (int) env(
                    'SECURITY_RESET_BACKOFF_BASE',
                    30
                ),

                'max' => (int) env(
                    'SECURITY_RESET_BACKOFF_MAX',
                    3600
                ),

                'violation_window' => (int) env(
                    'SECURITY_RESET_VIOLATION_WINDOW',
                    86400
                ),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Email Verification
        |--------------------------------------------------------------------------
        */
        'verification' => [

            'ip' => [
                (int) env('SECURITY_VERIFY_IP_MAX', 10),
                (int) env('SECURITY_VERIFY_IP_DECAY', 900),
            ],

            'account' => [
                (int) env('SECURITY_VERIFY_ACCOUNT_MAX', 5),
                (int) env('SECURITY_VERIFY_ACCOUNT_DECAY', 900),
            ],

            'backoff' => [

                'base' => (int) env(
                    'SECURITY_VERIFY_BACKOFF_BASE',
                    15
                ),

                'max' => (int) env(
                    'SECURITY_VERIFY_BACKOFF_MAX',
                    1800
                ),

                'violation_window' => (int) env(
                    'SECURITY_VERIFY_VIOLATION_WINDOW',
                    86400
                ),
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Sensitive Authenticated Operations
        |--------------------------------------------------------------------------
        */
        'sensitive' => [

            'ip' => [
                (int) env('SECURITY_SENSITIVE_IP_MAX', 30),
                (int) env('SECURITY_SENSITIVE_IP_DECAY', 60),
            ],

            'account' => [
                (int) env('SECURITY_SENSITIVE_ACCOUNT_MAX', 20),
                (int) env('SECURITY_SENSITIVE_ACCOUNT_DECAY', 60),
            ],

            'backoff' => [

                'base' => (int) env(
                    'SECURITY_SENSITIVE_BACKOFF_BASE',
                    5
                ),

                'max' => (int) env(
                    'SECURITY_SENSITIVE_BACKOFF_MAX',
                    300
                ),

                'violation_window' => (int) env(
                    'SECURITY_SENSITIVE_VIOLATION_WINDOW',
                    3600
                ),
            ],
        ],
    ],

];