<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'exchangerate' => [
        'key' => env('EXCHANGERATE_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OpenAI (AI-assisted verification)
    |--------------------------------------------------------------------------
    |
    | Unlike every service above, the key Trenakt actually uses is set at
    | runtime by a Super Admin from Settings > Submission verification and
    | stored encrypted in the database (see Setting::getEncrypted()) - that's
    | the whole point, so it can be rotated without a redeploy. These env
    | values are only a fallback OpenAiEvaluator reaches for if nothing has
    | been configured in Settings yet (handy for local/dev bootstrapping).
    |
    */

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'timeout' => env('OPENAI_TIMEOUT', 20),
    ],

];
