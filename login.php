<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>SharedSpace – Sign In</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --navy:       #0d1b2e;
    --navy-card:  #112240;
    --accent:     #f5a623;
    --accent2:    #e8952a;
    --muted:      #8899aa;
    --white:      #ffffff;
    --offwhite:   #f7f8fa;
    --border-l:   #e5e9f0;
    --text-dark:  #12233a;
    --text-mid:   #4a5a72;
  }

  html, body { height: 100%; font-family: 'DM Sans', sans-serif; }

  body { display: flex; height: 100vh; overflow: hidden; }

  /* LEFT */
  .left {
    width: 42%; min-width: 380px;
    background: var(--white);
    display: flex; flex-direction: column; justify-content: center;
    padding: 60px 64px;
    position: relative; z-index: 2;
    box-shadow: 4px 0 40px rgba(13,27,46,0.10);
  }
  .left::before {
    content: ''; position: absolute;
    top: 0; left: 0; right: 0; height: 4px;
    background: linear-gradient(90deg, var(--accent), var(--accent2));
  }

  .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 48px; }
  .brand-icon {
    width: 34px; height: 34px; background: var(--navy);
    border-radius: 7px; display: grid; place-items: center;
  }
  .brand-name { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--text-dark); }

  .left h1 { font-family: 'Playfair Display', serif; font-size: 2.1rem; color: var(--text-dark); margin-bottom: 8px; }
  .left .subtitle { font-size: 0.88rem; color: var(--text-mid); margin-bottom: 36px; }

  label { display: block; font-size: 0.78rem; font-weight: 600; color: var(--text-dark); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 7px; }

  .input-wrap { position: relative; margin-bottom: 20px; }
  .input-wrap svg.icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
  .input-wrap input {
    width: 100%; padding: 13px 14px 13px 40px;
    border: 1.5px solid var(--border-l); border-radius: 10px;
    font-size: 0.92rem; font-family: 'DM Sans', sans-serif;
    color: var(--text-dark); background: var(--offwhite); outline: none;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
  }
  .input-wrap input:focus {
    border-color: var(--accent); background: var(--white);
    box-shadow: 0 0 0 3px rgba(245,166,35,0.13);
  }
  .input-wrap input::placeholder { color: #aab4c4; }

  .eye-btn {
    position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
    background: none; border: none; cursor: pointer; color: var(--muted);
    display: grid; place-items: center; padding: 2px;
  }
  .eye-btn:hover { color: var(--text-dark); }

  .btn-signin {
    width: 100%; padding: 14px; border: none; border-radius: 10px;
    background: linear-gradient(135deg, var(--accent) 0%, var(--accent2) 100%);
    color: var(--white); font-family: 'DM Sans', sans-serif;
    font-size: 0.97rem; font-weight: 600; cursor: pointer; margin-top: 6px;
    transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
    box-shadow: 0 4px 18px rgba(245,166,35,0.30);
  }
  .btn-signin:hover { opacity: 0.93; transform: translateY(-1px); box-shadow: 0 7px 22px rgba(245,166,35,0.38); }

  .register-cta { text-align: center; margin-top: 24px; font-size: 0.85rem; color: var(--text-mid); }
  .register-cta a { color: var(--accent2); font-weight: 600; text-decoration: none; }
  .register-cta a:hover { text-decoration: underline; }

  /* RIGHT */
  .right {
    flex: 1; background: var(--navy);
    position: relative; overflow: hidden;
    display: flex; flex-direction: column; justify-content: flex-end;
    padding: 48px 52px;
  }
  .right::before {
    content: ''; position: absolute; top: -120px; right: -120px;
    width: 520px; height: 520px;
    background: radial-gradient(circle, rgba(78,205,196,0.07) 0%, transparent 70%);
    pointer-events: none;
  }

  .cards-area { position: absolute; top: 32px; left: 0; right: 0; padding: 0 40px; display: flex; flex-direction: column; gap: 16px; }
  .news-card {
    background: #112240; border: 1px solid rgba(255,255,255,0.06); border-radius: 14px;
    padding: 18px 22px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
    animation: floatUp 0.7s ease both;
  }
  .news-card:nth-child(1) { animation-delay: 0.05s; }
  .news-card:nth-child(2) { animation-delay: 0.18s; margin-left: 48px; }
  .news-card:nth-child(3) { animation-delay: 0.31s; }
  .news-card:nth-child(4) { animation-delay: 0.44s; margin-left: 32px; }
  @keyframes floatUp { from { opacity: 0; transform: translateY(18px); } to { opacity: 1; transform: translateY(0); } }

  .card-tag { font-size: 0.65rem; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase; color: var(--accent); margin-bottom: 6px; }
  .card-title { font-size: 0.88rem; font-weight: 600; color: #dce8f5; line-height: 1.4; }
  .card-meta { display: flex; align-items: center; gap: 6px; margin-top: 8px; font-size: 0.72rem; color: var(--muted); }
  .card-dot { width: 6px; height: 6px; border-radius: 50%; background: #4ecdc4; flex-shrink: 0; }
  .trust-badge {
    flex-shrink: 0; display: flex; align-items: center; gap: 4px;
    background: rgba(78,205,196,0.12); color: #4ecdc4;
    font-size: 0.75rem; font-weight: 700; padding: 5px 10px; border-radius: 20px;
    white-space: nowrap; align-self: flex-start; margin-top: 2px;
  }

  .hero-text { position: relative; z-index: 2; margin-bottom: 10px; }
  .hero-eyebrow { width: 32px; height: 3px; background: var(--accent); border-radius: 2px; margin-bottom: 14px; }
  .hero-text h2 { font-family: 'Playfair Display', serif; font-size: 2.4rem; color: #fff; line-height: 1.2; margin-bottom: 12px; }
  .hero-text p { font-size: 0.88rem; color: var(--muted); line-height: 1.6; max-width: 340px; }

  .stats { display: flex; gap: 36px; margin-top: 28px; position: relative; z-index: 2; }
  .stat-val { font-family: 'Playfair Display', serif; font-size: 1.6rem; color: #fff; }
  .stat-lbl { font-size: 0.68rem; font-weight: 600; letter-spacing: 0.8px; text-transform: uppercase; color: var(--muted); margin-top: 2px; }

  .ticker {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(255,255,255,0.04); border-top: 1px solid rgba(255,255,255,0.06);
    padding: 10px 24px; display: flex; align-items: center; gap: 14px; overflow: hidden;
  }
  .ticker-live { font-size: 0.65rem; font-weight: 800; letter-spacing: 1px; color: var(--accent); text-transform: uppercase; flex-shrink: 0; background: rgba(245,166,35,0.13); padding: 3px 8px; border-radius: 4px; }
  .ticker-track { display: flex; gap: 40px; animation: ticker 28s linear infinite; white-space: nowrap; }
  @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
  .ticker-item { font-size: 0.75rem; color: var(--muted); }
  .ticker-item::before { content: '·'; margin-right: 12px; color: rgba(255,255,255,0.2); }
</style>
</head>
<body>

<!-- LEFT -->
<div class="left">
  <div class="brand">
    <div class="brand-icon">
      <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
        <rect x="2" y="2" width="6" height="6" rx="1.5" fill="#f5a623"/>
        <rect x="10" y="2" width="6" height="6" rx="1.5" fill="rgba(255,255,255,0.4)"/>
        <rect x="2" y="10" width="6" height="6" rx="1.5" fill="rgba(255,255,255,0.4)"/>
        <rect x="10" y="10" width="6" height="6" rx="1.5" fill="#f5a623" opacity="0.6"/>
      </svg>
    </div>
    <span class="brand-name">SharedSpace</span>
  </div>

  <h1>Welcome back</h1>
  <p class="subtitle">Sign in to continue to your dashboard</p>

  <!-- YOUR PHP ERROR MESSAGE HERE -->
  <?php if (!empty($error)): ?>
    <p style="color:red; margin-bottom:16px;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <form method="POST" action="/login.php">
    <label for="email">Email</label>
    <div class="input-wrap">
      <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>
      </svg>
      <input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required/>
    </div>

    <label for="password">Password</label>
    <div class="input-wrap">
      <svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
      </svg>
      <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required/>
      <button type="button" class="eye-btn" onclick="togglePw()" aria-label="Toggle password">
        <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
        </svg>
      </button>
    </div>

    <button type="submit" class="btn-signin">Sign In</button>
  </form>

  <p class="register-cta">Don't have an account? <a href="/register.php">Sign up free</a></p>
</div>

<!-- RIGHT -->
<div class="right">
  <div class="cards-area">
    <div class="news-card">
      <div class="card-left">
        <div class="card-tag">Breaking · World</div>
        <div class="card-title">Global Leaders Reach Historic Climate Agreement at Summit</div>
        <div class="card-meta"><span class="card-dot"></span>2 min ago · Reuters</div>
      </div>
      <div class="trust-badge">✓ 94%</div>
    </div>
    <div class="news-card">
      <div class="card-left">
        <div class="card-tag">Technology</div>
        <div class="card-title">AI Breakthrough Enables Real-Time Medical Diagnosis</div>
        <div class="card-meta"><span class="card-dot"></span>14 min ago · Tech Review</div>
      </div>
      <div class="trust-badge">✓ 89%</div>
    </div>
    <div class="news-card">
      <div class="card-left">
        <div class="card-tag">Economics</div>
        <div class="card-title">Central Banks Signal Rate Cuts as Inflation Eases</div>
        <div class="card-meta"><span class="card-dot"></span>1 hr ago · Financial Times</div>
      </div>
      <div class="trust-badge">✓ 91%</div>
    </div>
    <div class="news-card">
      <div class="card-left">
        <div class="card-tag">Science</div>
        <div class="card-title">NASA Confirms Water Ice Discovery on Lunar Surface</div>
        <div class="card-meta"><span class="card-dot"></span>3 hr ago · NASA</div>
      </div>
      <div class="trust-badge">✓ 97%</div>
    </div>
  </div>

  <div class="hero-text">
    <div class="hero-eyebrow"></div>
    <h2>Truth in Every<br>Headline.</h2>
    <p>Join thousands of journalists and readers who trust SharedSpace for verified, fact-checked news.</p>
  </div>

  <div class="stats">
    <div><div class="stat-val">12K+</div><div class="stat-lbl">Verified Articles</div></div>
    <div><div class="stat-val">94%</div><div class="stat-lbl">Avg Trust Score</div></div>
    <div><div class="stat-val">3.4K</div><div class="stat-lbl">Active Writers</div></div>
  </div>

  <div class="ticker">
    <span class="ticker-live">Live</span>
    <div class="ticker-track">
      <span class="ticker-item">Climate Summit Agreement</span>
      <span class="ticker-item">AI Medical Breakthrough</span>
      <span class="ticker-item">Rate Cut Signals</span>
      <span class="ticker-item">Lunar Water Ice Confirmed</span>
      <span class="ticker-item">Infrastructure Bill Passed</span>
      <span class="ticker-item">Climate Summit Agreement</span>
      <span class="ticker-item">AI Medical Breakthrough</span>
      <span class="ticker-item">Rate Cut Signals</span>
      <span class="ticker-item">Lunar Water Ice Confirmed</span>
      <span class="ticker-item">Infrastructure Bill Passed</span>
    </div>
  </div>
</div>

<script>
  function togglePw() {
    const input = document.getElementById('password');
    const icon  = document.getElementById('eye-icon');
    if (input.type === 'password') {
      input.type = 'text';
      icon.innerHTML = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/>`;
    } else {
      input.type = 'password';
      icon.innerHTML = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
    }
  }
</script>
</body>
</html>