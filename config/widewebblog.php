<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service API Configuration
    |--------------------------------------------------------------------------
    |
    | The admin panel is a client of the Wide Web Blog service application.
    | Keep API transport settings centralized here so page components and
    | auth flows never hard-code environment-specific URLs or timeouts.
    |
    */

    'api' => [
        'base_url' => env('WIDEWEBBLOG_API_BASE_URL', 'http://localhost:8000/api/v1'),
        'timeout' => (int) env('WIDEWEBBLOG_API_TIMEOUT', 15),
        'connect_timeout' => (int) env('WIDEWEBBLOG_API_CONNECT_TIMEOUT', 5),
        'retry_times' => (int) env('WIDEWEBBLOG_API_RETRY_TIMES', 1),
        'retry_sleep_ms' => (int) env('WIDEWEBBLOG_API_RETRY_SLEEP_MS', 150),
    ],

    /*
    |--------------------------------------------------------------------------
    | Media URL Configuration
    |--------------------------------------------------------------------------
    |
    | The service may return media paths without a host. Normalize those
    | paths through this base URL so admin screens can render previews and
    | featured-media assets consistently across environments.
    |
    */

    'media' => [
        'base_url' => env('WIDEWEBBLOG_MEDIA_BASE_URL', 'https://media.widewebblog.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Session Bridge
    |--------------------------------------------------------------------------
    |
    | The service API issues the bearer token, but the admin stores it
    | server-side in the Laravel session. These keys define the session
    | locations used by future auth middleware and API client helpers.
    |
    */

    'session' => [
        'token_key' => env('WIDEWEBBLOG_AUTH_TOKEN_KEY', 'widewebblog.auth.token'),
        'token_type_key' => env('WIDEWEBBLOG_AUTH_TOKEN_TYPE_KEY', 'widewebblog.auth.token_type'),
        'abilities_key' => env('WIDEWEBBLOG_AUTH_ABILITIES_KEY', 'widewebblog.auth.abilities'),
        'user_key' => env('WIDEWEBBLOG_AUTH_USER_KEY', 'widewebblog.auth.user'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Auth Behaviour
    |--------------------------------------------------------------------------
    |
    | These values support a future authenticated admin flow backed by the
    | service API without embedding route or device naming assumptions in
    | page components.
    |
    */

    'auth' => [
        'login_path' => env('WIDEWEBBLOG_LOGIN_PATH', '/login'),
        'logout_path' => env('WIDEWEBBLOG_LOGOUT_PATH', '/logout'),
        'home_path' => env('WIDEWEBBLOG_HOME_PATH', '/'),
        'device_name' => env('WIDEWEBBLOG_AUTH_DEVICE_NAME', 'widewebblog-admin'),
    ],
];
