<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();

// Redirect to dashboard if already logged in
if ($auth->currentUser()) {
    header('Location: /dashboard.php');
    exit;
}

$error = null;

// Handle POST login
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
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Sign In – SharedSpace</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Geist:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:        #0a0f1e;
  --ink-muted:  #3d4f6e;
  --slate:      #64748b;
  --silver:     #94a3b8;
  --fog:        #e2e8f0;
  --snow:       #f8fafc;
  --white:      #ffffff;
  --amber:      #f59e0b;
  --amber-d:    #d97706;
  --amber-glow: rgba(245,158,11,0.15);
  --navy:       #0b1628;
  --card-border: rgba(255,255,255,0.065);
  --success:    #10b981;
  --danger:     #ef4444;
}

html, body { height: 100%; overflow: hidden; }

body {
  display: flex;
  font-family: 'Geist', -apple-system, sans-serif;
  font-size: 14px;
  -webkit-font-smoothing: antialiased;
}

/* ══════════════════════
   LEFT — Form Panel
══════════════════════ */
.panel-left {
  width: 480px;
  flex-shrink: 0;
  background: var(--white);
  display: flex;
  flex-direction: column;
  position: relative;
  overflow: hidden;
}

/* amber top bar */
.panel-left::after {
  content: '';
  position: absolute;
  top: 0; left: 0; right: 0; height: 3px;
  background: linear-gradient(90deg, var(--amber), var(--amber-d) 60%, transparent);
  z-index: 10;
}

.left-inner {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 0 56px;
}

/* Brand */
.brand {
  display: flex; align-items: center; gap: 10px;
  margin-bottom: 48px;
  text-decoration: none;
}

.brand-mark {
  width: 32px; height: 32px;
  background: var(--ink);
  border-radius: 8px;
  position: relative;
  flex-shrink: 0;
}
.brand-mark::before {
  content: '';
  position: absolute; inset: 3px;
  background:
    linear-gradient(135deg, var(--amber) 0 50%, transparent 50%) top left / 10px 10px no-repeat,
    linear-gradient(135deg, transparent 0 50%, rgba(255,255,255,0.2) 50%) top right / 10px 10px no-repeat,
    linear-gradient(135deg, rgba(255,255,255,0.2) 0 50%, transparent 50%) bottom left / 10px 10px no-repeat,
    linear-gradient(135deg, transparent 0 50%, var(--amber) 50%) bottom right / 10px 10px no-repeat;
}
.brand-name {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: 18px;
  color: var(--ink);
  letter-spacing: -0.2px;
}

/* Heading */
.heading-block { margin-bottom: 28px; }
.heading-block h1 {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: 30px; font-weight: 400;
  color: var(--ink);
  letter-spacing: -0.7px; line-height: 1.15;
  margin-bottom: 5px;
}
.heading-block p {
  font-size: 13.5px; color: var(--slate); line-height: 1.5;
}

/* Alerts */
.alert {
  display: flex; align-items: flex-start; gap: 9px;
  border-radius: 9px; padding: 11px 13px;
  margin-bottom: 18px; font-size: 13px; line-height: 1.45;
  border: 1px solid;
}
.alert-error {
  background: #fef2f2; border-color: #fecaca; color: #b91c1c;
}
.alert-success {
  background: #f0fdf4; border-color: #bbf7d0; color: #166534;
}
.alert svg { flex-shrink: 0; margin-top: 1px; }

/* Form fields */
.form-group { margin-bottom: 18px; }

.form-row-between {
  display: flex; align-items: center;
  justify-content: space-between; margin-bottom: 6px;
}

.form-group label,
.form-row-between label {
  display: block;
  font-size: 11.5px; font-weight: 600;
  color: var(--ink-muted);
  letter-spacing: 0.05em; text-transform: uppercase;
}

