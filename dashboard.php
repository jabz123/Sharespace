<?php

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/ArticleController.php';
require_once __DIR__ . '/includes/controllers/HomepageController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();
$homeCtrl = new HomepageController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role === 'category_admin') {
    header('Location: /pages/category-admin-dashboard.php');
    exit;
}

if ($user->role === 'ai_trainer') {
    header('Location: /pages/ai-trainer-dashboard.php');
    exit;
}

$recommended = $homeCtrl->getRecommendedByInterest($user->id);
$ageGroupArticles = $homeCtrl->getPopularByAgeGroup($user->id);
$genderArticles = $homeCtrl->getPopularByGender($user->id);
$latestArticles = $homeCtrl->getLatest(6);
$spotlight = $recommended[0] ?? $latestArticles[0] ?? null;
$recommendedFeed = $recommended;

if ($spotlight && !empty($recommended) && $recommended[0]->id === $spotlight->id) {
    $recommendedFeed = array_slice($recommended, 1);
}

$credibilityPool = array_filter(array_merge($recommended, $ageGroupArticles, $genderArticles, $latestArticles));
$averageTrust = !empty($credibilityPool)
    ? (int) round(array_sum(array_map(fn($article) => (int) $article->trustScore, $credibilityPool)) / count($credibilityPool))
    : 0;

page_head('Dashboard');
?>

<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Welcome back, ' . htmlspecialchars($user->fullName), "Your tailored brief for today's verified stories."); ?>
<?php flash_messages(); ?>

<div class="page-content user-dashboard-content">
    <section class="dashboard-hero-panel">
        <div class="dashboard-hero-copy">
            <span class="dashboard-kicker">Personal news desk</span>
            <h2 class="dashboard-hero-title">A clearer view of the stories worth your attention.</h2>
            <p class="dashboard-hero-text">SharedSpace brings your most relevant coverage, a live trust layer, and a faster path into the reporting that matters.</p>

            <div class="dashboard-hero-actions">
                <a href="/pages/write.php" class="btn btn-primary">Write Article</a>
                <a href="/pages/browse.php" class="btn btn-secondary">Browse Verified News</a>
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

        <?php if ($spotlight): ?>
            <article class="dashboard-spotlight">
                <?php if (!empty($spotlight->imagePath)): ?>
                    <div class="dashboard-spotlight-media">
                        <img src="/public/<?= htmlspecialchars($spotlight->imagePath) ?>" alt="">
                        <div class="dashboard-spotlight-overlay"></div>
                    </div>
                <?php endif; ?>

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

                    <p class="dashboard-spotlight-excerpt"><?= htmlspecialchars(limit_words($spotlight->excerpt, 30)) ?></p>

                    <div class="dashboard-spotlight-footer">
                        <div>
                            <div class="dashboard-spotlight-author"><?= htmlspecialchars($spotlight->authorName) ?></div>
                            <div class="dashboard-spotlight-time"><?= relative_time($spotlight->publishedAt) ?></div>
                        </div>
                        <a href="/pages/article.php?id=<?= $spotlight->id ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="dashboard-spotlight-link">Read story</a>
                    </div>
                </div>
            </article>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <div>
                <span class="dashboard-section-kicker">Recommended</span>
                <h2>Recommended For You</h2>
            </div>
            <a href="/pages/browse.php?sort=trusted" class="dashboard-section-link">Open newsroom</a>
        </div>

        <?php if (empty($recommendedFeed)): ?>
            <p class="text-muted">No recommendations yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($recommendedFeed as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <div>
                <span class="dashboard-section-kicker">Audience pulse</span>
                <h2>People Your Age Are Reading</h2>
            </div>
        </div>
        <?php if (empty($ageGroupArticles)): ?>
            <p class="text-muted">Not enough data yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($ageGroupArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <div>
                <span class="dashboard-section-kicker">Reader lens</span>
                <h2>Popular With <?= htmlspecialchars(ucfirst($user->gender)); ?> Readers</h2>
            </div>
        </div>
        <?php if (empty($genderArticles)): ?>
            <p class="text-muted">Not enough data yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($genderArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </section>

    <section class="dashboard-section">
        <div class="dashboard-section-head">
            <div>
                <span class="dashboard-section-kicker">Newsroom feed</span>
                <h2>Latest Articles</h2>
            </div>
            <a href="/pages/browse.php?sort=recent" class="dashboard-section-link">See latest</a>
        </div>
        <?php if (empty($latestArticles)): ?>
            <p class="text-muted">No articles yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($latestArticles as $article):
                article_card($article, $user);
            endforeach; ?>
            <a href="/pages/browse.php" class="view-more-card">
                <div class="view-more-content"><span>View More</span><img src="/public/icons/viewmoreicon.png" alt="view more"></div>
            </a>
        </div>
        <?php endif; ?>
    </section>
</div>
</main>
</div>

<?php page_foot(); ?>
