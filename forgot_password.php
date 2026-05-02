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
    <title>Forgot Password – SharedSpace</title>
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
            <h1>Forgot password</h1>
            <p class="sub">Enter your account email and we will send a reset link.</p>

            <?php if ($message): ?>
                <div class="alert <?= isset($result['ok']) ? 'alert-success' : 'alert-error' ?>">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="<?= isset($result['ok']) ? 'M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z' : 'M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z' ?>" clip-rule="evenodd"/></svg>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input type="email" id="email" name="email" placeholder="you@example.com" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-hero btn-full">
                    Send Reset Link
                    <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                </button>
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
            <h2>Recover access securely.</h2>
            <p>SharedSpace keeps the account recovery flow simple, calm, and secure so readers and writers can get back to trusted news quickly.</p>

            <div class="login-return-card">
                <div class="login-return-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3 4 7v6c0 5 3.4 7.7 8 8 4.6-.3 8-3 8-8V7l-8-4Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>
                </div>
                <div>
                    <h3>Secure recovery link</h3>
                    <p>Check your email for a secure link to reset your password.</p>
                </div>
            </div>

            <div class="login-panel-grid">
                <div>
                    <span>Verify</span>
                    <strong>Email</strong>
                </div>
                <div>
                    <span>Click</span>
                    <strong>Link</strong>
                </div>
                <div>
                    <span>Reset</span>
                    <strong>Password</strong>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<script src="/public/js/app.js?v=<?= filemtime(__DIR__ . '/public/js/app.js') ?>"></script>
