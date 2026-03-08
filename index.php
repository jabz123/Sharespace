<?php
//landing page
<!-- CI/CD test -->
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/ArticleController.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();

//skip landing page if logged in
if ($auth->currentUser()) {
    header('Location: /dashboard.php');
    exit;
}

//fetch 3 most recent articles for preview cards, also shows loading skeletons if query is slow
$previewArticles = $articleCtrl->getPreview(3);


//assigns css class based on trust score, only visual
function score_class(int $score): string {
    if ($score >= 80) return 'score-high';
    if ($score >= 60) return 'score-mid';
    return 'score-low';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SharedSpace – Truth in Every Headline</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Merriweather:wght@400;700;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/css/landing.css" />
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">
            <div class="logo-icon-box">📰</div>
            <span>SharedSpace</span>
        </a>
        <div class="nav-links" id="nav-links">
            <a href="#features">Features</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#testimonials">Testimonials</a>
            <a href="#pricing">Pricing</a>
        </div>
        <div class="nav-auth">
            <a href="/login.php" class="btn-ghost-nav">Sign In</a>
            <a href="/register.php" class="btn-hero-nav">Get Started</a>
        </div>
        <button class="hamburger" id="hamburger"><span></span><span></span><span></span></button>
    </div>
    <div class="mobile-menu" id="mobile-menu">
        <a href="#features">Features</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#testimonials">Testimonials</a>
        <a href="#pricing">Pricing</a>
        <div class="mobile-auth">
            <a href="/login.php" class="btn-ghost-nav">Sign In</a>
            <a href="/register.php" class="btn-hero-nav">Get Started</a>
        </div>
    </div>
</nav>

<section class="hero">
    <div class="blob blob-right"></div>
    <div class="blob blob-left"></div>
    <div class="container hero-inner">
        <div class="hero-badge fade-in"><span>🛡</span> AI-Powered Fact Checking</div>
        <h1 class="hero-title slide-up">Truth in Every<br><span class="gradient-text">Headline</span></h1>
        <p class="hero-sub slide-up" style="animation-delay:.1s">
            Join the platform where news is verified, trusted, and shared responsibly.
            Our AI analyses every article for accuracy before it reaches you.
        </p>
        <div class="hero-cta slide-up" style="animation-delay:.1s">
            <a href="/register.php" class="btn-hero-lg">Start Publishing Free</a>
            <a href="#video" class="btn-outline-lg"><span class="play-icon">▶</span> Watch Demo</a>
        </div>
       
        <div class="preview-window slide-up" style="animation-delay:.1s">

            <div class="preview-cards">
                <?php if (!empty($previewArticles)):
                    foreach ($previewArticles as $article): ?>
                <a href="/login.php" class="preview-card">
                    <div class="preview-thumb"></div>
                    <div class="preview-meta">
                        <span class="preview-score <?= score_class($article->trustScore) ?>"><?= $article->trustScore ?>% Verified</span>
                        <span class="preview-cat"><?= htmlspecialchars($article->categoryName) ?></span>
                    </div>
                    <h3 class="preview-title"><?= htmlspecialchars(mb_substr($article->title, 0, 60)) ?><?= mb_strlen($article->title) > 60 ? '…' : '' ?></h3>
                    <p class="preview-excerpt"><?= htmlspecialchars(mb_substr($article->excerpt, 0, 80)) ?>…</p>
                </a>
                <?php endforeach; else:
                    for ($i = 0; $i < 3; $i++): ?>
                <div class="preview-card skeleton">
                    <div class="preview-thumb pulse"></div>
                    <div class="skel-line w-half pulse"></div>
                    <div class="skel-line w-full pulse"></div>
                    <div class="skel-line w-three-quarters pulse"></div>
                </div>
                <?php endfor; endif; ?>
            </div>
        </div>
    </div>
</section>

<section id="features" class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Built for Trusted Journalism</h2>
            <p>Everything you need to publish, verify, and share news with confidence.</p>
        </div>
        <div class="features-grid">
            <?php foreach ([
                ['🛡','AI Fact-Checking','Every article is analysed by our advanced AI to verify claims and provide a confidence score before publication.'],
                ['⚡','Real-Time Publishing','Share your verified news instantly with our streamlined publishing workflow and instant distribution.'],
                ['👥','Community Comments','Readers and writers can comment on every article, fostering open discussion around verified news.'],
                ['📊','Trust Analytics','Track your credibility score over time and see how your articles perform in trust metrics.'],
                ['🔒','Secure Accounts','Your account and data are protected with industry-standard security practices.'],
                ['🌐','Multi-Category Support','Organise content across technology, politics, science, sports, and more with dedicated category management.'],
            ] as [$icon,$title,$desc]): ?>
            <div class="feature-card">
                <div class="feature-icon-box"><?= $icon ?></div>
                <h3><?= $title ?></h3>
                <p><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="how-it-works" class="section">
    <div class="container">
        <div class="section-head">
            <h2>How SharedSpace Works</h2>
            <p>From draft to verified publication in four simple steps.</p>
        </div>
        <div class="steps-grid">
            <?php
            $steps = [
                ['✏️','01','Write Your Article','Create your news piece using our intuitive editor. Add sources, quotes, and supporting evidence.'],
                ['🤖','02','AI Analysis','Our AI fact-checker analyses claims, cross-references sources, and generates a trust score.'],
                ['✅','03','Review & Refine','See detailed feedback on claims that need verification. Improve your article\'s credibility.'],
                ['📢','04','Publish & Share','Publish your verified article with confidence. Readers see the trust score upfront.'],
            ];
            foreach ($steps as $i => [$icon,$num,$title,$desc]): ?>
            <div class="step-card">
                <?php if ($i < 3): ?><div class="step-connector"></div><?php endif; ?>
                <div class="step-icon-wrap">
                    <div class="step-icon-box"><?= $icon ?></div>
                    <div class="step-num"><?= $num ?></div>
                </div>
                <h3><?= $title ?></h3>
                <p><?= $desc ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="video" class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>See SharedSpace in Action</h2>
            <p>Watch how our AI verifies news in real-time.</p>
        </div>
        <div class="video-wrapper">
            <video id="demo-video" controls preload="none" src="/public/demo.mp4">
                Your browser does not support the video tag.
            </video>
            <div class="video-overlay" id="video-overlay">
                <div class="play-btn" id="play-btn"><span>▶</span></div>
                <h3>Watch the Demo</h3>
                <p>See how our AI verifies news in real-time</p>
            </div>
        </div>
    </div>
</section>

<section id="testimonials" class="section">
    <div class="container">
        <div class="section-head">
            <h2>What Our Users Say</h2>
            <p>Trusted by writers and readers who care about credible, verified news.</p>
        </div>
        

        <!--reviews are all hardcoded for now-->
        <div class="reviews-grid">
        <?php foreach ([
            ['SC', 'Sarah Chen',     'Investigative Journalist', 'SharedSpace has completely changed how I verify stories before publication. The AI catches sourcing gaps my team might miss, and readers can see the trust score upfront — that transparency builds real credibility.', 5],
            ['MT', 'Michael Torres', 'Tech Writer',              'Writing about fast-moving technology topics means I need to be accurate fast. The AI fact-checker gives me confidence before I hit publish, and the trust score has genuinely improved how readers engage with my work.', 5],
            ['EW', 'Emily Watson',   'Regular Reader',           'I was tired of not knowing whether what I was reading was reliable. SharedSpace puts the credibility score right on every article. It sounds simple but it changes everything about how you consume news.', 5],
            ['DK', 'David Kim',      'Freelance Writer',         'The feedback from the AI verification is specific and actionable — it tells me which claims need stronger sourcing, not just a vague score. My articles go out faster and with a much higher trust rating than before.', 5],
        ] as [$av, $name, $role, $content, $rating]): ?>
            <div class="review-card">
                <div class="review-top">
                    <div class="review-author-avatar"><?= $av ?></div>
                    <div class="review-author-info">
                        <strong><?= $name ?></strong>
                        <span><?= $role ?></span>
                    </div>
                    <span class="review-quote-icon">"</span>
                </div>
                <p class="review-excerpt">"<?= $content ?>"</p>
                <div class="review-footer">
                    <div class="review-stars"><?php for ($s = 0; $s < $rating; $s++) echo '★'; ?></div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="pricing" class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2>Simple, Transparent Pricing</h2>
            <p>Start free and upgrade when you need more. No hidden fees.</p>
        </div>
        <div class="pricing-grid">
            <div class="pricing-card">
                <div class="pricing-head">
                    <h3>Free</h3>
                    <div class="price">$0 <span>/ forever</span></div>
                    <p>Perfect for casual readers and new writers</p>
                </div>
                <ul class="pricing-features">
                    <li class="inc">Read all text-based articles</li>
                    <li class="inc">Publish plain-text articles</li>
                    <li class="inc">View AI trust scores</li>
                    <li class="inc">Comment on articles</li>
                    <li class="exc">Access to media content</li>
                    <li class="exc">Save articles for later</li>
                    <li class="exc">Ad-free experience</li>
                </ul>
                <a href="/register.php" class="btn-outline-full">Get Started Free</a>
            </div>
            <div class="pricing-card popular">
                <div class="popular-badge">Most Popular</div>
                <div class="pricing-head">
                    <h3>Premium</h3>
                    <div class="price">$12 <span>/ per month</span></div>
                    <p>For serious writers and engaged readers</p>
                </div>
                <ul class="pricing-features">
                    <li class="inc">Everything in Free</li>
                    <li class="inc">Access to all categories</li>
                    <li class="inc">Read articles with media</li>
                    <li class="inc">Save articles for later</li>
                    <li class="inc">Ad-free experience</li>
                    <li class="inc">Priority AI analysis</li>
                    <li class="inc">Priority support</li>
                </ul>
                <a href="/register.php" class="btn-hero-full">Upgrade to Premium</a>
            </div>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <a href="/" class="footer-logo"><div class="logo-icon-box small">📰</div><span>SharedSpace</span></a>
            <p>The trusted platform for verified news. AI-powered fact-checking for the modern age.</p>
        </div>
        <div class="footer-col"><h4>Product</h4><a href="#features">Features</a><a href="#pricing">Pricing</a></div>
        <div class="footer-col"><h4>Company</h4><a href="#">About Us</a><a href="#">Contact</a></div>
        <div class="footer-col"><h4>Legal</h4><a href="#">Privacy Policy</a><a href="#">Terms of Service</a></div>
    </div>

</footer>

<script>
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
});
document.getElementById('hamburger').addEventListener('click', function() {
    document.getElementById('mobile-menu').classList.toggle('open');
    this.classList.toggle('open');
});
const overlay = document.getElementById('video-overlay');
const video   = document.getElementById('demo-video');
if (overlay && video) {
    document.getElementById('play-btn').addEventListener('click', () => {
        overlay.style.display = 'none';
        video.play();
    });
    video.addEventListener('pause', () => { overlay.style.display = 'flex'; });
}
document.querySelectorAll('a[href^="#"]').forEach(a => {
    a.addEventListener('click', e => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) { e.preventDefault(); t.scrollIntoView({ behavior: 'smooth' }); }
    });
});
</script>
</body>
</html>
