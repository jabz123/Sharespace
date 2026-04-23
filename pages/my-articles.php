<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();
$auth->requireAuth();
$user = $auth->currentUser();

$filter = $_GET['filter'] ?? 'published';
if ($filter === 'draft') {
    $articles = $articleCtrl->getDraftsByAuthor($user->id);
} elseif ($filter === 'pending') {
    $articles = $articleCtrl->getPendingByAuthor($user->id);
} else {
    $articles = $articleCtrl->getByAuthor($user->id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $result = $articleCtrl->delete((int)($_POST['article_id'] ?? 0), $user->id);
    if (isset($result['ok'])) {
        redirect('/pages/my-articles.php', null, 'Article deleted.');
    }
    redirect('/pages/my-articles.php', $result['error']);
}

page_head('My Articles');
?>
<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('My Articles', 'Track your published, pending, and draft articles'); ?>
<?php flash_messages(); ?>
<div class="page-content my-articles-shell">
    <div class="my-articles-toolbar">
        <a href="/pages/write.php" class="btn btn-primary my-articles-create-btn">Write New Article</a>
    </div>

    <div class="article-toggle">
        <a href="?filter=published" class="toggle-btn <?= $filter === 'published' ? 'active' : '' ?>">Published</a>
        <a href="?filter=pending" class="toggle-btn <?= $filter === 'pending' ? 'active' : '' ?>">Pending</a>
        <a href="?filter=draft" class="toggle-btn <?= $filter === 'draft' ? 'active' : '' ?>">Drafts</a>
    </div>

    <?php if (empty($articles)): ?>
        <div class="card card-empty my-articles-empty-card">
            <h3 class="my-articles-empty-title">
                <?= $filter === 'draft' ? 'No drafts yet' : ($filter === 'pending' ? 'No pending articles' : 'No published articles yet') ?>
            </h3>
            <p class="text-muted my-articles-empty-copy">
                <?= $filter === 'draft'
                    ? 'Drafts and rejected articles you need to revise will appear here.'
                    : ($filter === 'pending'
                        ? 'Articles waiting for category expert review will appear here.'
                        : 'You have not published anything yet. Write your first article.') ?>
            </p>
            <a href="/pages/write.php" class="btn btn-primary my-articles-create-btn">Write Article</a>
        </div>
    <?php else: ?>
        <div class="my-articles-list">
            <?php foreach ($articles as $article): ?>
                <?php
                $statusTimeLabel = $article->status === 'published' ? 'Published' : 'Updated';
                $statusTimeValue = $article->status === 'published' ? $article->publishedAt : $article->updatedAt;
                ?>
                <div class="card my-article-card">
                    <div class="my-article-row">
                        <div class="my-article-info">
                            <div class="my-article-meta">
                                <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                                <?= trust_badge($article->trustScore) ?>
                                <?php if ($article->status === 'pending'): ?>
                                    <span class="role-badge" style="background:#f59e0b;color:#111827">Pending Review</span>
                                <?php elseif ($article->status === 'draft' && $article->reviewNoticePending): ?>
                                    <span class="role-badge" style="background:#ef4444;color:#fff">Rejected</span>
                                <?php endif; ?>
                                <span class="text-muted my-article-time"><?= $statusTimeLabel ?> <?= relative_time($statusTimeValue) ?></span>
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
<?php page_foot(); ?>