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
    <style>
        body { background: hsl(213,56%,10%); }

        .auth-wrap { display: flex; min-height: 100vh; }

        /* ── Form side — dark to match brand ── */
        .auth-form-side {
            width: 460px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 52px 56px;
            background: hsl(213,56%,13%);
            border-right: 1px solid rgba(255,255,255,.07);
            position: relative;
        }
        .auth-form-side::before {
            content: '';
            position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, hsl(38,88%,60%), hsl(15,88%,62%));
        }

        /* logo */
        .auth-logo {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 44px; color: #fff; text-decoration: none;
        }
        .auth-logo:hover { text-decoration: none; }
        .auth-logo span:last-child { color: #fff; font-family: Georgia, serif; font-size: 18px; font-weight: 700; }

        /* headings */
        .auth-box h1 {
            font-size: 28px; font-weight: 700;
            font-family: Georgia, serif;
            color: #fff;
            margin-bottom: 6px; letter-spacing: -.3px;
        }
        .auth-box p.sub {
            font-size: 14px;
            color: rgba(255,255,255,.45);
            margin-bottom: 36px;
        }

        /* labels */
        .auth-box label {
            font-size: 13px; font-weight: 500;
            color: rgba(255,255,255,.6);
        }

        /* inputs */
        .auth-box input[type=email],
        .auth-box input[type=password] {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.12);
            color: #fff;
            font-size: 14px;
        }
        .auth-box input::placeholder { color: rgba(255,255,255,.25); }
        .auth-box input:focus {
            background: rgba(255,255,255,.09);
            border-color: rgba(255,255,255,.28);
            box-shadow: 0 0 0 3px rgba(255,255,255,.06);
        }
        .auth-box .icon { color: rgba(255,255,255,.35); }

        /* password toggle */
        .auth-box [data-toggle-password] { color: rgba(255,255,255,.4) !important; }

        /* sign in button */
        .auth-box .btn-hero {
            padding: 12px 18px;
            font-size: 15px;
            letter-spacing: .2px;
            margin-top: 4px;
            background: linear-gradient(135deg, hsl(38,88%,55%), hsl(15,88%,58%));
            color: #fff;
        }
        .auth-box .btn-hero:hover { opacity: .88; }

        /* bottom link */
        .auth-box .text-muted { color: rgba(255,255,255,.35) !important; }
        .auth-box a { color: hsl(38,88%,65%); }
        .auth-box a:hover { color: hsl(38,88%,75%); }

        /* alerts */
        .auth-box .alert-error {
            background: hsl(0,72%,51%,.15);
            border-color: hsl(0,72%,51%,.3);
            color: hsl(0,80%,75%);
        }
        .auth-box .alert-success {
            background: hsl(142,71%,45%,.15);
            border-color: hsl(142,71%,45%,.3);
            color: hsl(142,60%,70%);
        }

        /* ── Brand side ── */
        .auth-brand-side {
            flex: 1;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-end;
            padding: 52px 52px 80px;
            background: linear-gradient(155deg,
                hsl(213,56%,10%) 0%,
                hsl(220,58%,20%) 55%,
                hsl(213,56%,10%) 100%
            );
        }
        .auth-brand-side::before {
            content: '';
            position: absolute; inset: 0; pointer-events: none;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 28px, rgba(255,255,255,.022) 28px, rgba(255,255,255,.022) 29px),
                repeating-linear-gradient(90deg, transparent, transparent 120px, rgba(255,255,255,.014) 120px, rgba(255,255,255,.014) 121px);
        }

        /* ── News cards ── */
        .nc {
            position: absolute;
            background: rgba(255,255,255,.055);
            border: 1px solid rgba(255,255,255,.11);
            border-radius: 10px;
            padding: 14px 16px;
            backdrop-filter: blur(10px);
            pointer-events: none;
            animation: nc-float 7s ease-in-out infinite;
            z-index: 2;
        }
        .nc:nth-child(1) { top: 5%;  left: 5%;  width: 52%; animation-delay: 0s;   }
        .nc:nth-child(2) { top: 24%; right: 5%; width: 46%; animation-delay: 1.9s; }
        .nc:nth-child(3) { top: 44%; left: 5%;  width: 50%; animation-delay: 3.6s; }
        .nc:nth-child(4) { top: 63%; right: 5%; width: 44%; animation-delay: 5.1s; }
        @keyframes nc-float {
            0%,100% { transform: translateY(0);    opacity: .6; }
            50%      { transform: translateY(-8px); opacity: 1;  }
        }
        .nc-tag   { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: hsl(38,88%,65%); margin-bottom: 6px; }
        .nc-title { font-size: 13px; font-family: Georgia, serif; color: rgba(255,255,255,.9); line-height: 1.45; }
        .nc-foot  { display: flex; align-items: center; gap: 6px; margin-top: 9px; }
        .nc-dot   { width: 5px; height: 5px; border-radius: 50%; background: hsl(142,68%,58%); flex-shrink: 0; animation: dot-blink 2s ease-in-out infinite; }
        @keyframes dot-blink { 0%,100%{opacity:1} 50%{opacity:.2} }
        .nc-meta  { font-size: 10px; color: rgba(255,255,255,.38); }
        .nc-score { font-size: 10px; font-weight: 700; color: hsl(142,68%,58%); background: rgba(34,197,94,.14); padding: 2px 9px; border-radius: 99px; margin-left: auto; }

        /* ── Brand bottom ── */
        .brand-body { position: relative; z-index: 3; max-width: 420px; }
        .brand-rule { width: 36px; height: 3px; background: linear-gradient(90deg, hsl(38,88%,60%), hsl(15,88%,62%)); border-radius: 2px; margin-bottom: 20px; }
        .brand-body h2 { font-size: 32px; font-weight: 700; font-family: Georgia, serif; color: #fff; line-height: 1.2; margin-bottom: 14px; letter-spacing: -.3px; }
        .brand-body p  { font-size: 14px; color: rgba(255,255,255,.55); line-height: 1.75; margin-bottom: 32px; }
        .brand-stats   { display: flex; gap: 36px; }
        .bs { display: flex; flex-direction: column; gap: 3px; }
        .bs-n { font-size: 22px; font-weight: 700; font-family: Georgia, serif; color: #fff; line-height: 1; }
        .bs-l { font-size: 10px; color: rgba(255,255,255,.35); text-transform: uppercase; letter-spacing: .9px; margin-top: 4px; }

        /* ── Live ticker ── */
        .brand-ticker {
            position: absolute; bottom: 0; left: 0; right: 0; z-index: 4;
            background: rgba(0,0,0,.4);
            border-top: 1px solid rgba(255,255,255,.07);
            padding: 10px 52px;
            display: flex; align-items: center; gap: 12px;
            overflow: hidden;
        }
        .bt-live { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: hsl(38,88%,65%); white-space: nowrap; flex-shrink: 0; }
        .bt-sep  { width: 1px; height: 13px; background: rgba(255,255,255,.15); flex-shrink: 0; }
        .bt-text { font-size: 11px; color: rgba(255,255,255,.45); white-space: nowrap; animation: ticker 30s linear infinite; }
        @keyframes ticker { 0%{transform:translateX(100%)} 100%{transform:translateX(-220%)} }

        @media (max-width: 800px) {
            .auth-brand-side { display: none; }
            .auth-form-side  { width: 100%; border-right: none; }
        }
    </style>
</head>
<body>
<div class="auth-wrap">

    <!-- Form side -->
    <div class="auth-form-side">
        <div class="auth-box">
            <a href="/" class="auth-logo">
                <span style="font-size:24px">📰</span>
                <span>SharedSpace</span>
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

        <div class="nc">
            <div class="nc-tag">Breaking · World</div>
            <div class="nc-title">Global Leaders Reach Historic Climate Agreement at Summit</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">2 min ago · Reuters</span><span class="nc-score">✓ 94%</span></div>
        </div>
        <div class="nc">
            <div class="nc-tag">Technology</div>
            <div class="nc-title">AI Breakthrough Enables Real-Time Medical Diagnosis</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">14 min ago · Tech Review</span><span class="nc-score">✓ 89%</span></div>
        </div>
        <div class="nc">
            <div class="nc-tag">Economics</div>
            <div class="nc-title">Central Banks Signal Rate Cuts as Inflation Eases</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">1 hr ago · Financial Times</span><span class="nc-score">✓ 91%</span></div>
        </div>
        <div class="nc">
            <div class="nc-tag">Science</div>
            <div class="nc-title">NASA Confirms Water Ice Discovery on Lunar Surface</div>
            <div class="nc-foot"><div class="nc-dot"></div><span class="nc-meta">3 hr ago · NASA</span><span class="nc-score">✓ 97%</span></div>
        </div>

        <div class="brand-body">
            <div class="brand-rule"></div>
            <h2>Truth in Every<br>Headline.</h2>
            <p>Join thousands of journalists and readers who trust SharedSpace for verified, fact-checked news.</p>
            <div class="brand-stats">
                <div class="bs"><span class="bs-n">12K+</span><span class="bs-l">Verified Articles</span></div>
                <div class="bs"><span class="bs-n">94%</span><span class="bs-l">Avg Trust Score</span></div>
                <div class="bs"><span class="bs-n">3.4K</span><span class="bs-l">Active Writers</span></div>
            </div>
        </div>

        <div class="brand-ticker">
            <span class="bt-live">Live</span>
            <div class="bt-sep"></div>
            <span class="bt-text">Climate Summit Agreement &nbsp;·&nbsp; AI Medical Breakthrough &nbsp;·&nbsp; Rate Cut Signals &nbsp;·&nbsp; Lunar Water Ice Confirmed &nbsp;·&nbsp; Infrastructure Bill Passed &nbsp;·&nbsp; Record Tech Earnings &nbsp;·&nbsp; Peace Talks Resume &nbsp;·&nbsp; Clean Energy Milestone</span>
        </div>

    </div>

</div>
<script src="/public/js/app.js"></script>
</body>
</html>
