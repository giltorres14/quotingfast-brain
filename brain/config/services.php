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

    'allstate' => [
        'api_key' => env('ALLSTATE_API_KEY', 'mock'),
        'test_mode' => env('ALLSTATE_TEST_MODE', true),
        'production_url' => 'https://api.allstateleadmarketplace.com/v2',
        'test_url' => 'https://int.allstateleadmarketplace.com/v2',
    ],

    'vici' => [
        'api_key' => env('VICI_API_KEY', 'production'),
        'api_url' => env('VICI_API_URL', 'https://philli.callix.ai/vicidial/api'),
        'server_ip' => env('VICI_SERVER_IP', '37.27.138.222'),
        'web_server' => env('VICI_WEB_SERVER', 'philli.callix.ai'),
        'test_mode' => env('VICI_TEST_MODE', false), // FIXED: Default to production mode
        'push_enabled' => env('VICI_PUSH_ENABLED', false), // MIGRATION TOGGLE - Set to false during migration
        'default_campaign' => env('VICI_DEFAULT_CAMPAIGN', 'Autodial'),
        'default_list' => env('VICI_DEFAULT_LIST', '101'),
        // Database connection - PRODUCTION CREDENTIALS
        'mysql_host' => env('VICI_MYSQL_HOST', '37.27.138.222'),
        'mysql_db' => env('VICI_MYSQL_DB', 'asterisk'),
        'mysql_user' => env('VICI_MYSQL_USER', 'Superman'),
        'mysql_pass' => env('VICI_MYSQL_PASS', '8ZDWGAAQRD'),
        'mysql_port' => env('VICI_MYSQL_PORT', 3306),
    ],

];
