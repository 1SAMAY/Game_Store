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
    $projectRef = (string) app_env('SUPABASE_PROJECT_REF', 'gcshwfphrgpzvzmosvzf');
    $defaultHost = sprintf('db.%s.supabase.co', $projectRef);

    return [
        'driver' => (string) app_env('DB_DRIVER', 'pgsql'),
        'url' => (string) app_env('DATABASE_URL', ''),
        'host' => (string) app_env('DB_HOST', $defaultHost),
        'port' => (int) app_env('DB_PORT', 5432),
        'database' => (string) app_env('DB_NAME', 'postgres'),
        'username' => (string) app_env('DB_USER', 'postgres'),
        'password' => (string) app_env('DB_PASSWORD', ''),
        'charset' => (string) app_env('DB_CHARSET', 'utf8mb4'),
        'sslmode' => (string) app_env('DB_SSLMODE', 'require'),
        'project_ref' => $projectRef,
        'region' => (string) app_env('SUPABASE_REGION', 'ap-southeast-1'),
        'pooler_mode' => (string) app_env('SUPABASE_POOLER_MODE', 'session'),
    ];
}

function app_db_connection_url(): string
{
    $db = app_db_config();

    $password = (string) $db['password'];

    if ($password !== '') {
        $projectRef = (string) $db['project_ref'];
        $region = (string) $db['region'];
        $encodedPassword = rawurlencode($password);

        return sprintf(
            'postgres://postgres.%s:%s@aws-0-%s.pooler.supabase.com:5432/postgres?sslmode=%s',
            $projectRef,
            $encodedPassword,
            $region,
            rawurlencode((string) $db['sslmode'])
        );
    }

    if (!empty($db['url'])) {
        return $db['url'];
    }

    return '';
}
