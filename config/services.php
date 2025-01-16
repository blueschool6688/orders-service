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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],


    'paytm-wallet' => [
        'env'              => "",
        'merchant_id'      => "",
        'merchant_key'     => "",
        'merchant_website' => "",
        'channel'          => "",
        'industry_type'    => "",
    ],
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID',
        '1055916261287-o80am6nb6mm62udp1m748s51chh6n2sl.apps.googleusercontent.com'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET','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'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],
];
