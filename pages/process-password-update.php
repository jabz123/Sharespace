<?php
// handles password change form submission
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/edit-password.php');
}

$result = $auth->updatePassword($user->id, $_POST);

if (isset($result['ok'])) {
    redirect('/pages/edit-password.php', null, 'Password changed successfully!');
}

redirect('/pages/edit-password.php', $result['error']);