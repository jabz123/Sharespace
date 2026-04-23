<?php

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/ArticleController.php';
require_once __DIR__ . '/includes/controllers/HomepageController.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();
$homeCtrl    = new HomepageController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role === 'category_admin') {
    header('Location: /pages/category-admin-dashboard.php');
    exit;
}

$recommended      = $homeCtrl->getRecommendedByInterest($user->id);
$ageGroupArticles = $homeCtrl->getPopularByAgeGroup($user->id);
$genderArticles   = $homeCtrl->getPopularByGender($user->id);
$latestArticles   = $homeCtrl->getLatest(6);
$spotlight        = $recommended[0] ?? $latestArticles[0] ?? null;
$recommendedFeed  = $recommended;

if ($spotlight && !empty($recommended) && $recommended[0]->id === $spotlight->id) {
    $recommendedFeed = array_slice($recommended, 1);
}

$credibilityPool = array_filter(array_merge($recommended, $ageGroupArticles, $genderArticles, $latestArticles));
$averageTrust = !empty($credibilityPool)
    ? (int) round(array_sum(array_map(fn($a) => (int) $a->trustScore, $credibilityPool)) / count($credibilityPool))
    : 0;

/* Build top contributors from article data */
$contributorMap = [];
foreach (array_merge($recommended, $latestArticles, $ageGroupArticles) as $a) {
    if (!isset($contributorMap[$a->authorName])) {
        $contributorMap[$a->authorName] = ['name' => $a->authorName, 'count' => 0, 'initial' => $a->authorInitial()];
    }
    $contributorMap[$a->authorName]['count']++;
}
arsort($contributorMap);
$topContributors = array_slice(array_values($contributorMap), 0, 3);

page_head('Dashboard');
?>

