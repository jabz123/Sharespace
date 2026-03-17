<?php
// page that displays all articles written by the logged in user
// allows the user to view, edit, or delete their own articles
// retrieves the user's articles from ArticleController
// handles article deletion using a POST request


//for users own articles
//can CRUD
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();
$auth->requireAuth();
$user = $auth->currentUser();

//determine which tab user is viewing (published / draft)
$filter = $_GET['filter'] ?? 'published';

//fetch articles based on filter
if ($filter === 'draft') {
    $articles = $articleCtrl->getDraftsByAuthor($user->id);
} else {
    $articles = $articleCtrl->getByAuthor($user->id);
}

//handle delete POST
//usually its only REST APIs that use DELETE method
//but here no js so will use POST with hidden delete action 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $result = $articleCtrl->delete((int)($_POST['article_id'] ?? 0), $user->id);
    if (isset($result['ok'])) {
        redirect('/pages/my-articles.php', null, 'Article deleted.');
    }
    redirect('/pages/my-articles.php', $result['error']);
}
page_head('My Articles');

?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('My Articles', 'All articles you have published'); ?>
<?php flash_messages(); ?>
<div class="page-content">
    <!--top action button-->
    <div class="flex gap-2 mb-6">
        <a href="/pages/write.php" class="btn btn-primary">✏️ Write New Article</a>
    </div>
    <!--filter toggle (published / drafts)-->
    <div class="article-toggle">
        <a href="?filter=published" 
           class="toggle-btn <?= $filter === 'published' ? 'active' : '' ?>">
            📰 Published
        </a>
        <a href="?filter=draft" 
           class="toggle-btn <?= $filter === 'draft' ? 'active' : '' ?>">
            📝 Drafts
        </a>
    </div>
    <?php if (empty($articles)): ?>
        <!--empty state-->
        <div class="card" style="text-align:center;padding:48px 32px">
            <div style="font-size:48px;margin-bottom:16px">📝</div>
            <h3 style="font-size:18px;font-weight:700;margin-bottom:8px">
                <?= $filter === 'draft' ? 'No drafts yet' : 'No published articles yet' ?>
            </h3>
            <p class="text-muted" style="margin-bottom:24px">
                You haven't published anything yet. Write your first article!
            </p>
            <a href="/pages/write.php" class="btn btn-primary">Write Article</a>
        </div>
    <?php else: ?>
        <!--article list container-->
        <div class="my-articles-list">

        <?php foreach ($articles as $article): ?>
        
        <!--single article card-->
        <div class="card my-article-card">
            <div class="my-article-row">
                <!--article display shit-->
        <div class="my-article-info">
                <!--top row: category + trust + time-->
        <div class="my-article-meta">
            <span class="category-tag">
                <?= htmlspecialchars($article->categoryName) ?>
            </span>
            <?= trust_badge($article->trustScore) ?>
            <span class="text-muted my-article-time">
                <?= relative_time($article->publishedAt) ?>
            </span>
            </div>
        <!--title-->
        <h3 class="my-article-title">
        <a href="/pages/article.php?id=<?= $article->id ?>" class="my-article-link">
            <?= htmlspecialchars($article->title) ?>
        </a>
        </h3>
        <!--excerpt-->
        <p class="my-article-excerpt">
            <?= htmlspecialchars(mb_substr($article->excerpt, 0, 120)) ?>
        </p>
    </div>
        <!--edit and delete btns-->
    <div class="my-article-actions">
        <a href="/pages/write.php?id=<?= $article->id ?>"
            class="btn btn-ghost btn-sm">✏️ Edit</a>
    <form method="POST">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="article_id" value="<?= $article->id ?>">
        <button type="submit"
            class="btn btn-ghost btn-sm"
            style="color:var(--danger, #e53e3e)"
            onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($article->title)) ?>\'? This cannot be undone.')">
            🗑 Delete
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