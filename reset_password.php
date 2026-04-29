<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$token = $_GET['token'] ?? '';

$user = DB::first(
    'SELECT id FROM users
     WHERE reset_token = ?
     AND reset_expires > NOW()',
    [$token]
);

if (!$user) {
    die('Invalid or expired password reset link.');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        DB::execute(
            'UPDATE users
             SET password = ?, reset_token = NULL, reset_expires = NULL
             WHERE id = ?',
            [password_hash($password, PASSWORD_BCRYPT), $user['id']]
        );

        redirect('/login.php', null, 'Password updated successfully.');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Reset Password - SharedSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-form-side">
        <div class="auth-box">
            <a href="/" class="auth-logo auth-inline-logo">
                <img src="/public/icons/sharedspace-logo-dark.svg" alt="SharedSpace" class="sharedspace-brand-image">
            </a>

            <h1>Reset your password</h1>
            <p class="sub">Set a new password to restore access to your account.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="password">New password</label>
                    <div class="input-icon" style="position:relative">
                        <input type="password" id="password" name="password" placeholder="Create a new password" required style="padding-right:64px">
                        <button type="button" data-toggle-password="password" class="password-toggle">Show</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm new password</label>
                    <div class="input-icon" style="position:relative">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required style="padding-right:64px">
                        <button type="button" data-toggle-password="confirm_password" class="password-toggle">Show</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-hero btn-full">Continue</button>
            </form>

            <p class="text-sm text-muted" style="text-align:center;margin-top:20px">
                <a href="/login.php">Back to login</a>
            </p>
        </div>
    </div>

    <div class="auth-brand-side">
        <div class="auth-brand-shell">
            <img src="/public/icons/sharedspace-logo-light.svg" alt="SharedSpace" class="sharedspace-brand-image" style="width:240px;margin-bottom:32px">
            <div class="brand-rule"></div>
            <h2>Return to trusted reading.</h2>
            <p>Once your password is updated, you can continue writing, reading, and reviewing articles in the same premium environment.</p>
        </div>
    </div>
</div>
<script src="/public/js/app.js"></script>
</body>
</html>
