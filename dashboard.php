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

$recommended = $homeCtrl->getRecommendedByInterest($user->id);
$ageGroupArticles = $homeCtrl->getPopularByAgeGroup($user->id);
$genderArticles = $homeCtrl->getPopularByGender($user->id);
$latestArticles = $homeCtrl->getLatest(6);

page_head('Dashboard');
?>

<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Welcome back, ' . htmlspecialchars($user->fullName), "Your tailored brief for today's verified stories."); ?>
<?php flash_messages(); ?>

<div class="page-content user-dashboard-content">
    <div class="flex gap-2 mb-6">
        <a href="/pages/write.php" class="btn btn-primary">Write Article</a>
    </div>

    <div class="mb-10">
        <h2>Recommended For You</h2>

        <?php if (empty($recommended)): ?>
            <p class="text-muted">No recommendations yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($recommended as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="mb-10">
        <h2>People Your Age Are Reading</h2>
        <?php if (empty($ageGroupArticles)): ?>
            <p class="text-muted">Not enough data yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($ageGroupArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="mb-10">
        <h2>Popular With <?= htmlspecialchars(ucfirst($user->gender)); ?> Readers</h2>
        <?php if (empty($genderArticles)): ?>
            <p class="text-muted">Not enough data yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($genderArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="mb-10">
        <h2 style="font-size:18px;font-weight:700;font-family:'Instrument Serif', Georgia, serif;margin-bottom:16px">Latest Articles</h2>
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
    </div>
</div>
</main>
</div>

<?php page_foot(); ?>
