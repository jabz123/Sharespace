<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
//redirect to dashboard if user is log in
if ($auth->currentUser()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
//post login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->login(
        $_POST['email']    ?? '',
        $_POST['password'] ?? ''
    );

    if (isset($result['ok'])) {
        header('Location: /dashboard.php');
        exit;
    }

    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Sign In – SharedSpace</title>
    <link rel="stylesheet" href="/public/css/app.css" />
</head>
<body>
<div class="auth-wrap">

    <!-- Form side -->
    <div class="auth-form-side">
        <div class="auth-box">
            <a href="/" class="auth-logo">
                <span style="font-size:24px">📰</span>
                <span style="font-size:18px;font-weight:700;font-family:Georgia,serif">SharedSpace</span>
            </a>

            <h1>Welcome back</h1>
            <p class="sub">Sign in to continue to your dashboard</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php $successMsg = flash('flash_success'); if ($successMsg): ?>
                <div class="alert alert-success"><?= htmlspecialchars($successMsg) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-icon">
                        <span class="icon">✉</span>
                        <input type="email" id="email" name="email" placeholder="you@example.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon" style="position:relative">
                        <span class="icon">🔒</span>
                        <input type="password" id="password" name="password"
                            placeholder="••••••••" required style="padding-right:44px" />
                        <button type="button" data-toggle-password="password"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px">👁</button>
                    </div>
                </div>

                <button type="submit" class="btn btn-hero btn-full">Sign In</button>
            </form>

            <p class="text-sm text-muted" style="text-align:center;margin-top:20px">
                Don't have an account? <a href="/register.php">Sign up free</a>
            </p>

        </div>
    </div>

    <!-- Brand side -->
    <div class="auth-brand-side">
        <div style="max-width:420px;text-align:center;color:#fff">
            <div style="font-size:80px;margin-bottom:24px">📰</div>
            <h2 style="font-size:32px;font-weight:700;font-family:Georgia,serif;margin-bottom:16px">Truth in Every Headline</h2>
            <p style="opacity:.85;line-height:1.7">Join thousands of journalists and readers who trust SharedSpace for verified, fact-checked news.</p>
        </div>
    </div>

</div>
<script src="/public/js/app.js"></script>
</body>
</html>
