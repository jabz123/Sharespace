<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();
$auth->requireAuth();
$user = $auth->currentUser();

$filter = $_GET['filter'] ?? 'published';
$articles = $filter === 'draft'
    ? $articleCtrl->getDraftsByAuthor($user->id)
    : $articleCtrl->getByAuthor($user->id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $result = $articleCtrl->delete((int)($_POST['article_id'] ?? 0), $user->id);
    if (isset($result['ok'])) {
        redirect('/pages/my-articles.php', null, 'Article deleted.');
    }
    redirect('/pages/my-articles.php', $result['error']);
}

page_head('My Articles');
?>
<style>
body.page-my-articles {
    background:
        radial-gradient(circle at top left, rgba(245,166,35,0.08), transparent 24%),
        linear-gradient(180deg, #060b14 0%, #09111d 100%) !important;
    color: #f5f8ff !important;
}

body.page-my-articles .dashboard-layout main,
body.page-my-articles .page-content {
    background: transparent !important;
}

body.page-my-articles .dash-header {
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 22%),
        linear-gradient(180deg, rgba(8,14,26,0.94) 0%, rgba(8,14,26,0.84) 100%) !important;
    border-bottom: 1px solid rgba(245,166,35,0.14) !important;
}

body.page-my-articles .dash-title,
body.page-my-articles .dash-subtitle,
body.page-my-articles .text-muted {
    color: #dbe6f6 !important;
}

.my-articles-shell {
    max-width: 1380px;
    padding: 30px 32px 42px !important;
}

.my-articles-toolbar {
    display: flex;
    margin-bottom: 22px;
}

.my-articles-create-btn {
    min-height: 46px;
    padding: 0 20px !important;
    border-radius: 14px !important;
    border: 1px solid rgba(245,166,35,0.18) !important;
    background: linear-gradient(135deg, #f4a321 0%, #ffca61 100%) !important;
    color: #08111f !important;
    font-weight: 700 !important;
}

.article-toggle {
    margin-bottom: 24px !important;
}

.article-toggle .toggle-btn {
    min-height: 42px;
    padding: 0 16px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background: rgba(255,255,255,0.04) !important;
    color: #a4b7d1 !important;
}

.article-toggle .toggle-btn.active {
    background: linear-gradient(135deg, rgba(245,166,35,0.18), rgba(245,166,35,0.1)) !important;
    border-color: rgba(245,166,35,0.24) !important;
    color: #ffd37a !important;
}

.my-article-card {
    padding: 22px 24px !important;
    border-radius: 22px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 20%),
        linear-gradient(180deg, rgba(11,17,31,0.98) 0%, rgba(9,14,25,1) 100%) !important;
    box-shadow: 0 22px 48px rgba(0,0,0,0.22) !important;
}

.my-article-title,
.my-article-link {
    color: #f6f8ff !important;
}

.my-article-excerpt,
.my-article-time {
    color: #9caeca !important;
}

.my-article-actions .btn {
    min-height: 40px;
    padding: 0 14px !important;
    border-radius: 12px !important;
    border: 1px solid rgba(255,255,255,0.10) !important;
    background: rgba(255,255,255,0.04) !important;
    color: #d7e3f6 !important;
}

.my-article-actions .my-article-delete-btn {
    color: #ff959d !important;
    border-color: rgba(255,118,132,0.26) !important;
}

.my-articles-empty-card {
    border-radius: 24px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background: linear-gradient(180deg, rgba(11,17,31,0.98) 0%, rgba(9,14,25,1) 100%) !important;
    box-shadow: 0 22px 48px rgba(0,0,0,0.22) !important;
}

.my-articles-empty-title {
    color: #ffffff !important;
}

@media (max-width: 768px) {
    .my-articles-shell {
        padding: 22px 18px 32px !important;
    }
}
</style>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('My Articles', 'All articles you have published'); ?>
<?php flash_messages(); ?>
<div class="page-content my-articles-shell">
    <div class="my-articles-toolbar">
        <a href="/pages/write.php" class="btn btn-primary my-articles-create-btn">Write New Article</a>
    </div>

    <div class="article-toggle">
        <a href="?filter=published" class="toggle-btn <?= $filter === 'published' ? 'active' : '' ?>">Published</a>
        <a href="?filter=draft" class="toggle-btn <?= $filter === 'draft' ? 'active' : '' ?>">Drafts</a>
    </div>

    <?php if (empty($articles)): ?>
        <div class="card card-empty my-articles-empty-card">
            <h3 class="my-articles-empty-title">
                <?= $filter === 'draft' ? 'No drafts yet' : 'No published articles yet' ?>
            </h3>
            <p class="text-muted my-articles-empty-copy">
                You have not published anything yet. Write your first article.
            </p>
            <a href="/pages/write.php" class="btn btn-primary my-articles-create-btn">Write Article</a>
        </div>
    <?php else: ?>
        <div class="my-articles-list">
            <?php foreach ($articles as $article): ?>
                <div class="card my-article-card">
                    <div class="my-article-row">
                        <div class="my-article-info">
                            <div class="my-article-meta">
                                <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                                <?= trust_badge($article->trustScore) ?>
                                <span class="text-muted my-article-time"><?= relative_time($article->publishedAt) ?></span>
                            </div>

                            <h3 class="my-article-title">
                                <a href="/pages/article.php?id=<?= $article->id ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>" class="my-article-link">
                                    <?= htmlspecialchars($article->title) ?>
                                </a>
                            </h3>

                            <p class="my-article-excerpt"><?= htmlspecialchars(mb_substr($article->excerpt, 0, 120)) ?></p>
                        </div>

                        <div class="my-article-actions">
                            <a href="/pages/write.php?id=<?= $article->id ?>" class="btn btn-ghost btn-sm">Edit</a>
                            <form method="POST">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="article_id" value="<?= $article->id ?>">
                                <button
                                    type="submit"
                                    class="btn btn-ghost btn-sm my-article-delete-btn"
                                    onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($article->title)) ?>\'? This cannot be undone.')">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</main>
</div>
<link rel="stylesheet" href="/public/css/myarticles.css">
<?php page_foot(); ?>
