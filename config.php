<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function app_env(string $key, $default = null)
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

function app_base_url(): string
{
    $baseUrl = app_env('APP_BASE_URL', 'http://localhost/Game_Store');
    return rtrim((string) $baseUrl, '/');
}

function app_db_config(): array
{
    return [
        'driver' => (string) app_env('DB_DRIVER', 'pgsql'),
        'host' => (string) app_env('DB_HOST', 'localhost'),
        'port' => (int) app_env('DB_PORT', 5432),
        'database' => (string) app_env('DB_NAME', 'postgres'),
        'username' => (string) app_env('DB_USER', 'postgres'),
        'password' => (string) app_env('DB_PASSWORD', ''),
        'charset' => (string) app_env('DB_CHARSET', 'utf8mb4'),
        'sslmode' => (string) app_env('DB_SSLMODE', 'require'),
    ];
}
