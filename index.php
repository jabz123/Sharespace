<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/ArticleController.php';
require_once __DIR__ . '/includes/controllers/CommentController.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/textlimit.php';
require_once __DIR__ . '/includes/db.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();

if ($auth->currentUser()) {
    header('Location: /dashboard.php');
    exit;
}

$previewArticles = $articleCtrl->getPreview(3);
$hero = DB::first("SELECT * FROM landing_sections WHERE section_key = 'hero'");
$demoVideo = DB::first("SELECT * FROM landing_sections WHERE section_key = 'demo_video'");
$features = DB::query("SELECT * FROM landing_features ORDER BY display_order ASC");
$steps = DB::query("SELECT * FROM landing_steps ORDER BY display_order ASC");
$plans = DB::query("SELECT * FROM landing_pricing_plans ORDER BY display_order ASC");
$pricingFeatures = DB::query("SELECT * FROM landing_pricing_features ORDER BY display_order ASC");

function score_class(int $score): string
{
    if ($score >= 80) return 'score-high';
    if ($score >= 60) return 'score-mid';
    return 'score-low';
}

function youtubeEmbedUrl(?string $url): string
{
    $url = trim((string)$url);

    if ($url === '') {
        return '';
    }

    if (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    if (preg_match('/youtube\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        return 'https://www.youtube.com/embed/' . $matches[1];
    }

    return $url;
}

$demoEmbedUrl = youtubeEmbedUrl($demoVideo['video_url'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SharedSpace - Truth in Every Headline</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/public/css/landing.css" />
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container nav-inner">
        <a href="/" class="nav-logo">
            <img src="/public/icons/sharedspace-logo-light.svg" alt="SharedSpace" class="nav-logo-image">
        </a>

        <div class="nav-links" id="nav-links">
            <a href="#recent-articles">Recent Articles</a>
            <a href="#features">Fact Checking</a>
            <a href="#how-it-works">How It Works</a>
            <a href="#pricing">Pricing</a>
        </div>

        <div class="nav-auth">
            <a href="/login.php" class="btn-ghost-nav">Sign In</a>
            <a href="/register.php" class="btn-hero-nav">Get Started</a>
        </div>

        <button class="hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </button>
    </div>

    <div class="mobile-menu" id="mobile-menu">
        <a href="#recent-articles">Recent Articles</a>
        <a href="#features">Fact Checking</a>
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
    <div class="starfield starfield-back" aria-hidden="true"></div>
    <div class="starfield starfield-mid" aria-hidden="true"></div>
    <div class="starfield starfield-front" aria-hidden="true"></div>
    <div class="cosmos-haze" aria-hidden="true"></div>
    <div class="planet planet-large" aria-hidden="true"></div>
    <div class="planet planet-small" aria-hidden="true"></div>
    <div class="signal-satellite" aria-hidden="true">
        <span class="satellite-core"></span>
        <span class="satellite-panel satellite-panel-left"></span>
        <span class="satellite-panel satellite-panel-right"></span>
        <span class="satellite-dish"></span>
        <span class="satellite-beam satellite-beam-one"></span>
        <span class="satellite-beam satellite-beam-two"></span>
    </div>
    <div class="hero-galaxy" aria-hidden="true"></div>
    <div class="hero-aura" aria-hidden="true"></div>
    <div class="blob blob-right"></div>
    <div class="blob blob-left"></div>

    <div class="container hero-inner">
        <div class="hero-copy">
            <div class="hero-badge fade-in">
                <span class="hero-badge-dot" aria-hidden="true"></span>
                AI-powered fact checking
            </div>

            <h1 class="hero-title slide-up">
                Uncover the<br>
                Truth in <span class="gradient-text">Every Headline</span>
            </h1>

            <p class="hero-sub slide-up" style="animation-delay:.1s">
                Join the platform built for finding and sharing trustworthy news. Our AI analyzes each story for accuracy before it reaches you.
            </p>

            <div class="hero-cta slide-up" style="animation-delay:.1s">
                <a href="/register.php" class="btn-hero-lg">Start Publishing Free</a>
                <a href="#video" class="btn-outline-lg">View Demo</a>
            </div>
        </div>

        <div id="recent-articles" class="preview-window slide-up" style="animation-delay:.18s">
            <div class="orbital-swimmer" aria-hidden="true">
                <span class="swimmer-trail"></span>
                <span class="swimmer-helmet"></span>
                <span class="swimmer-body"></span>
                <span class="swimmer-fin"></span>
            </div>

            <div class="preview-header">
                <span class="preview-kicker">Editorial content</span>
                <h2>Articles Reviewed &amp; Fact-Checked</h2>
                <p>See what our AI has verified on SharedSpace.</p>
            </div>

            <div class="preview-cards">
                <?php if (!empty($previewArticles)): ?>
                    <?php foreach ($previewArticles as $index => $article): ?>
                        <?php
                        $statusLabel = $article->trustScore >= 80 ? 'Verified' : ($article->trustScore >= 60 ? 'Reviewed' : 'Watchlist');
                        ?>
                        <a href="/login.php" class="preview-card">
                            <div class="preview-meta">
                                <span class="preview-cat"><?= htmlspecialchars($article->categoryName) ?></span>
                                <span class="preview-score <?= score_class($article->trustScore) ?>"><?= $article->trustScore ?>%</span>
                            </div>

                            <?php if (!empty($article->imagePath)): ?>
                                <div class="preview-thumb">
                                    <img src="/public/<?= htmlspecialchars($article->imagePath) ?>" alt="">
                                </div>
                            <?php endif; ?>

                            <h3 class="preview-title"><?= htmlspecialchars(limit_words($article->title, 8)) ?></h3>

                            <p class="preview-excerpt">
                                <?php
                                $excerpt = $article->excerpt;
                                if (mb_strlen($excerpt, 'UTF-8') > 120) {
                                    echo htmlspecialchars(mb_substr($excerpt, 0, 120, 'UTF-8')) . '...';
                                } else {
                                    echo htmlspecialchars($excerpt);
                                }
                                ?>
                            </p>

                            <div class="preview-footer">
                                <div class="preview-author">
                                    <div class="author-avatar"><?= htmlspecialchars($article->authorInitial()) ?></div>
                                    <div class="author-info">
                                        <span class="author-name"><?= htmlspecialchars($article->authorName) ?></span>
                                        <span class="author-time"><?= relative_time($article->publishedAt) ?></span>
                                    </div>
                                </div>
                                <div class="preview-stats">
                                    <span><?= $statusLabel ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php for ($i = 0; $i < 3; $i++): ?>
                        <div class="preview-card skeleton">
                            <div class="preview-thumb pulse"></div>
                            <div class="skel-line w-half pulse"></div>
                            <div class="skel-line w-full pulse"></div>
                            <div class="skel-line w-three-quarters pulse"></div>
                        </div>
                    <?php endfor; ?>
                <?php endif; ?>
            </div>

            <div class="preview-window-cta">
                <a href="/login.php" class="preview-window-link">See All Verified News</a>
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
            <?php foreach ($features as $feature): ?>
                <div class="feature-card">
                    <div class="feature-icon-box">
                        <?php if (!empty($feature['icon_path'])): ?>
                            <img src="/<?= htmlspecialchars($feature['icon_path']) ?>" alt="">
                        <?php endif; ?>
                    </div>

                    <h3><?= htmlspecialchars($feature['title']) ?></h3>
                    <p><?= htmlspecialchars($feature['description']) ?></p>
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
            <?php foreach ($steps as $i => $step): ?>
                <div class="step-card">
                    <?php if ($i < count($steps) - 1): ?>
                        <div class="step-connector"></div>
                    <?php endif; ?>

                    <div class="step-icon-wrap">
                        <div class="step-icon-box">
                            <?php if (!empty($step['icon_path'])): ?>
                                <img src="/<?= htmlspecialchars($step['icon_path']) ?>" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="step-num"><?= htmlspecialchars($step['step_number']) ?></div>
                    </div>

                    <h3><?= htmlspecialchars($step['title']) ?></h3>
                    <p><?= htmlspecialchars($step['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section id="video" class="section section-alt">
    <div class="container">
        <div class="section-head">
            <h2><?= htmlspecialchars($demoVideo['title'] ?? 'See SharedSpace in Action') ?></h2>
            <p><?= htmlspecialchars($demoVideo['subtitle'] ?? 'Watch how our AI verifies news in real-time.') ?></p>
        </div>

        <div class="video-wrapper">
            <?php if (!empty($demoEmbedUrl)): ?>
                <iframe
                    width="100%"
                    height="450"
                    src="<?= htmlspecialchars($demoEmbedUrl) ?>"
                    title="Demo Video"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen
                    style="border-radius: 16px;"
                ></iframe>
            <?php else: ?>
                <div style="padding: 40px; text-align: center; background: #111827; color: white; border-radius: 16px;">
                    No demo video set yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section id="testimonials" class="section">
    <div class="container">
        <div class="section-head">
            <h2>What Our Users Say</h2>
            <p>Trusted by writers and readers who care about credible, verified news.</p>
        </div>

        <div class="reviews-grid">
            <?php foreach ([
                ['SC', 'Sarah Chen', 'Investigative Journalist', 'SharedSpace has completely changed how I verify stories before publication. The AI catches sourcing gaps my team might miss, and readers can see the trust score upfront - that transparency builds real credibility.', 5],
                ['MT', 'Michael Torres', 'Tech Writer', 'Writing about fast-moving technology topics means I need to be accurate fast. The AI fact-checker gives me confidence before I hit publish, and the trust score has genuinely improved how readers engage with my work.', 5],
                ['EW', 'Emily Watson', 'Regular Reader', 'I was tired of not knowing whether what I was reading was reliable. SharedSpace puts the credibility score right on every article. It sounds simple but it changes everything about how you consume news.', 5],
                ['DK', 'David Kim', 'Freelance Writer', 'The feedback from the AI verification is specific and actionable - it tells me which claims need stronger sourcing, not just a vague score. My articles go out faster and with a much higher trust rating than before.', 5],
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
                        <div class="review-stars"><?= $rating ?>/5 rating</div>
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
            <?php foreach ($plans as $plan): ?>
                <div class="pricing-card <?= $plan['is_popular'] ? 'popular' : '' ?>">
                    <?php if ($plan['is_popular']): ?>
                        <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>

                    <div class="pricing-head">
                        <h3><?= htmlspecialchars($plan['name']) ?></h3>

                        <div class="price">
                            <?= htmlspecialchars($plan['price']) ?>
                            <span><?= htmlspecialchars($plan['price_suffix']) ?></span>
                        </div>

                        <p><?= htmlspecialchars($plan['description']) ?></p>
                    </div>

                    <ul class="pricing-features">
                        <?php foreach ($pricingFeatures as $feature): ?>
                            <?php if ($feature['plan_id'] == $plan['id']): ?>
                                <li class="<?= $feature['is_included'] ? 'inc' : 'exc' ?>">
                                    <?= htmlspecialchars($feature['feature_text']) ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>

                    <a href="<?= htmlspecialchars($plan['button_link']) ?>" class="<?= $plan['is_popular'] ? 'btn-hero-full' : 'btn-outline-full' ?>">
                        <?= htmlspecialchars($plan['button_text']) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<footer class="site-footer">
    <div class="container footer-inner">
        <div class="footer-brand">
            <a href="/" class="footer-logo">
                    <img src="/public/icons/sharedspace-logo-light.svg" alt="SharedSpace" class="footer-logo-image">
            </a>
            <p>The trusted platform for verified news. AI-powered fact-checking for the modern age.</p>
        </div>

        <div class="footer-col">
            <h4>Product</h4>
            <a href="#features">Features</a>
            <a href="#pricing">Pricing</a>
        </div>

        <div class="footer-col">
            <h4>Company</h4>
            <a href="#">About Us</a>
            <a href="#">Contact</a>
        </div>

        <div class="footer-col">
            <h4>Legal</h4>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
    </div>
</footer>

<script>
window.addEventListener('scroll', () => {
    document.getElementById('navbar').classList.toggle('scrolled', window.scrollY > 20);
});

document.getElementById('hamburger').addEventListener('click', function () {
    document.getElementById('mobile-menu').classList.toggle('open');
    this.classList.toggle('open');
});

document.querySelectorAll('a[href^="#"]').forEach((a) => {
    a.addEventListener('click', (e) => {
        const t = document.querySelector(a.getAttribute('href'));
        if (t) {
            e.preventDefault();
            t.scrollIntoView({ behavior: 'smooth' });
        }
    });
});
</script>

</body>
</html>
