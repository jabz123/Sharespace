<?php
require_once __DIR__ . '/db.php';

defined('BASE_PATH') || define('BASE_PATH', '');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

function redirect(string $url, ?string $error = null, ?string $success = null): never {
    if ($error) {
        $_SESSION['flash_error'] = $error;
    }

    if ($success) {
        $_SESSION['flash_success'] = $success;
    }

    header('Location: ' . $url);
    exit;
}

function flash(string $key): ?string {
    $val = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $val;
}

function flash_set(string $key, string $value): void {
    $_SESSION[$key] = $value;
}
