<?php

//registration page. will check if user is already logged in. if logged in redirect to dashboard
//calls register() from AuthController to handle registration logic

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
    <title>Register – SharedSpace</title>
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
            <h1>Create your account</h1>
            <p class="sub">Start publishing and reading trusted stories in a calmer, premium news space.</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">
                <div class="form-group">
                    <label for="name">Full name</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M6 20c0-2 3-4 6-4s6 2 6 4"/></svg>
                        </span>
                        <input type="text" id="name" name="name" placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required />
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password" placeholder="Create a secure password" required />
                        <button type="button" class="eye-btn" onclick="togglePw('password')" aria-label="Toggle password">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat your password" required />
                        <button type="button" class="eye-btn" onclick="togglePw('confirm_password')" aria-label="Toggle password">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div style="display:flex;gap:8px;align-items:flex-start;margin-bottom:20px;font-size:13px;color:var(--muted)">
                    <input type="checkbox" id="terms" required style="margin-top:3px;flex-shrink:0" />
                    <label for="terms">I agree to the <a href="/terms.php">Terms of Service</a> and <a href="/privacy.php">Privacy Policy</a></label>
                </div>

                <button type="submit" class="btn btn-hero btn-full">
                    Create Account
                    <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                </button>
            </form>

        </div>
    </div>

    <div class="left-footer">
        <p class="signup-cta">Already have an account? <a href="/login.php">Sign in</a></p>
    </div>
</div>


<!-- ══ RIGHT — 60% ══ -->
<div class="panel-right">
    <div class="bg-grid"></div>
    <div class="bg-glow-teal"></div>
    <div class="bg-glow-amber"></div>

    <div class="login-brand-panel">
        <div class="login-brand-content">
            <span class="login-brand-kicker">Start with credibility</span>
            <h2>Build your trusted news identity.</h2>
            <p>Create an account to publish verified stories, save articles that matter, and join a reading space built around clarity.</p>

            <div class="login-return-card">
                <div class="login-return-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3 4 7v6c0 5 3.4 7.7 8 8 4.6-.3 8-3 8-8V7l-8-4Z"/>
                        <path d="m9 12 2 2 4-5"/>
                    </svg>
                </div>
                <div>
                    <h3>Publish with confidence</h3>
                    <p>Get AI-assisted credibility checks and join a calmer, verified news environment.</p>
                </div>
            </div>

            <div class="login-panel-grid">
                <div>
                    <span>AI Check</span>
                    <strong>Ready</strong>
                </div>
                <div>
                    <span>Saved</span>
                    <strong>Articles</strong>
                </div>
                <div>
                    <span>Community</span>
                    <strong>Verified</strong>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
<script src="/public/js/app.js?v=<?= filemtime(__DIR__ . '/public/js/app.js') ?>"></script>
