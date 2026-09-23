<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | Integration contracts are bound in AppServiceProvider. The drivers below
    | select the local/fake implementations until the production providers are
    | configured. No real credentials are used during local development.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
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
    | OTP / authentication
    |--------------------------------------------------------------------------
    | driver: fake | firebase | whatsapp
    */
    'otp' => [
        'driver' => env('OTP_DRIVER', 'fake'),
        'ttl_minutes' => (int) env('OTP_TTL_MINUTES', 5),
        'resend_cooldown_seconds' => (int) env('OTP_RESEND_COOLDOWN_SECONDS', 60),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 3),
        'lock_minutes' => (int) env('OTP_LOCK_MINUTES', 10),
        'lock_minutes_escalated' => (int) env('OTP_LOCK_MINUTES_ESCALATED', 60),
        'fake' => [
            // Deterministic code used by FakeOtpService when configured.
            'code' => env('OTP_FAKE_CODE', '123456'),
            // Expose the generated code in the API response (dev/test only).
            'expose_code' => (bool) env('OTP_FAKE_EXPOSE_CODE', true),
        ],
        'firebase' => [
            'api_key' => env('FIREBASE_API_KEY'),
            'project_id' => env('FIREBASE_PROJECT_ID'),
            'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        ],
        'whatsapp' => [
            'enabled' => (bool) env('WHATSAPP_OTP_ENABLED', false),
            'gateway_url' => env('WHATSAPP_OTP_GATEWAY_URL'),
            'api_key' => env('WHATSAPP_OTP_API_KEY'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Maps / geolocation
    |--------------------------------------------------------------------------
    */
    'google_maps' => [
        'enabled' => (bool) env('GOOGLE_MAPS_ENABLED', false),
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'radius_steps_km' => array_map('intval', explode(',', env('SEARCH_RADIUS_STEPS_KM', '10,20,30'))),
        'initial_radius_km' => (int) env('SEARCH_INITIAL_RADIUS_KM', 10),
        'max_radius_km' => (int) env('SEARCH_MAX_RADIUS_KM', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Push notifications / realtime
    |--------------------------------------------------------------------------
    */
    'firebase' => [
        'credentials_path' => env('FIREBASE_CREDENTIALS_PATH'),
    ],

    'realtime' => [
        'driver' => env('REALTIME_DRIVER', 'fake'), // fake | pusher | soketi
        'broadcast_connection' => env('REALTIME_BROADCAST_CONNECTION', 'null'),
    ],

    /*
    |--------------------------------------------------------------------------
    | File storage
    |--------------------------------------------------------------------------
    */
    'storage' => [
        'default_disk' => env('FILESYSTEM_DISK', 'local'),
        'document_disk' => env('DOCUMENT_DISK', 'local'),
        's3' => [
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('AWS_BUCKET'),
        ],
        'spaces' => [
            'key' => env('SPACES_ACCESS_KEY_ID'),
            'secret' => env('SPACES_SECRET_ACCESS_KEY'),
            'region' => env('SPACES_REGION'),
            'endpoint' => env('SPACES_ENDPOINT'),
            'bucket' => env('SPACES_BUCKET'),
        ],
    ],
];