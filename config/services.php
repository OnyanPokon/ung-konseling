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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Gateway (Fonnte)
    |--------------------------------------------------------------------------
    |
    | This is the only place the sending account is configured. The number
    | messages appear to come from is not listed here and cannot be set by
    | request: it is whatever number is paired with the token below. Moving
    | sending to an institutional number means pairing that number as a device
    | in the gateway dashboard and replacing this token — not editing config.
    |
    */

    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
        'country_code' => env('FONNTE_COUNTRY_CODE') ?: '62',
    ],

];
