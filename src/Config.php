<?php

namespace App;

class Config
{
    private static bool $loaded = false;

    private static function loadEnv(): void
    {
        if (self::$loaded) return;
        self::$loaded = true;

        $envFile = dirname(__DIR__) . '/.env';
        if (!file_exists($envFile)) return;

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;
            if (strpos($line, '=') === false) continue;
            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }

    private static function env(string $key, string $default = ''): string
    {
        self::loadEnv();
        return getenv($key) ?: $default;
    }
    // JWT
    public static function jwtKey(): string
    {
        return self::env('JWT_SECRET', 'PlankThuthu');
    }

    // Base de datos
    public static function dbHost(): string
    {
        return self::env('DB_HOST', 'localhost');
    }

    public static function dbName(): string
    {
        return self::env('DB_NAME', 'steven');
    }

    public static function dbUser(): string
    {
        return self::env('DB_USER', 'root');
    }

    public static function dbPass(): string
    {
        return self::env('DB_PASS', '');
    }

    // CORS
    public static function corsOrigin(): string
    {
        return self::env('CORS_ORIGIN', 'https://autowat.com');
    }

    // AutoWhat API
    public static function autowhatUrl(): string
    {
        return self::env('AUTOWHAT_URL', 'https://whatsapp.autowat.site');
    }

    // Amazon S3 CDN
    public static function s3CdnUrl(): string
    {
        return self::env('S3_CDN_URL', 'https://cdn.multimarcas.app');
    }
}
