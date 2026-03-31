<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();

$auth->requireAuth();
$user = $auth->currentUser();

$isPremium = ($user->role === 'premium');

// ⭐ LIMIT = 4 for free users
if ($isPremium) {
    $savedArticles = $articleCtrl->getSavedArticles($user->id);
} else {
    $savedArticles = $articleCtrl->getSavedArticles($user->id, 3);
}

$totalSaved = $articleCtrl->countSavedArticles($user->id);

page_head('Saved Articles');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>

<main>
<?php dash_header('Saved Articles', 'Your bookmarked articles'); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <?php if (empty($savedArticles)): ?>
        <p class="text-muted">You have not saved any articles yet.</p>

    <?php else: ?>

    <div class="article-grid">

        <!-- ✅ NORMAL ARTICLES -->
        <?php foreach ($savedArticles as $article): ?>
            <?php article_card($article, $user); ?>
        <?php endforeach; ?>

        <!-- 🔒 PAYWALL CARD INSIDE GRID -->
        <?php if (!$isPremium && $totalSaved > 4): ?>

            <div class="article-card paywall-card">

                <div class="paywall-inner">

                    <img src="/public/icons/premiumlockicon2.png" class="paywall-icon">

                    <h3>Unlock all your saved articles</h3>

                    <p>
                        You have <span class="highlight-number"><?= $totalSaved ?></span> saved articles.<br>
                        Upgrade to access everything.
                    </p>

                    <a href="/subscribe.php" class="btn btn-primary">
                        Upgrade to Premium
                    </a>

                </div>

            </div>

        <?php endif; ?>

    </div>

    <?php endif; ?>

</div>

</main>
</div>

<?php page_foot(); ?>