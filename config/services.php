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

    'dispatchtrack' => [
        'base_url'   => env('DISPATCHTRACK_BASE_URL'),
        'token_name' => env('DISPATCHTRACK_TOKEN_NAME', 'Authorization'),
        'token'      => env('DISPATCHTRACK_TOKEN'),
        'timeout'    => (int) env('DISPATCHTRACK_TIMEOUT', 15),
    ],

    // config/services.php
    'defontana' => [
        'base_url' => env('DEFONTANA_BASE_URL', 'https://api.defontana.com/api'),
        'username' => env('DEFONTANA_USERNAME'),
        'password' => env('DEFONTANA_PASSWORD'),
        'timeout'  => (int) env('DEFONTANA_TIMEOUT', 20),
    ],



];