<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>

    <!-- DASH HEADER -->
    <header class="dash-header">
        <div>
            <h1 class="dash-title">Welcome back, <?= htmlspecialchars($user->fullName) ?> 👋</h1>
            <p class="dash-subtitle">Your tailored brief for today's verified stories.</p>
        </div>
        <div class="dash-header-right">
            <form class="dash-search" action="/pages/browse.php" method="GET">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" name="search" placeholder="Search articles, topics, users…" autocomplete="off">
            </form>
            <button class="dash-notif-btn" aria-label="Notifications">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
                </svg>
            </button>
        </div>
    </header>

    <?php flash_messages(); ?>

    <!-- MAIN CONTENT GRID -->
    <div class="page-content">
    <div class="user-dashboard-content">

        <!-- HERO PANEL -->
        <div class="dash-hero">

            <!-- Hero copy card -->
            <div class="dashboard-hero-copy">
                <span class="dashboard-kicker">Personal news desk</span>
                <h2 class="dashboard-hero-title">A clearer view of the stories worth your attention.</h2>
                <p class="dashboard-hero-text">SharedSpace brings you the most relevant coverage, a live trust layer, and a faster path into the reporting that matters.</p>

                <div class="dashboard-hero-actions">
                    <a href="/pages/write.php" class="btn btn-primary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Write Article
                    </a>
                    <a href="/pages/browse.php" class="btn btn-secondary">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Browse Verified News
                    </a>
                </div>

                <div class="dashboard-hero-stats">
                    <div class="dashboard-stat-card">
                        <strong><?= (int) count($recommended) ?></strong>
                        <span>Recommended stories</span>
                    </div>
                    <div class="dashboard-stat-card">
                        <strong><?= (int) count($latestArticles) ?></strong>
                        <span>Fresh reports today</span>
                    </div>
                    <div class="dashboard-stat-card">
                        <strong><?= $averageTrust ?>%</strong>
                        <span>Average trust signal</span>
                    </div>
                </div>
            </div>

            <!-- Spotlight article -->
            <?php if ($spotlight): ?>
            <article class="dashboard-spotlight">
                <div class="dashboard-spotlight-media" <?= empty($spotlight->imagePath) ? 'style="background:linear-gradient(135deg,#0d1a38,#1a1040)"' : '' ?>>
                    <?php if (!empty($spotlight->imagePath)): ?>
                        <img src="/public/<?= htmlspecialchars($spotlight->imagePath) ?>" alt="">
                    <?php endif; ?>
                    <div class="dashboard-spotlight-overlay"></div>
                </div>
                <div class="dashboard-spotlight-body">
                    <div class="dashboard-spotlight-topline">
                        <span class="category-tag <?= category_theme_class($spotlight->categoryName) ?>"><?= htmlspecialchars($spotlight->categoryName) ?></span>
                        <span class="dashboard-spotlight-score"><?= (int) $spotlight->trustScore ?>% credibility</span>
                    </div>
                    <h3 class="dashboard-spotlight-title">
                        <a href="/pages/article.php?id=<?= $spotlight->id ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                            <?= htmlspecialchars($spotlight->title) ?>
                        </a>
                    </h3>
                    <p class="dashboard-spotlight-excerpt"><?= htmlspecialchars(limit_words($spotlight->excerpt, 28)) ?></p>
                    <div class="dashboard-spotlight-footer">
                        <div>
                            <div class="dashboard-spotlight-author"><?= htmlspecialchars($spotlight->authorName) ?></div>
                            <div class="dashboard-spotlight-time"><?= relative_time($spotlight->publishedAt) ?></div>
                        </div>
                        <a href="/pages/article.php?id=<?= $spotlight->id ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="dashboard-spotlight-link">Read story →</a>
                    </div>
                </div>
            </article>
            <?php endif; ?>

        </div><!-- /dash-hero -->


        <!-- RIGHT SIDEBAR PANEL -->
        <aside class="dash-right-panel">

            <!-- Trending topics -->
            <div class="dash-panel-card">
                <p class="dash-panel-card-title">Trending Topics</p>
                <div class="trending-list">
                    <?php
                    $trendingTopics = [
                        ['emoji' => '🤖', 'label' => 'Artificial Intelligence', 'bg' => 'rgba(139,92,246,0.15)',  'href' => '/pages/browse.php?category=technology'],
                        ['emoji' => '🌐', 'label' => 'Global Economy',          'bg' => 'rgba(245,158,11,0.15)', 'href' => '/pages/browse.php?category=economy'],
                        ['emoji' => '🏛️', 'label' => 'US Politics',             'bg' => 'rgba(239,68,68,0.15)',  'href' => '/pages/browse.php?category=politics'],
                        ['emoji' => '🚀', 'label' => 'Space Exploration',        'bg' => 'rgba(59,130,246,0.15)','href' => '/pages/browse.php?category=science'],
                        ['emoji' => '🌱', 'label' => 'Climate Change',           'bg' => 'rgba(34,197,94,0.15)', 'href' => '/pages/browse.php?category=environment'],
                    ];
                    foreach ($trendingTopics as $t): ?>
                    <a href="<?= $t['href'] ?>" class="trending-item">
                        <span class="trending-dot" style="background:<?= $t['bg'] ?>"><?= $t['emoji'] ?></span>
                        <span class="trending-label"><?= $t['label'] ?></span>
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" style="color:var(--text-muted);flex-shrink:0">
                            <path d="M9 18l6-6-6-6"/>
                        </svg>
                    </a>
                    <?php endforeach; ?>
                </div>
                <a href="/pages/browse.php" class="trending-view-all">
                    View all topics
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Top contributors -->
            <?php if (!empty($topContributors)): ?>
            <div class="dash-panel-card">
                <p class="dash-panel-card-title">Top Contributors</p>
                <div class="contributor-list">
                    <?php foreach ($topContributors as $c): ?>
                    <div class="contributor-item">
                        <div class="contributor-avatar"><?= htmlspecialchars(strtoupper(substr($c['name'], 0, 1))) ?></div>
                        <span class="contributor-name"><?= htmlspecialchars($c['name']) ?></span>
                        <span class="contributor-count"><?= $c['count'] ?> stories</span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Promo CTA -->
            <div class="dash-promo-card">
                <p class="dash-promo-title">Explore more stories that matter.</p>
                <p class="dash-promo-text">Dive into verified news across all topics and perspectives.</p>
                <a href="/pages/browse.php" class="btn btn-primary" style="font-size:12px;padding:8px 14px;">
                    Open newsroom →
                </a>
                <svg class="dash-promo-planet" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg" fill="none">
                    <circle cx="50" cy="50" r="28" fill="rgba(139,92,246,0.4)" stroke="rgba(139,92,246,0.3)" stroke-width="1"/>
                    <ellipse cx="50" cy="50" rx="46" ry="14" stroke="rgba(139,92,246,0.25)" stroke-width="2" fill="none" transform="rotate(-20 50 50)"/>
                    <circle cx="38" cy="34" r="5" fill="rgba(255,255,255,0.08)"/>
                </svg>
            </div>

        </aside><!-- /dash-right-panel -->


        <!-- RECOMMENDED SECTION -->
        <?php if (!empty($recommendedFeed)): ?>
        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <div>
                    <span class="dashboard-section-kicker">⭐ Recommended</span>
                    <h2>Recommended For You</h2>
                </div>
                <a href="/pages/browse.php?sort=trusted" class="dashboard-section-link">Open newsroom ↗</a>
            </div>
            <div class="article-grid">
                <?php foreach ($recommendedFeed as $article): article_card($article, $user); endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- AGE GROUP SECTION -->
        <?php if (!empty($ageGroupArticles)): ?>
        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <div>
                    <span class="dashboard-section-kicker">📈 Audience pulse</span>
                    <h2>People Your Age Are Reading</h2>
                </div>
            </div>
            <div class="article-grid">
                <?php foreach ($ageGroupArticles as $article): article_card($article, $user); endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- GENDER SECTION -->
        <?php if (!empty($genderArticles)): ?>
        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <div>
                    <span class="dashboard-section-kicker">👁 Reader lens</span>
                    <h2>Popular With <?= htmlspecialchars(ucfirst($user->gender)) ?> Readers</h2>
                </div>
            </div>
            <div class="article-grid">
                <?php foreach ($genderArticles as $article): article_card($article, $user); endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- LATEST SECTION -->
        <section class="dashboard-section">
            <div class="dashboard-section-head">
                <div>
                    <span class="dashboard-section-kicker">🗞 Newsroom feed</span>
                    <h2>Latest Articles</h2>
                </div>
                <a href="/pages/browse.php?sort=recent" class="dashboard-section-link">See latest ↗</a>
            </div>
            <?php if (empty($latestArticles)): ?>
                <p style="color:var(--text-muted);font-size:14px">No articles yet.</p>
            <?php else: ?>
            <div class="article-grid">
                <?php foreach ($latestArticles as $article): article_card($article, $user); endforeach; ?>
                <a href="/pages/browse.php" class="view-more-card">
                    <div class="view-more-content">
                        <span>View More</span>
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </section>

    </div><!-- /user-dashboard-content -->
    </div><!-- /page-content -->

</main>
</div>

<?php page_foot(); ?>