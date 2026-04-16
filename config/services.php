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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash-lite'),
        'discovery_model' => env('GEMINI_DISCOVERY_MODEL', 'gemini-2.5-flash-lite'),
        'content_model' => env('GEMINI_CONTENT_MODEL', 'gemini-2.5-flash-lite'),
        'fallback_model' => env('GEMINI_FALLBACK_MODEL', 'gemini-2.5-flash'),
    ],

    'twitter' => [
        'api_key' => env('TWITTER_API_KEY'),
        'api_secret' => env('TWITTER_API_SECRET'),
        'access_token' => env('TWITTER_ACCESS_TOKEN'),
        'access_token_secret' => env('TWITTER_ACCESS_TOKEN_SECRET'),
        'bearer_token' => env('TWITTER_BEARER_TOKEN'),
    ],

    'instagram' => [
        'app_id' => env('INSTAGRAM_APP_ID'),
        'app_secret' => env('INSTAGRAM_APP_SECRET'),
        'access_token' => env('INSTAGRAM_ACCESS_TOKEN'),
        'account_id' => env('INSTAGRAM_ACCOUNT_ID'),
    ],

    'linkedin' => [
        'client_id' => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'access_token' => env('LINKEDIN_ACCESS_TOKEN'),
        'person_urn' => env('LINKEDIN_PERSON_URN'),
    ],

    'snapchat' => [
        'client_id' => env('SNAPCHAT_CLIENT_ID'),
        'client_secret' => env('SNAPCHAT_CLIENT_SECRET'),
        'access_token' => env('SNAPCHAT_ACCESS_TOKEN'),
        'refresh_token' => env('SNAPCHAT_REFRESH_TOKEN'),
        'organization_id' => env('SNAPCHAT_ORGANIZATION_ID'),
        'profile_id' => env('SNAPCHAT_PROFILE_ID'),
    ],

    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
    ],

    'waha' => [
        'base_url' => env('WAHA_BASE_URL', 'http://localhost:3000'),
        'session' => env('WAHA_SESSION', 'default'),
        'api_key' => env('WAHA_API_KEY'),
        'status_bg' => env('WAHA_STATUS_BG', '#121212'),
        'status_font' => (int) env('WAHA_STATUS_FONT', 1),
    ],

    'turnstile' => [
        'site_key' => env('TURNSTILE_SITE_KEY'),
        'secret_key' => env('TURNSTILE_SECRET_KEY'),
    ],

    'google_sheets' => [
        'webhook_url' => env('GOOGLE_SHEETS_WEBHOOK_URL'),
        'secret' => env('GOOGLE_SHEETS_SECRET'),
    ],

    'indexnow' => [
        'key' => env('INDEXNOW_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
