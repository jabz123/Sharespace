<?php

//form for forgot password page
//will post to itself and call requestPasswordReset from AuthController.
//will send reset link to email if exist in system.

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->requestPasswordReset($_POST['email'] ?? '');
    $message = isset($result['ok']) ? 'Password reset link sent to your email.' : $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Forgot Password - SharedSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/public/css/app.css">
</head>
<body>
<div class="auth-wrap">
    <div class="auth-form-side">
        <div class="auth-box">
            <a href="/login.php" class="auth-logo auth-inline-logo">
                <img src="/public/icons/sharedspace-logo-dark.svg" alt="SharedSpace" class="sharedspace-brand-image">
            </a>

            <h1>Forgot password</h1>
            <p class="sub">Enter your account email and we will send a reset link.</p>

            <?php if ($message): ?>
                <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-hero btn-full">Send Reset Link</button>
            </form>

            <p class="auth-alt-link">
                <a href="/login.php" class="back-link">Back to login</a>
            </p>
        </div>
    </div>

    <div class="auth-brand-side">
        <div class="auth-brand-shell">
            <img src="/public/icons/sharedspace-logo-light.svg" alt="SharedSpace" class="sharedspace-brand-image" style="width:240px;margin-bottom:32px">
            <div class="brand-rule"></div>
            <h2>Recover access securely.</h2>
            <p>SharedSpace keeps the account recovery flow simple, calm, and secure so readers and writers can get back to trusted news quickly.</p>
        </div>
    </div>
</div>
</body>
</html>
