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

    /*
    |--------------------------------------------------------------------------
    | HOTSPOT SMS
    |--------------------------------------------------------------------------
    */

    'hotspot_sms' => [
        'api_key' => env('HOTSPOT_SMS_API_KEY'),
    ],

    'hotspot_beem' => [
        'sender' => env('HOTSPOT_BEEM_SENDER'),
    ],

    /*
    |--------------------------------------------------------------------------
    | BEEM SMS
    |--------------------------------------------------------------------------
    |
    | Used by Bagambakamo for sending SMS messages to members.
    |
    */

    'beem' => [
        'api_key' => env('BEEM_API_KEY'),
        'secret_key' => env('BEEM_SECRET_KEY'),
        'sender' => env('BEEM_SENDER'),
    ],

    'bagambakamo' => [
        'forwarder_member_id' => env('BAGAMBAKAMO_FORWARDER_MEMBER_ID'),
    ],

];
