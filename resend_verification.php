<?php
// This page handles the action when the user clicks "Resend Verification Email"
// UI display is handled in verify_notice.php
// email sending logic is inside AuthController 


require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();


// get email passed from verify_notice.php through URL
$email = $_GET['email'] ?? '';

// if email is missing, redirect back to notice page
if (!$email) {
    header("Location: verify_notice.php");
    exit();
}

// call resend verification function from AuthController
$result = $auth->resendVerification($email);

if (isset($result['ok'])) {

    header("Location: verify_notice.php?resent=1&email=" . urlencode($email));

} elseif (isset($result['cooldown'])) {

    header("Location: verify_notice.php?cooldown=" . $result['cooldown'] . "&email=" . urlencode($email));

} else {

    header("Location: verify_notice.php?error=1&email=" . urlencode($email));

}

exit();