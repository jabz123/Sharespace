<?php

//saved articles page
//shows all saved articles for user
//free user will habe paywall

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();

$auth->requireAuth();
$user = $auth->currentUser();

//only if not free then can acecss
$canViewSavedArticles = in_array($user->role, ['premium', 'system_admin', 'category_admin'], true);
$totalSaved = $articleCtrl->countSavedArticles($user->id);
$savedArticles = [];


if ($canViewSavedArticles) {
    $savedArticles = $articleCtrl->getSavedArticles($user->id);
}

page_head('Saved Articles');
?>
<div class="dashboard-layout user-dashboard-shell">
    <?php sidebar($user); ?>

    <main>
        <?php dash_header('Saved Articles', 'Your bookmarked articles'); ?>
        <?php flash_messages(); ?>

        <div class="page-content saved-page-content">

            <?php if (!$canViewSavedArticles): ?>

                <section class="saved-feature-paywall">
                    <div class="saved-feature-lock">
                        <img src="/public/icons/premiumlockicon2.png" alt="">
                    </div>

                    <span class="saved-feature-kicker">Premium feature</span>

                    <h2>Unlock your saved article library</h2>

                    <p>
                        Saved Articles is a premium workspace for keeping trusted stories in one place,
                        returning to them anytime, and building your personal reading list.
                    </p>

                    <?php if ($totalSaved > 0): ?>
                        <p class="saved-feature-count">
                            You already have <span><?= (int) $totalSaved ?></span> saved <?= $totalSaved === 1 ? 'article' : 'articles' ?> waiting.
                        </p>
                    <?php endif; ?>

                    <a href="/pages/subscription.php" class="btn btn-primary saved-feature-btn">
                        Upgrade to Premium
                    </a>
                </section>

            <?php elseif (empty($savedArticles)): ?>
                <p class="text-muted saved-empty-state">You have not saved any articles yet.</p>

            <?php else: ?>

                <div class="article-grid">

                    <!-- ✅ NORMAL ARTICLES -->
                    <?php foreach ($savedArticles as $article): ?>
                        <?php article_card($article, $user); ?>
                    <?php endforeach; ?>

                    <!-- 🔒 PAYWALL CARD INSIDE GRID -->
                </div>

            <?php endif; ?>

        </div>

    </main>
</div>

<?php page_foot(); ?>