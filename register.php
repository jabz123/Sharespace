<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
if ($auth->currentUser()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->register(
        $_POST['name'] ?? '',
        $_POST['email'] ?? '',
        $_POST['password'] ?? '',
        $_POST['confirm_password'] ?? ''
    );

    if (isset($result['ok'])) {
        redirect('/verify_notice.php?email=' . urlencode($_POST['email']));
    }

    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Register - SharedSpace</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/public/css/app.css" />
</head>
<body>
<div class="auth-wrap">
    <div class="auth-form-side">
        <div class="auth-box">
            <a href="/" class="auth-logo auth-inline-logo">
                <img src="/public/icons/sharedspace-logo-dark.svg" alt="SharedSpace" class="sharedspace-brand-image">
            </a>

            <h1>Create your account</h1>
            <p class="sub">Start publishing and reading trusted stories in a calmer, premium news space.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="name">Full name</label>
                    <div class="input-icon">
                        <input type="text" id="name" name="name" placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-icon">
                        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon" style="position:relative">
                        <input type="password" id="password" name="password" placeholder="Create a secure password" required style="padding-right:64px" />
                        <button type="button" data-toggle-password="password" data-icon-toggle="true" class="password-toggle" aria-label="Show password" title="Show password">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <div class="input-icon" style="position:relative">
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required style="padding-right:64px" />
                        <button type="button" data-toggle-password="confirm_password" data-icon-toggle="true" class="password-toggle" aria-label="Show password" title="Show password">
                            <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                        </button>
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

    <div class="auth-brand-side">
        <div class="auth-brand-shell">
            <img src="/public/icons/sharedspace-logo-light.svg" alt="SharedSpace" class="sharedspace-brand-image" style="width:240px;margin-bottom:32px">
            <div class="brand-rule"></div>
            <h2>Join a trusted news community.</h2>
            <p>Publish with confidence, read with clarity, and build credibility around stories that feel informed rather than noisy.</p>

            <div class="auth-feature-list">
                <div class="auth-feature-item"><span class="auth-feature-dot"></span><span>Verification-focused publishing flow</span></div>
                <div class="auth-feature-item"><span class="auth-feature-dot"></span><span>Thoughtful community reading experience</span></div>
                <div class="auth-feature-item"><span class="auth-feature-dot"></span><span>Premium visual trust across every article</span></div>
            </div>
        </div>
    </div>
</div>
<script src="/public/js/app.js?v=<?= filemtime(__DIR__ . '/public/js/app.js') ?>"></script>
</body>
</html>
