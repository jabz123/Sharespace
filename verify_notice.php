<?php
// Page shown after registration to ask the user to verify their email.
$email = $_GET['email'] ?? '';
$hasResent = isset($_GET['resent']);
$cooldown = isset($_GET['cooldown']) ? max(0, (int) $_GET['cooldown']) : 0;
$hasError = isset($_GET['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - SharedSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/public/css/verify-notice.css?v=<?= filemtime(__DIR__ . '/public/css/verify-notice.css') ?>">
</head>

<body>
    <main class="verify-page">
        <section class="verify-card">
            <a href="/" class="verify-logo" aria-label="SharedSpace home">
                <img src="/public/icons/sharedspace-logo-dark.svg" alt="SharedSpace">
            </a>

            <div class="verify-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" focusable="false">
                    <path d="M4 6.5h16v11H4z" />
                    <path d="m4.5 7 7.5 6 7.5-6" />
                    <path d="m8.5 13.5-4 3.5" />
                    <path d="m15.5 13.5 4 3.5" />
                </svg>
            </div>

            <p class="verify-kicker">Account created</p>
            <h1>Check your email to finish signing up.</h1>

            <p class="verify-copy">
                We sent a verification link<?= $email ? ' to' : '' ?>
                <?php if ($email): ?>
                    <strong><?= htmlspecialchars($email) ?></strong>
                <?php endif; ?>.
                Open the email and click the link before logging in.
            </p>

            <?php if ($hasResent): ?>
                <div class="verify-alert verify-alert-success">Verification email sent again. Please check your inbox.</div>
            <?php endif; ?>

            <?php if ($cooldown > 0): ?>
                <div class="verify-alert verify-alert-warning">Resend verification email available in <?= $cooldown ?> seconds.</div>
            <?php endif; ?>

            <?php if ($hasError): ?>
                <div class="verify-alert verify-alert-error">Something went wrong. Please try again.</div>
            <?php endif; ?>

            <div class="verify-note">
                <span aria-hidden="true">i</span>
                <p>If you do not see the email, check your spam or junk folder.</p>
            </div>

            <div class="verify-actions">
                <a class="verify-btn verify-btn-primary" href="/login.php">Go to Login Page</a>
                <button
                    class="verify-btn verify-btn-secondary"
                    id="resendBtn"
                    type="button"
                    onclick="window.location.href='resend_verification.php?email=<?= urlencode($email) ?>'">
                    Resend Verification Email
                </button>
            </div>
        </section>
    </main>

    <script src="/public/js/countdown.js?v=<?= filemtime(__DIR__ . '/public/js/countdown.js') ?>"></script>
</body>

</html>
