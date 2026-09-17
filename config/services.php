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

    'google_ads' => [
        'config_file' => config_path('google_ads_php.ini'),
        'api_key' => env('API_KEY', 'wisevisiontest'),
        // Master list used by SpendingController to validate any incoming customer_id.
        // Excludes 9486155847 (Royal Auto Maintenance - no admin access) and 2342328484 (Suspended).
        'battery_ids' => [


            '1966568739', // 800mybatt
            '5997615193', // 800Battery.com
            '2260001647', // GMB Abu Dhabi
            '6886934984', // 800sayara dubai
            
        ],


        'gmb_ids' => [
            '1543050984', // GMB


            '2864209607', // GMB
            '2671900464', // GMB
            '1848779768', // GMB
            '9486155847', // GMB
            '6315440435', // GMB
            '3673874794', // GMB
            '8466805998', // GMB
            '7258632939', // GMB
            '4135584694', // GMB
            '2343232484', // GMB
            '4556106500', // GMB
            '9932553829', // GMB


        ],

        'garage_ids' => [

            '5338176252',


        ],

        'tyre_ids' => [

            '6267682600', // Tyre

        ],
    ],

];
