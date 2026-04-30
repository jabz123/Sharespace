<?php

//profile password change logic handled here. form is in edit-password.php.
//edit-password will post old_password, new_password, confirm_password here
//will call updatePassword, which will use password_verify() to check old password

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/edit-password.php');
}
//call updatePassword to update password and handle result
$result = $auth->updatePassword($user->id, $_POST);

if (isset($result['ok'])) {
    redirect('/pages/edit-password.php', null, 'Password changed successfully!');
}

redirect('/pages/edit-password.php', $result['error']);
