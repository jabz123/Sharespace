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
<style>
body.page-saved-articles {
    background:
        radial-gradient(circle at top left, rgba(245,166,35,0.08), transparent 24%),
        linear-gradient(180deg, #060b14 0%, #09111d 100%) !important;
    color: #f5f8ff !important;
}

body.page-saved-articles .dashboard-layout main,
body.page-saved-articles .page-content {
    background: transparent !important;
}

body.page-saved-articles .dash-header {
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 22%),
        linear-gradient(180deg, rgba(8,14,26,0.94) 0%, rgba(8,14,26,0.84) 100%) !important;
    border-bottom: 1px solid rgba(245,166,35,0.14) !important;
}

body.page-saved-articles .dash-title,
body.page-saved-articles .dash-subtitle,
body.page-saved-articles .text-muted {
    color: #dbe6f6 !important;
}

body.page-saved-articles .page-content {
    max-width: 1380px;
    padding: 34px 28px 48px !important;
}

body.page-saved-articles .article-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

body.page-saved-articles .article-card,
body.page-saved-articles .paywall-card {
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 20%),
        linear-gradient(180deg, rgba(11,17,31,0.98) 0%, rgba(9,14,25,1) 100%) !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    border-radius: 24px !important;
    box-shadow: 0 22px 48px rgba(0,0,0,0.22) !important;
    backdrop-filter: none !important;
}

body.page-saved-articles .article-card:hover,
body.page-saved-articles .paywall-card:hover {
    border-color: rgba(245,166,35,0.24) !important;
    transform: translateY(-2px);
}

body.page-saved-articles .card-title,
body.page-saved-articles .card-title a,
body.page-saved-articles .card-excerpt,
body.page-saved-articles .card-footer,
body.page-saved-articles .card-footer *,
body.page-saved-articles .card-time,
body.page-saved-articles .author-name,
body.page-saved-articles .text-muted {
    color: #dbe6f6 !important;
}

body.page-saved-articles .card-title,
body.page-saved-articles .card-title a {
    color: #ffffff !important;
}

body.page-saved-articles .card-excerpt,
body.page-saved-articles .card-time,
body.page-saved-articles .card-footer,
body.page-saved-articles .card-footer * {
    color: #9caeca !important;
}

body.page-saved-articles .category-tag {
    color: #ffd37a !important;
    border-color: rgba(245,166,35,0.24) !important;
    background: rgba(245,166,35,0.08) !important;
}

body.page-saved-articles .card-image {
    border-radius: 18px !important;
    overflow: hidden;
}

body.page-saved-articles .paywall-inner {
    min-height: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    gap: 16px;
    padding: 14px;
}

body.page-saved-articles .paywall-inner h3,
body.page-saved-articles .paywall-inner p {
    color: #f5f8ff !important;
}

body.page-saved-articles .paywall-inner .btn {
    min-height: 46px;
    padding: 0 20px !important;
    border-radius: 14px !important;
    border: 1px solid rgba(245,166,35,0.18) !important;
    background: linear-gradient(135deg, #f4a321 0%, #ffca61 100%) !important;
    color: #08111f !important;
    font-weight: 700 !important;
}

body.page-saved-articles .saved-empty-state {
    padding: 38px 10px;
    color: #dbe6f6 !important;
    font-size: 18px;
}
</style>

<div class="dashboard-layout">
<?php sidebar($user); ?>

<main>
<?php dash_header('Saved Articles', 'Your bookmarked articles'); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <?php if (empty($savedArticles)): ?>
        <p class="text-muted saved-empty-state">You have not saved any articles yet.</p>

    <?php else: ?>

    <div class="article-grid">

        <!-- ✅ NORMAL ARTICLES -->
        <?php foreach ($savedArticles as $article): ?>
            <?php article_card($article, $user); ?>
        <?php endforeach; ?>

        <!-- 🔒 PAYWALL CARD INSIDE GRID -->
        <?php if (!$isPremium && $totalSaved > 3): ?>

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