.forgot-link {
  font-size: 12px; color: var(--amber-d);
  font-weight: 500; text-decoration: none;
}
.forgot-link:hover { text-decoration: underline; }

.input-shell {
  position: relative; display: flex; align-items: center;
}
.input-shell .pre-icon {
  position: absolute; left: 13px;
  pointer-events: none; color: var(--silver);
  display: flex; align-items: center;
}
.input-shell input {
  width: 100%; height: 46px;
  padding: 0 44px;
  border: 1.5px solid var(--fog); border-radius: 10px;
  font-size: 14px; font-family: 'Geist', sans-serif;
  color: var(--ink); background: var(--snow);
  outline: none;
  transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
}
.input-shell input::placeholder { color: #b0bdd0; font-weight: 300; }
.input-shell input:hover { border-color: #c8d4e3; background: var(--white); }
.input-shell input:focus {
  border-color: var(--amber); background: var(--white);
  box-shadow: 0 0 0 3.5px var(--amber-glow);
}

.eye-btn {
  position: absolute; right: 12px;
  background: none; border: none; cursor: pointer;
  color: var(--silver); display: flex; align-items: center;
  padding: 4px; border-radius: 4px;
  transition: color 0.15s;
}
.eye-btn:hover { color: var(--ink-muted); }

/* Submit */
.btn-submit {
  width: 100%; height: 48px;
  border: none; border-radius: 10px;
  background: var(--ink); color: var(--white);
  font-family: 'Geist', sans-serif;
  font-size: 14px; font-weight: 600; letter-spacing: 0.02em;
  cursor: pointer; margin-top: 8px;
  position: relative; overflow: hidden;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: transform 0.15s, box-shadow 0.15s;
}
.btn-submit::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(245,158,11,0.18) 0%, transparent 60%);
  opacity: 0; transition: opacity 0.2s;
}
.btn-submit:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(10,15,30,0.18); }
.btn-submit:hover::before { opacity: 1; }
.btn-submit:active { transform: translateY(0); }

.btn-arrow { transition: transform 0.2s; }
.btn-submit:hover .btn-arrow { transform: translateX(3px); }

/* Divider */
.or-divider {
  display: flex; align-items: center; gap: 12px; margin: 18px 0;
}
.or-divider::before, .or-divider::after {
  content: ''; flex: 1; height: 1px; background: var(--fog);
}
.or-divider span { font-size: 11.5px; color: var(--silver); font-weight: 500; letter-spacing: 0.04em; }

/* SSO */
.sso-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.btn-sso {
  height: 42px;
  border: 1.5px solid var(--fog); border-radius: 9px;
  background: var(--white);
  font-family: 'Geist', sans-serif; font-size: 13px; font-weight: 500;
  color: var(--ink-muted); cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  transition: border-color 0.15s, box-shadow 0.15s, background 0.15s;
}
.btn-sso:hover { border-color: #c8d4e3; background: var(--snow); box-shadow: 0 2px 8px rgba(0,0,0,0.06); }

/* Footer */
.left-footer {
  padding: 22px 56px;
  border-top: 1px solid var(--fog);
  display: flex; align-items: center; justify-content: space-between;
}
.signup-cta { font-size: 13px; color: var(--slate); }
.signup-cta a {
  color: var(--ink); font-weight: 600; text-decoration: none;
  border-bottom: 1.5px solid var(--amber); padding-bottom: 1px;
  transition: color 0.15s;
}
.signup-cta a:hover { color: var(--amber-d); }

.status-pill {
  display: flex; align-items: center; gap: 6px;
  background: var(--snow); border: 1px solid var(--fog);
  border-radius: 20px; padding: 5px 11px 5px 8px;
  font-size: 11.5px; color: var(--slate); font-weight: 500;
}
.status-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: var(--success);
  box-shadow: 0 0 0 2.5px rgba(16,185,129,0.2);
  animation: pulse-green 2s infinite;
}
@keyframes pulse-green {
  0%,100% { box-shadow: 0 0 0 2.5px rgba(16,185,129,0.2); }
  50%      { box-shadow: 0 0 0 5px rgba(16,185,129,0.07); }
}

