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

    'app_store_connect' => [
        'issuer_id' => env('ASC_ISSUER_ID'),
        'key_id' => env('ASC_KEY_ID'),
        'private_key' => resource_path('appstore/' . env('ASC_PRIVATE_KEY')), // PEM contents or path managed by secrets
        'vendor_id' => env('ASC_VENDOR_ID'),
        'base_url' => env('ASC_BASE_URL', 'https://api.appstoreconnect.apple.com'),
    ],

    'fixer' => [
        'key' => env('FIXER_API_KEY'),
        'base_url' => env('FIXER_BASE_URL', 'https://data.fixer.io/api'),
        // Note: Free plans may use EUR as base. We'll compute cross rates accordingly.
    ],

];
