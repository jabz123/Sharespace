<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

// Base path: empty string for AWS root deployment, '/sharedspace' for local dev
defined('BASE_PATH') || define('BASE_PATH', getenv('APP_BASE_PATH') !== false ? getenv('APP_BASE_PATH') : '');

$auth = new AuthController();

//redirect to dashboard if user is log in
if ($auth->currentUser()) {
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

$error = null;

//post login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $auth->login(
        $_POST['email'] ?? '',
        $_POST['password'] ?? ''
    );

    if (isset($result['ok'])) {
        header('Location: ' . BASE_PATH . '/dashboard.php');
        exit;
    }

    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1.0" />
    <title>Sign In – SharedSpace</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/app.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&display=swap" rel="stylesheet">
    <style>
        /* ── Brand side redesign only ── */
        .auth-brand-side {
            background:
                linear-gradient(160deg, hsl(213,56%,10%) 0%, hsl(220,60%,20%) 50%, hsl(213,56%,12%) 100%);
            position: relative;
            overflow: hidden;
        }

        /* Newspaper line texture */
        .auth-brand-side::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 28px, rgba(255,255,255,0.03) 28px, rgba(255,255,255,0.03) 29px),
                repeating-linear-gradient(90deg, transparent, transparent 120px, rgba(255,255,255,0.02) 120px, rgba(255,255,255,0.02) 121px);
            pointer-events: none;
        }

        /* Floating headline cards */
        .news-card {
            position: absolute;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 10px;
            padding: 12px 14px;
            backdrop-filter: blur(6px);
            pointer-events: none;
            animation: floatUp 7s ease-in-out infinite;
        }
        .news-card:nth-child(1) { top: 7%;  left: 5%;   width: 55%; animation-delay: 0s;   }
        .news-card:nth-child(2) { top: 24%; right: 4%;  width: 48%; animation-delay: 1.8s; }
        .news-card:nth-child(3) { top: 43%; left: 7%;   width: 50%; animation-delay: 3.5s; }
        .news-card:nth-child(4) { top: 62%; right: 5%;  width: 42%; animation-delay: 5s;   }

        @keyframes floatUp {
            0%,100% { transform: translateY(0);   opacity: .65; }
            50%      { transform: translateY(-7px); opacity: 1;   }
        }

        .nc-tag  { font-size: 9px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; color: hsl(38,92%,62%); margin-bottom: 5px; }
        .nc-title { font-family: 'Playfair Display', serif; font-size: 12px; color: rgba(255,255,255,.82); line-height: 1.45; }
        .nc-foot  { display: flex; align-items: center; gap: 6px; margin-top: 7px; }
        .nc-dot   { width: 5px; height: 5px; border-radius: 50%; background: hsl(142,71%,55%); animation: blink 2s ease-in-out infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.4} }
        .nc-meta  { font-size: 10px; color: rgba(255,255,255,.38); }
        .nc-score { font-size: 10px; font-weight: 700; color: hsl(142,71%,55%); background: rgba(34,197,94,.12); padding: 2px 7px; border-radius: 99px; margin-left: auto; }

        /* Live ticker at the very bottom of brand side */
        .brand-ticker {
            position: absolute; bottom: 0; left: 0; right: 0;
            background: rgba(0,0,0,.45);
            border-top: 1px solid rgba(255,255,255,.07);
            padding: 9px 32px;
            display: flex; align-items: center; gap: 10px;
            overflow: hidden; z-index: 5;
        }
        .bt-live { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: hsl(38,92%,62%); white-space: nowrap; flex-shrink: 0; }
        .bt-sep  { width: 1px; height: 13px; background: rgba(255,255,255,.18); flex-shrink: 0; }
        .bt-text { font-size: 11px; color: rgba(255,255,255,.55); white-space: nowrap; animation: ticker 28s linear infinite; }
        @keyframes ticker { 0%{transform:translateX(100%)} 100%{transform:translateX(-220%)} }

        /* Keep brand text above cards */
        .auth-brand-side > div:last-of-type { position: relative; z-index: 6; }

        /* Accent line on form side top */
        .auth-form-side::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, hsl(213,56%,24%), hsl(220,60%,45%));
        }
        .auth-form-side { position: relative; }
    </style>
</head>
<body>
<div class="auth-wrap">

    <div class="auth-form-side">
        <div class="auth-box">
            <a href="<?= BASE_PATH ?>/" class="auth-logo">
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
                Don't have an account? <a href="<?= BASE_PATH ?>/register.php">Sign up free</a>
            </p>
        </div>
    </div>

    <div class="auth-brand-side">

        <!-- Floating news headline cards -->
        <div class="news-card">
            <div class="nc-tag">Breaking · World</div>
            <div class="nc-title">Global Leaders Reach Historic Climate Agreement at Summit</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">2 min ago · Reuters</span><span class="nc-score">✓ 94%</span></div>
        </div>
        <div class="news-card">
            <div class="nc-tag">Technology</div>
            <div class="nc-title">AI Breakthrough Enables Real-Time Medical Diagnosis</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">14 min ago · Tech Review</span><span class="nc-score">✓ 89%</span></div>
        </div>
        <div class="news-card">
            <div class="nc-tag">Economics</div>
            <div class="nc-title">Central Banks Signal Rate Cuts as Inflation Eases</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">1 hr ago · Financial Times</span><span class="nc-score">✓ 91%</span></div>
        </div>
        <div class="news-card">
            <div class="nc-tag">Science</div>
            <div class="nc-title">NASA Confirms Water Ice Discovery on Lunar Surface</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">3 hr ago · NASA</span><span class="nc-score">✓ 97%</span></div>
        </div>

        <!-- Original brand content unchanged -->
        <div style="max-width:420px;text-align:center;color:#fff">
            <div style="font-size:80px;margin-bottom:24px">📰</div>
            <h2 style="font-size:32px;font-weight:700;font-family:Georgia,serif;margin-bottom:16px">Truth in Every Headline</h2>
            <p style="opacity:.85;line-height:1.7">Join thousands of journalists and readers who trust SharedSpace for verified, fact-checked news.</p>
        </div>

        <!-- Live ticker -->
        <div class="brand-ticker">
            <span class="bt-live">Live</span>
            <div class="bt-sep"></div>
            <span class="bt-text">Climate Summit Agreement · AI Medical Breakthrough · Rate Cut Signals · Lunar Water Ice Confirmed · Infrastructure Bill Passed · Record Tech Earnings · Peace Talks Resume · Clean Energy Milestone</span>
        </div>

    </div>

</div>
<script src="<?= BASE_PATH ?>/public/js/app.js"></script>
</body>
</html>