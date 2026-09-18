<?php

declare(strict_types=1);

define('APP_NAME', 'Sistema de Escrituras');

define('APP_ENV', 'production');

define('APP_TIMEZONE', 'America/Mexico_City');

date_default_timezone_set(APP_TIMEZONE);

define(
    'MAX_FILE_SIZE',
    20 * 1024 * 1024
);

if (session_status() === PHP_SESSION_NONE) {

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);

    session_start();
}