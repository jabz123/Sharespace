<?php

// starts the php session used across the website
// provides helper functions for redirects and flash messages
// flash messages store temporary success or error messages in session
// used by pages to show messages after redirects 

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

    $target = $url;

    $isPostRedirect = (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST');

    if (!$isPostRedirect && !headers_sent()) {
        header('Location: ' . $target, true, 302);
        exit;
    }

    if (!headers_sent()) {
        header('Content-Type: text/html; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
    }

    $escapedTarget = htmlspecialchars($target, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta http-equiv="refresh" content="0;url=' . $escapedTarget . '">';
    echo '<title>Redirecting...</title></head><body>';
    echo '<script>window.top.location.replace(' . json_encode($target) . ');</script>';
    echo '<p>Redirecting... If nothing happens, <a href="' . $escapedTarget . '">continue here</a>.</p>';
    echo '</body></html>';
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
