<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
//go to dashboard if logged in
if ($auth->currentUser()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
// create new user 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register(
        $_POST['name']             ?? '',
        $_POST['email']            ?? '',
        $_POST['password']         ?? '',
        $_POST['confirm_password'] ?? ''
    );

    if (isset($result['ok'])) {
        // redirect to verify notice page and pass user email so we can resend verification easily
        redirect('/verify_notice.php?email=' . urlencode($_POST['email']));
    }

    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" /><meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Register – SharedSpace</title>
    <link rel="stylesheet" href="/public/css/app.css" />
</head>
<body>
<div class="auth-wrap">

    <!-- Brand side -->
    <div class="auth-brand-side">
        <div style="max-width:420px;text-align:center;color:#fff">
            <div style="font-size:80px;margin-bottom:24px">📰</div>
            <h2 style="font-size:32px;font-weight:700;font-family:Georgia,serif;margin-bottom:16px">Start Your Journey</h2>
            <p style="opacity:.85;line-height:1.7;margin-bottom:32px">Create your free account and join a community of writers and readers committed to truthful, verified journalism.</p>
            <div style="display:flex;flex-direction:column;gap:12px;text-align:left">
                <?php foreach (['Free to read and publish articles','AI trust score on every article','Comment and engage with the community'] as $f): ?>
                <div style="display:flex;align-items:center;gap:12px;background:rgba(255,255,255,.1);border-radius:10px;padding:14px 16px">
                    <span style="font-size:18px">✓</span>
                    <span style="font-size:14px"><?= $f ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Form side -->
    <div class="auth-form-side">
        <div class="auth-box">
            <a href="/" class="auth-logo">
                <span style="font-size:24px">📰</span>
                <span style="font-size:18px;font-weight:700;font-family:Georgia,serif">SharedSpace</span>
            </a>

            <h1>Create your account</h1>
            <p class="sub">Join the platform for verified news</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <div class="input-icon">
                        <span class="icon">👤</span>
                        <input type="text" id="name" name="name" placeholder="John Doe"
                            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
                    </div>
                </div>
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
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-icon">
                        <span class="icon">🔒</span>
                        <input type="password" id="confirm_password" name="confirm_password"
                            placeholder="••••••••" required />
                    </div>
                </div>
                <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:20px;font-size:13px;color:var(--muted)">
                    <input type="checkbox" id="terms" required style="margin-top:3px;flex-shrink:0" />
                    <label for="terms">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a></label>
                </div>

                <button type="submit" class="btn btn-hero btn-full">Create Account</button>
            </form>

            <p class="text-sm text-muted" style="text-align:center;margin-top:20px">
                Already have an account? <a href="/login.php">Sign in</a>
            </p>
        </div>
    </div>

</div>
<script src="/public/js/app.js"></script>
</body>
</html>
