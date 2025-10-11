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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'commcare' => [
        'api_url' => env('COMMCARE_API_URL'),
        'email' => env('COMMCARE_EMAIL'),
        'api_key' => env('COMMCARE_API_KEY'),
    ],

    'africas_talking' => [
        'username' => env('AFRICAS_TALKING_USERNAME', 'sandbox'),
        'api_key' => env('AFRICAS_TALKING_API_KEY'),
        'sender_id' => env('AFRICAS_TALKING_SENDER_ID', 'S-REMIND'),
        'environment' => env('AFRICAS_TALKING_ENVIRONMENT', 'sandbox'),
    ],

];