/* ══════════════════════
   RIGHT — Brand Panel
══════════════════════ */
.panel-right {
  flex: 1; background: var(--navy);
  position: relative; overflow: hidden;
  display: flex; flex-direction: column;
}

.bg-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.024) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.024) 1px, transparent 1px);
  background-size: 48px 48px;
  mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 100%);
}
.bg-glow-teal {
  position: absolute; top: -180px; right: -80px;
  width: 580px; height: 580px;
  background: radial-gradient(circle, rgba(13,148,136,0.11) 0%, transparent 65%);
  pointer-events: none;
}
.bg-glow-amber {
  position: absolute; bottom: -80px; left: -160px;
  width: 480px; height: 480px;
  background: radial-gradient(circle, rgba(245,158,11,0.07) 0%, transparent 65%);
  pointer-events: none;
}

/* Top bar */
.r-topbar {
  position: relative; z-index: 10;
  display: flex; align-items: center; justify-content: space-between;
  padding: 20px 40px;
  border-bottom: 1px solid var(--card-border);
}
.live-badge { display: flex; align-items: center; gap: 8px; }
.live-dot {
  width: 7px; height: 7px; border-radius: 50%;
  background: #f87171;
  box-shadow: 0 0 0 3px rgba(248,113,113,0.2);
  animation: pulse-red 1.8s infinite;
}
@keyframes pulse-red {
  0%,100% { box-shadow: 0 0 0 3px rgba(248,113,113,0.2); }
  50%      { box-shadow: 0 0 0 6px rgba(248,113,113,0.05); }
}
.live-text { font-size: 11px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: #f87171; }

.r-stats { display: flex; align-items: center; gap: 20px; }
.r-stat { text-align: right; }
.r-stat-val { font-size: 13px; font-weight: 600; color: rgba(255,255,255,0.85); line-height: 1; }
.r-stat-lbl { font-size: 10px; font-weight: 500; letter-spacing: 0.06em; text-transform: uppercase; color: rgba(255,255,255,0.3); margin-top: 2px; }
.r-divider { width: 1px; height: 28px; background: var(--card-border); }

/* Feed */
.r-feed {
  flex: 1; position: relative; z-index: 5;
  padding: 24px 36px 0;
  display: flex; flex-direction: column; gap: 11px;
  overflow: hidden;
}

.feed-hdr {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 4px;
}
.feed-lbl { font-size: 11px; font-weight: 600; letter-spacing: 0.08em; text-transform: uppercase; color: rgba(255,255,255,0.28); }
.feed-chips { display: flex; gap: 6px; }
.chip {
  font-size: 11px; font-weight: 500; color: rgba(255,255,255,0.32);
  background: rgba(255,255,255,0.05); border: 1px solid var(--card-border);
  border-radius: 20px; padding: 3px 10px; cursor: pointer; transition: all 0.15s;
}
.chip.active { color: var(--amber); background: rgba(245,158,11,0.1); border-color: rgba(245,158,11,0.25); }

/* Article cards */
.a-card {
  background: rgba(255,255,255,0.035);
  border: 1px solid var(--card-border); border-radius: 13px;
  padding: 14px 16px;
  display: flex; gap: 14px; align-items: flex-start;
  cursor: pointer; position: relative; overflow: hidden;
  transition: background 0.18s, border-color 0.18s, transform 0.18s;
  animation: fadeUp 0.5s cubic-bezier(0.22,1,0.36,1) both;
}
.a-card::before {
  content: ''; position: absolute; left: 0; top: 0; bottom: 0;
  width: 3px; background: var(--amber); opacity: 0; transition: opacity 0.18s;
}
.a-card:hover { background: rgba(255,255,255,0.055); border-color: rgba(255,255,255,0.1); transform: translateX(3px); }
.a-card:hover::before { opacity: 1; }
.a-card:nth-child(2) { animation-delay: 0.07s; }
.a-card:nth-child(3) { animation-delay: 0.14s; }
.a-card:nth-child(4) { animation-delay: 0.21s; }
.a-card:nth-child(5) { animation-delay: 0.28s; }
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(14px); }
  to   { opacity: 1; transform: translateY(0); }
}

