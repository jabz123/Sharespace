<?php
require_once __DIR__ . '/includes/db.php';

if (!isset($_GET['token'])) {
    die("Invalid verification link.");
}

$token = $_GET['token'];
$user = DB::first("SELECT id FROM users WHERE verification_token = ?", [$token]);

if (!$user) {
    die("Invalid or expired verification link.");
}

DB::execute("UPDATE users SET email_verified = 1, verification_token = NULL WHERE id = ?", [$user['id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified – SharedSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <!-- ✅ Correct path to the existing verify-notice.css (same as verify-notice.php) -->
    <link rel="stylesheet" href="/public/css/verify-notice.css?v=<?= filemtime(__DIR__ . '/public/css/verify-notice.css') ?>">
</head>
<body>
    <main class="verify-page">
        <section class="verify-card">

            <a href="/" class="verify-logo" aria-label="SharedSpace home">
                <img src="/public/icons/sharedspace-logo-dark.svg" alt="SharedSpace">
            </a>

            <div class="verify-icon" aria-hidden="true">
                <!-- Checkmark icon (same stroke styling as the envelope in verify-notice) -->
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>

            <p class="verify-kicker">Email Verified</p>
            <h1>Your account is ready.</h1>

            <p class="verify-copy">
                Your email has been confirmed successfully. You can now sign in and start reading verified stories.
            </p>

            <div class="verify-alert verify-alert-success">
                ✅ Email verified successfully. Redirecting you to the login page…
            </div>

            <div class="verify-actions" style="grid-template-columns: 1fr;">
                <a class="verify-btn verify-btn-primary" href="/login.php">Go to Login Page</a>
            </div>

        </section>
    </main>

    <script>
        setTimeout(() => {
            window.location.href = '/login.php';
        }, 5000);
    </script>
</body>
</html>