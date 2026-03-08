<?php
/**
 * Session bootstrap and shared utility helpers.
 *
 * This file only starts the session and provides redirect/flash helpers
 * used across all boundary pages.
 *
 * All authentication logic (login, register, logout, currentUser) now
 * lives in AuthController. All DB queries live in the controllers.
 */
//to start session and set flash/ temporary messages for errors and success, 
//also has redirect helper that sets flash messages and redirects to a url.

require_once __DIR__ . '/db.php';

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1'); //prevents js access to cookies for security from xss
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}


//redirect helper that sets flash messages and redirects to a url.
function redirect(string $url, ?string $error = null, ?string $success = null): never {
    if ($error)   $_SESSION['flash_error']   = $error;
    if ($success) $_SESSION['flash_success'] = $success;
    header('Location: ' . $url);
    exit;
}

//flash messages are stored in session and cleared after being read once.
function flash(string $key): ?string {
    $val = $_SESSION[$key] ?? null;
    unset($_SESSION[$key]);
    return $val;
}

//write flash message to session
function flash_set(string $key, string $value): void {
    $_SESSION[$key] = $value;
}
