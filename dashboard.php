<?php
// basically homepage/ after login will direct here.
// retrieves recent articles from ArticleController
// calculates some article stats like avg trust score and category count (functions created but not used in the UI yet)
// displays recent articles and quick actions like writing a new article

//dashboard page 
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/ArticleController.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();

$auth->requireAuth();
$user = $auth->currentUser();

//fetch recent articles for dashboard stats and listing
$recentArticles = $articleCtrl->getRecent(6);

$avgTrust = count($recentArticles)
    ? round(array_sum(array_map(fn($a) => $a->trustScore, $recentArticles)) / count($recentArticles))
    : 0;
$trendingCount = count(array_unique(array_map(fn($a) => $a->categoryName, $recentArticles)));

page_head('Dashboard');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Welcome back, ' . htmlspecialchars($user->fullName), "Here's what's happening today"); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <div class="flex gap-2 mb-6">
        <a href="/pages/write.php" class="btn btn-primary">✏️ Write Article</a>
    </div>

    <div class="mb-8">
        <h2 style="font-size:18px;font-weight:700;font-family:Georgia,serif;margin-bottom:16px">Recent Articles</h2>
        <?php if (empty($recentArticles)): ?>
            <p class="text-muted">No articles yet. <a href="/pages/write.php">Write the first one!</a></p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($recentArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>
</main>
</div>
<?php page_foot(); ?>