.a-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; margin-top: 5px; }
.a-body { flex: 1; min-width: 0; }
.a-meta { display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
.a-cat { font-size: 10px; font-weight: 700; letter-spacing: 0.09em; text-transform: uppercase; }
.a-sep { font-size: 10px; color: rgba(255,255,255,0.18); }
.a-time { font-size: 10px; color: rgba(255,255,255,0.27); }
.a-title { font-size: 13px; font-weight: 500; color: rgba(255,255,255,0.82); line-height: 1.45; letter-spacing: -0.1px; }
.a-src { font-size: 11px; color: rgba(255,255,255,0.28); margin-top: 4px; }
.a-right { flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 6px; }

.trust {
  display: flex; align-items: center; gap: 3px;
  padding: 3px 9px; border-radius: 20px;
  font-size: 11px; font-weight: 700;
}
.trust.hi  { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(52,211,153,0.2); }
.trust.mid { background: rgba(245,158,11,0.12);  color: #fbbf24; border: 1px solid rgba(251,191,36,0.2);  }

/* Bottom hero */
.r-hero {
  position: relative; z-index: 5;
  padding: 24px 36px;
  border-top: 1px solid var(--card-border);
  display: flex; align-items: flex-end; justify-content: space-between; gap: 20px;
}
.hero-eyebrow { display: flex; align-items: center; gap: 8px; margin-bottom: 9px; }
.hero-line { width: 22px; height: 2px; background: var(--amber); border-radius: 2px; }
.hero-eyebrow-lbl { font-size: 10px; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--amber); }

.hero-copy h2 {
  font-family: 'Instrument Serif', Georgia, serif;
  font-size: 28px; font-weight: 400; color: var(--white);
  line-height: 1.2; letter-spacing: -0.4px; margin-bottom: 7px;
}
.hero-copy h2 em { font-style: italic; color: rgba(255,255,255,0.52); }
.hero-copy p { font-size: 13px; color: rgba(255,255,255,0.36); line-height: 1.6; max-width: 290px; }

.hero-stats { display: flex; gap: 26px; flex-shrink: 0; }
.hs { text-align: right; }
.hs-n { font-family: 'Instrument Serif', Georgia, serif; font-size: 24px; color: var(--white); line-height: 1; letter-spacing: -0.4px; }
.hs-n sup { font-family: 'Geist', sans-serif; font-size: 12px; font-weight: 600; color: var(--amber); vertical-align: super; }
.hs-l { font-size: 9.5px; font-weight: 600; letter-spacing: 0.07em; text-transform: uppercase; color: rgba(255,255,255,0.24); margin-top: 3px; }

/* Ticker */
.r-ticker {
  position: relative; z-index: 5;
  background: rgba(0,0,0,0.32); border-top: 1px solid var(--card-border);
  height: 36px; padding: 0 20px;
  display: flex; align-items: center; gap: 14px; overflow: hidden;
}
.ticker-badge {
  font-size: 9px; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;
  color: var(--amber); flex-shrink: 0;
  background: rgba(245,158,11,0.12); border: 1px solid rgba(245,158,11,0.22);
  padding: 2px 8px; border-radius: 3px;
}
.ticker-track {
  display: flex; gap: 44px;
  animation: ticker-anim 32s linear infinite;
  white-space: nowrap;
}
@keyframes ticker-anim {
  from { transform: translateX(0); }
  to   { transform: translateX(-50%); }
}
.ticker-item { font-size: 11.5px; color: rgba(255,255,255,0.3); display: flex; align-items: center; gap: 8px; }
.ticker-item::before { content: ''; width: 4px; height: 4px; border-radius: 50%; background: rgba(255,255,255,0.14); }

/* Category colours */
.c-world { color: #60a5fa; } .d-world { background: #60a5fa; }
.c-tech  { color: #a78bfa; } .d-tech  { background: #a78bfa; }
.c-econ  { color: #34d399; } .d-econ  { background: #34d399; }
.c-sci   { color: #38bdf8; } .d-sci   { background: #38bdf8; }
.c-geo   { color: #fb923c; } .d-geo   { background: #fb923c; }

/* Responsive */
@media (max-width: 860px) {
  .panel-right { display: none; }
  .panel-left  { width: 100%; }
}
</style>
</head>
<body>

<!-- ══════════════════════════
     LEFT — Login Form
══════════════════════════ -->
<div class="panel-left">
  <div class="left-inner">

    <a href="/" class="brand">
      <div class="brand-mark"></div>
      <span class="brand-name">SharedSpace</span>
    </a>

    <div class="heading-block">
      <h1>Welcome back</h1>
      <p>Sign in to continue to your dashboard</p>
    </div>

    <?php if ($error): ?>
    <div class="alert alert-error">
      <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
      </svg>
      <?= htmlspecialchars($error) ?>
    </div>
    <?php endif; ?>

    <?php $successMsg = flash('flash_success'); if ($successMsg): ?>
    <div class="alert alert-success">
      <svg width="15" height="15" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
      </svg>
      <?= htmlspecialchars($successMsg) ?>
    </div>
    <?php endif; ?>

    <form method="POST" autocomplete="on">

      <div class="form-group">
        <label for="email">Email address</label>
        <div class="input-shell">
          <span class="pre-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
          </span>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="you@example.com"
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
            autocomplete="email"
            required
          />
        </div>
      </div>

      <div class="form-group">
        <div class="form-row-between">
          <label for="password">Password</label>
          <a href="/forgot-password.php" class="forgot-link">Forgot password?</a>
        </div>
        <div class="input-shell">
          <span class="pre-icon">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
          </span>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
          <button type="button" class="eye-btn" onclick="togglePw()" aria-label="Toggle password">
            <svg id="eyeIcon" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
              <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
              <circle cx="12" cy="12" r="3"/>
            </svg>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        Sign In
        <svg class="btn-arrow" width="16" height="16" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 8h10M9 4l4 4-4 4"/>
        </svg>
      </button>

    </form>

    <div class="or-divider"><span>or continue with</span></div>

    <div class="sso-row">
      <button type="button" class="btn-sso">
        <svg width="16" height="16" viewBox="0 0 24 24">
          <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
          <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
          <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="#FBBC05"/>
          <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
        </svg>
        Google
      </button>
      <button type="button" class="btn-sso">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12 0C5.374 0 0 5.373 0 12c0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23A11.509 11.509 0 0 1 12 5.803c1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576C20.566 21.797 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
        </svg>
        GitHub
      </button>
    </div>

  </div>

  <div class="left-footer">
    <p class="signup-cta">Don't have an account? <a href="/register.php">Sign up free</a></p>
    <div class="status-pill">
      <span class="status-dot"></span>
      All systems normal
    </div>
  </div>
</div>


<!-- ══════════════════════════
     RIGHT — Brand / Feed
══════════════════════════ -->
<div class="panel-right">
  <div class="bg-grid"></div>
  <div class="bg-glow-teal"></div>
  <div class="bg-glow-amber"></div>

  <div class="r-topbar">
    <div class="live-badge">
      <span class="live-dot"></span>
      <span class="live-text">Live feed</span>
    </div>
    <div class="r-stats">
      <div class="r-stat">
        <div class="r-stat-val">1,284</div>
        <div class="r-stat-lbl">Articles today</div>
      </div>
      <div class="r-divider"></div>
      <div class="r-stat">
        <div class="r-stat-val">94.2%</div>
        <div class="r-stat-lbl">Avg trust score</div>
      </div>
      <div class="r-divider"></div>
      <div class="r-stat">
        <div class="r-stat-val">3.4K</div>
        <div class="r-stat-lbl">Active writers</div>
      </div>
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

    <div class="a-card">
      <span class="a-dot d-world"></span>
      <div class="a-body">
        <div class="a-meta"><span class="a-cat c-world">World</span><span class="a-sep">·</span><span class="a-time">2 min ago</span></div>
        <div class="a-title">Global Leaders Reach Historic Climate Agreement at Summit in Geneva</div>
        <div class="a-src">Reuters · International Affairs</div>
      </div>
      <div class="a-right"><div class="trust hi">✓ 94%</div></div>
    </div>

    <div class="a-card">
      <span class="a-dot d-tech"></span>
      <div class="a-body">
        <div class="a-meta"><span class="a-cat c-tech">Technology</span><span class="a-sep">·</span><span class="a-time">14 min ago</span></div>
        <div class="a-title">AI Breakthrough Enables Real-Time Medical Diagnosis at Hospital Scale</div>
        <div class="a-src">Tech Review · Health & AI</div>
      </div>
      <div class="a-right"><div class="trust hi">✓ 89%</div></div>
    </div>

    <div class="a-card">
      <span class="a-dot d-econ"></span>
      <div class="a-body">
        <div class="a-meta"><span class="a-cat c-econ">Economics</span><span class="a-sep">·</span><span class="a-time">1 hr ago</span></div>
        <div class="a-title">Central Banks Signal Coordinated Rate Cuts as Inflation Eases</div>
        <div class="a-src">Financial Times · Monetary Policy</div>
      </div>
      <div class="a-right"><div class="trust hi">✓ 91%</div></div>
    </div>

    <div class="a-card">
      <span class="a-dot d-sci"></span>
      <div class="a-body">
        <div class="a-meta"><span class="a-cat c-sci">Science</span><span class="a-sep">·</span><span class="a-time">3 hr ago</span></div>
        <div class="a-title">NASA Confirms Substantial Water Ice Deposits on Lunar South Pole</div>
        <div class="a-src">NASA · Space Exploration</div>
      </div>
      <div class="a-right"><div class="trust hi">✓ 97%</div></div>
    </div>

    <div class="a-card">
      <span class="a-dot d-geo"></span>
      <div class="a-body">
        <div class="a-meta"><span class="a-cat c-geo">Geopolitics</span><span class="a-sep">·</span><span class="a-time">5 hr ago</span></div>
        <div class="a-title">Infrastructure Bill Passes Senate With Bipartisan Support</div>
        <div class="a-src">AP News · US Politics</div>
      </div>
      <div class="a-right"><div class="trust mid">✓ 86%</div></div>
    </div>
  </div>

  <div class="r-hero">
    <div class="hero-copy">
      <div class="hero-eyebrow">
        <div class="hero-line"></div>
        <span class="hero-eyebrow-lbl">Trusted Journalism</span>
      </div>
      <h2>Truth in <em>every</em><br>headline.</h2>
      <p>Join thousands of journalists and readers who trust SharedSpace for AI-verified, fact-checked news.</p>
    </div>
    <div class="hero-stats">
      <div class="hs">
        <div class="hs-n">12<sup>K+</sup></div>
        <div class="hs-l">Verified Articles</div>
      </div>
      <div class="hs">
        <div class="hs-n">94<sup>%</sup></div>
        <div class="hs-l">Trust Score</div>
      </div>
      <div class="hs">
        <div class="hs-n">3.4<sup>K</sup></div>
        <div class="hs-l">Active Writers</div>
      </div>
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