<?php
// handles profile update form submission including avatar upload
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
 
$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/profile.php');
}
 
// pass the avatar file separately — empty array if nothing uploaded
$avatarFile = $_FILES['avatar'] ?? [];
 
$result = $auth->updateProfile($user->id, $_POST, $avatarFile);
 
if (isset($result['ok'])) {
    redirect('/pages/profile.php', null, 'Profile updated successfully!');
}
 
redirect('/pages/profile.php', $result['error']);