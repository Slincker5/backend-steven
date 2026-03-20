<?php

namespace App;

class Config
{
    // JWT
    public static function jwtKey(): string
    {
        return getenv('JWT_SECRET') ?: 'PlankThuthu';
    }

    // Base de datos
    public static function dbHost(): string
    {
        return getenv('DB_HOST') ?: 'localhost';
    }

    public static function dbName(): string
    {
        return getenv('DB_NAME') ?: 'steven';
    }

    public static function dbUser(): string
    {
        return getenv('DB_USER') ?: 'root';
    }

    public static function dbPass(): string
    {
        return getenv('DB_PASS') ?: '';
    }

    // CORS
    public static function corsOrigin(): string
    {
        return getenv('CORS_ORIGIN') ?: 'https://autowat.com';
    }

    // AutoWhat API
    public static function autowhatUrl(): string
    {
        return getenv('AUTOWHAT_URL') ?: 'https://whatsapp.autowat.site';
    }

    // Amazon S3 CDN
    public static function s3CdnUrl(): string
    {
        return getenv('S3_CDN_URL') ?: 'https://cdn.multimarcas.app';
    }
}
