<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
 'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'esewa' => [
    'merchant_code' => env('ESEWA_MERCHANT_CODE', 'EPAYTEST'),
    'secret_key' => env('ESEWA_SECRET_KEY', '8gBm/:&EnhH.1/q'),
    'url' => env('ESEWA_URL', 'https://rc-epay.esewa.com.np/api/epay/main/v2/form'),
    ],

    'bank' => [
            'url' => env('BANK_URL', '#'),
        ],

];

// FACEBOOK_CLIENT_ID=945860118562238
// FACEBOOK_CLIENT_SECRET=6d290b0180cf236ce35e592a81af219f
// FACEBOOK_REDIRECT_URI="http://localhost:8000/auth/facebook/callback"

// GOOGLE_CLIENT_ID=116565645897-s13guq26f95f8t8asicecdkhemo60hvk.apps.googleusercontent.com
// GOOGLE_CLIENT_SECRET=OCSPX-GBTmw1mYSG20RvYG1e1OG0gkh1Uu
// GOOGLE_REDIRECT_URI="http://localhost:8000/auth/google/callback"
