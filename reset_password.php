<?php

//this page handles the password reset form when user clicks the link in their email.
//the link contains a token that is used to identify the user and validate the request.

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
    <title>Reset Password – SharedSpace</title>
    <link rel="stylesheet" href="/public/css/login.css"/>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet"/>
</head>
<body>

<!-- ══ LEFT — 40% ══ -->
<div class="panel-left">
    <div class="left-inner">

        <a href="/" class="auth-logo">
            <img src="/public/icons/sharedspace-logo-dark.svg" alt="SharedSpace" class="sharedspace-brand-image" style="width:188px">
        </a>

        <div class="auth-box">
            <h1>Reset your password</h1>
            <p class="sub">Set a new password to restore access to your account.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="password">New password</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password" placeholder="Create a new password" required/>
                        <button type="button" class="eye-btn" onclick="togglePw('password')" aria-label="Toggle password">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm new password</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required/>
                        <button type="button" class="eye-btn" onclick="togglePw('confirm_password')" aria-label="Toggle password">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-hero btn-full">Continue</button>
            </form>

        </div>
    </div>

    <div class="left-footer">
        <p class="signup-cta"><a href="/login.php">Back to login</a></p>
    </div>
</div>


<!-- ══ RIGHT — 60% ══ -->
<div class="panel-right">
    <div class="bg-grid"></div>
    <div class="bg-glow-teal"></div>
    <div class="bg-glow-amber"></div>

    <div class="login-brand-panel">
        <div class="login-brand-content">
            <span class="login-brand-kicker">Account recovery</span>
            <h2>Return to trusted reading.</h2>
            <p>Once your password is updated, you can continue writing, reading, and reviewing articles in the same premium environment.</p>
        </div>
    </div>
</div>

</body>
</html>
<script src="/public/js/app.js?v=<?= filemtime(__DIR__ . '/public/js/app.js') ?>"></script>
