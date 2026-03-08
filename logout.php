<?php

//session destroy is in AuthController, this page just calls it and redirects to landing page.
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->logout();

header('Location: /');
exit;
