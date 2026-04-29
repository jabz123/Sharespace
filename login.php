<?php
// Displays the login page UI
// Contains the HTML login form for email and password input
// When user clicks "Sign In", the form sends the input to AuthController.php for authentication
// After successful login, check if the user has completed the onboarding form.
// If not completed, redirect the user to onboarding.php to set their preferences.
// when check for user details during login will also check if they completed onboarding form

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/OnboardingController.php';

$auth = new AuthController();
//redirect to dashboard if user is log in
if ($auth->currentUser()) {
    $currentUser = $auth->currentUser();
    if ($currentUser->role === 'system_admin') {
        header('Location: /pages/admin-dashboard.php');
    } elseif ($currentUser->role === 'ai_trainer') {
        $auth->logout();
        header('Location: /login.php');
    } elseif ($currentUser->role === 'category_admin') {
        header('Location: /pages/category-admin-dashboard.php');
    } else {
        header('Location: /dashboard.php');
    }
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

        // get login user
        $user = $auth->currentUser();

        // system_admin users skip onboarding and go straight to admin dashboard
        if ($user->role === 'system_admin') {
            header('Location: /pages/admin-dashboard.php');
        } elseif ($user->role === 'ai_trainer') {
            $auth->logout();
            header('Location: /login.php');
        } elseif ($user->role === 'category_admin') {
            // category_admin users go straight to the category admin dashboard
            header('Location: /pages/category-admin-dashboard.php');
        } else {
            // regular users check if they completed onboarding
            $onboardCtrl = new OnboardingController();
            if (!$onboardCtrl->isCompleted($user->id)) {
                header('Location: /pages/onboarding.php');
            } else {
                header('Location: /dashboard.php');
            }
        }

        exit;
    }

    $error = $result['error'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1.0"/>
    <title>Sign In – SharedSpace</title>
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
            <h1>Welcome back</h1>
            <p class="sub">Sign in to continue to your dashboard</p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php $successMsg = flash('flash_success');
if ($successMsg): ?>
                <div class="alert alert-success">
                    <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0;margin-top:1px"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <?= htmlspecialchars($successMsg) ?>
                </div>
            <?php endif; ?>

            <form method="POST" autocomplete="on">

                <div class="form-group">
                    <label for="email">Email address</label>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input type="email" id="email" name="email" placeholder="you@example.com"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            autocomplete="email" required/>
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-row-between">
                        <label for="password">Password</label>
                        <a href="/forgot_password.php" class="forgot-link">Forgot password?</a>
                    </div>
                    <div class="input-shell">
                        <span class="pre-icon">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        </span>
                        <input type="password" id="password" name="password"
                            placeholder="••••••••" autocomplete="current-password" required/>
                        <button type="button" class="eye-btn" onclick="togglePw()" aria-label="Toggle password">
                            <svg id="eyeIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-hero btn-full">
                    Sign In
                    <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 8h10M9 4l4 4-4 4"/></svg>
                </button>

            </form>


        </div>
    </div>

    <div class="left-footer">
        <p class="signup-cta">Don't have an account? <a href="/register.php">Sign up free</a></p>
        <div class="status-pill"><span class="status-dot"></span>All systems normal</div>
    </div>
</div>


<!-- ══ RIGHT — 60% ══ -->
<div class="panel-right">
    <div class="bg-grid"></div>
    <div class="bg-glow-teal"></div>
    <div class="bg-glow-amber"></div>

    <div class="r-topbar">
        <div class="live-badge"><span class="live-dot"></span><span class="live-text">Live feed</span></div>
        <div class="r-stats">
            <div class="r-stat"><div class="r-stat-val">1,284</div><div class="r-stat-lbl">Articles today</div></div>
            <div class="r-divider"></div>
            <div class="r-stat"><div class="r-stat-val">94.2%</div><div class="r-stat-lbl">Avg trust score</div></div>
            <div class="r-divider"></div>
            <div class="r-stat"><div class="r-stat-val">3.4K</div><div class="r-stat-lbl">Active writers</div></div>
        </div>
    </div>

    <div class="r-feed">
        <div class="feed-hdr">
            <span class="feed-lbl">Trending Now</span>
            <div class="feed-chips">
                <span class="chip active">All</span>
                <span class="chip">World</span>
                <span class="chip">Tech</span>
            </div>
        </div>
        <div class="a-card"><span class="a-dot d-world"></span><div class="a-body"><div class="a-meta"><span class="a-cat c-world">World</span><span class="a-sep">/</span><span class="a-time">2 min ago</span></div><div class="a-title">Global Leaders Reach Historic Climate Agreement at Summit in Geneva</div><div class="a-src">Reuters / International Affairs</div></div><div class="a-right"><div class="trust hi">94%</div></div></div>
        <div class="a-card"><span class="a-dot d-tech"></span><div class="a-body"><div class="a-meta"><span class="a-cat c-tech">Technology</span><span class="a-sep">/</span><span class="a-time">14 min ago</span></div><div class="a-title">AI Breakthrough Enables Real-Time Medical Diagnosis at Hospital Scale</div><div class="a-src">Tech Review / Health & AI</div></div><div class="a-right"><div class="trust hi">89%</div></div></div>
        <div class="a-card"><span class="a-dot d-econ"></span><div class="a-body"><div class="a-meta"><span class="a-cat c-econ">Economics</span><span class="a-sep">/</span><span class="a-time">1 hr ago</span></div><div class="a-title">Central Banks Signal Coordinated Rate Cuts as Inflation Eases</div><div class="a-src">Financial Times / Monetary Policy</div></div><div class="a-right"><div class="trust hi">91%</div></div></div>
        <div class="a-card"><span class="a-dot d-sci"></span><div class="a-body"><div class="a-meta"><span class="a-cat c-sci">Science</span><span class="a-sep">/</span><span class="a-time">3 hr ago</span></div><div class="a-title">NASA Confirms Substantial Water Ice Deposits on Lunar South Pole</div><div class="a-src">NASA / Space Exploration</div></div><div class="a-right"><div class="trust hi">97%</div></div></div>
        <div class="a-card"><span class="a-dot d-geo"></span><div class="a-body"><div class="a-meta"><span class="a-cat c-geo">Geopolitics</span><span class="a-sep">/</span><span class="a-time">5 hr ago</span></div><div class="a-title">Infrastructure Bill Passes Senate With Bipartisan Support</div><div class="a-src">AP News / US Politics</div></div><div class="a-right"><div class="trust mid">86%</div></div></div>
    </div>

    <div class="r-hero">
        <div class="hero-copy">
            <div class="hero-eyebrow"><div class="hero-line"></div><span class="hero-eyebrow-lbl">Trusted Journalism</span></div>
            <h2>Truth in <em>every</em><br>headline.</h2>
            <p>Join thousands of journalists and readers who trust SharedSpace for AI-verified, fact-checked news.</p>
        </div>
        <div class="hero-stats">
            <div class="hs"><div class="hs-n">12<sup>K+</sup></div><div class="hs-l">Verified Articles</div></div>
            <div class="hs"><div class="hs-n">94<sup>%</sup></div><div class="hs-l">Trust Score</div></div>
            <div class="hs"><div class="hs-n">3.4<sup>K</sup></div><div class="hs-l">Active Writers</div></div>
        </div>
    </div>

    <div class="r-ticker">
        <span class="ticker-badge">Breaking</span>
        <div class="ticker-track">
            <span class="ticker-item">Climate Summit Agreement Signed</span>
            <span class="ticker-item">AI Medical Breakthrough</span>
            <span class="ticker-item">Fed Rate Cut Signals</span>
            <span class="ticker-item">Lunar Water Ice Confirmed</span>
            <span class="ticker-item">Infrastructure Bill Passed</span>
            <span class="ticker-item">OPEC+ Output Decision Due</span>
            <span class="ticker-item">Tech Antitrust Ruling Today</span>
            <span class="ticker-item">Climate Summit Agreement Signed</span>
            <span class="ticker-item">AI Medical Breakthrough</span>
            <span class="ticker-item">Fed Rate Cut Signals</span>
            <span class="ticker-item">Lunar Water Ice Confirmed</span>
            <span class="ticker-item">Infrastructure Bill Passed</span>
            <span class="ticker-item">OPEC+ Output Decision Due</span>
            <span class="ticker-item">Tech Antitrust Ruling Today</span>
        </div>
    </div>
</div>

<script src="/public/js/app.js"></script>
<script>
function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
        input.type = 'password';
        icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
}
document.querySelectorAll('.chip').forEach(c => {
    c.addEventListener('click', () => {
        document.querySelectorAll('.chip').forEach(x => x.classList.remove('active'));
        c.classList.add('active');
    });
});
</script>
</body>
</html>
