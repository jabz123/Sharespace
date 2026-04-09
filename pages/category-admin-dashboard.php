<?php
// dashboard page for category_admin users
// read-only: displays articles exactly like the regular user dashboard

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/controllers/HomepageController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();
$homeCtrl = new HomepageController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role !== 'category_admin') {
    header('Location: /dashboard.php');
    exit;
}

$recommended = $homeCtrl->getRecommendedByInterest($user->id);
$ageGroupArticles = $homeCtrl->getPopularByAgeGroup($user->id);
$genderArticles = $homeCtrl->getPopularByGender($user->id);
$latestArticles = $homeCtrl->getLatest(6);

page_head('Dashboard');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Welcome back, ' . htmlspecialchars($user->fullName), "Here's what's happening today"); ?>
<?php flash_messages(); ?>

<div class="page-content">
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
        <h2>Popular With <?php echo ucfirst($user->gender); ?> Readers</h2>
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
        <h2 style="font-size:18px;font-weight:700;font-family:Georgia,serif;margin-bottom:16px">Latest Articles</h2>
        <?php if (empty($latestArticles)): ?>
            <p class="text-muted">No articles yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($latestArticles as $article):
                article_card($article, $user);
            endforeach; ?>
            <a href="/pages/browse.php" class="view-more-card">
                <div class="view-more-content"><span>View More</span><img src="/public/icons/viewmoreicon.png" alt="View more"></div>
            </a>
        </div>
        <?php endif; ?>
    </div>

</div>
</main>
</div>

<?php page_foot(); ?>
