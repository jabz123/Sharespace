<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

/* Define base path for redirects */
defined('BASE_PATH') || define(
    'BASE_PATH',
    getenv('APP_BASE_PATH') !== false ? getenv('APP_BASE_PATH') : ''
);

/* Create Auth controller */
$auth = new AuthController();

/* Logout the user */
$auth->logout();

/* Redirect to homepage */
header('Location: ' . BASE_PATH . '/');
exit;