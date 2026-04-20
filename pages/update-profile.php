<?php
// handles profile update form submission including avatar upload and interests
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/OnboardingController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/profile.php');
}

//this is new, this is for the interests shit from profile page
// handle interests update (separate form on the profile page)
if (isset($_POST['update_interests'])) {
    $onboardCtrl = new OnboardingController();
    $interests    = $_POST['interests'] ?? [];
    $result       = $onboardCtrl->updateInterests($user->id, $interests);

    if (isset($result['ok'])) {
        redirect('/pages/profile.php', null, 'Interests updated successfully!');
    }
    redirect('/pages/profile.php', $result['error']);
}

// handle regular profile update (name, email, bio, avatar)
$avatarFile = $_FILES['avatar'] ?? [];
$result     = $auth->updateProfile($user->id, $_POST, $avatarFile);

if (isset($result['ok'])) {
    redirect('/pages/profile.php', null, 'Profile updated successfully!');
}

redirect('/pages/profile.php', $result['error']);