<?php

//for users own articles
//can CRUD
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();

$auth->requireAuth();
$user = $auth->currentUser();

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

//fetch this users articles from articlecontroller
$articles = $articleCtrl->getByAuthor($user->id);

page_head('My Articles');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('My Articles', 'All articles you have published'); ?>
<?php flash_messages(); ?>
<div class="page-content">

    <div class="flex gap-2 mb-6">
        <a href="/pages/write.php" class="btn btn-primary">✏️ Write New Article</a>
    </div>

    <?php if (empty($articles)): ?>
        <div class="card" style="text-align:center;padding:48px 32px">
            <div style="font-size:48px;margin-bottom:16px">📝</div>
            <h3 style="font-size:18px;font-weight:700;margin-bottom:8px">No articles yet</h3>
            <p class="text-muted" style="margin-bottom:24px">You haven't published anything yet. Write your first article!</p>
            <a href="/pages/write.php" class="btn btn-primary">Write Article</a>
        </div>
    <?php else: ?>
        <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($articles as $article): ?>
            <div class="card" style="padding:20px 24px">
                <div class="flex items-center gap-4">

                    <!--article display shit-->
                    <div style="flex:1;min-width:0">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
                            <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                            <?= trust_badge($article->trustScore) ?>
                            <span class="text-muted" style="font-size:12px;margin-left:auto"><?= relative_time($article->publishedAt) ?></span>
                        </div>
                        <h3 style="font-size:16px;font-weight:700;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <a href="/pages/article.php?id=<?= $article->id ?>" style="color:inherit;text-decoration:none">
                                <?= htmlspecialchars($article->title) ?>
                            </a>
                        </h3>
                        <p class="text-muted" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                            <?= htmlspecialchars(mb_substr($article->excerpt, 0, 120)) ?>
                        </p>
                    </div>

                    <!--edit and delete btns-->
                    <div class="flex gap-2" style="flex-shrink:0">
      
                        <a href="/pages/write.php?id=<?= $article->id ?>"
                           class="btn btn-ghost btn-sm">✏️ Edit</a>
                        <form method="POST" style="margin:0">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="article_id" value="<?= $article->id ?>">
                            <button type="submit" class="btn btn-ghost btn-sm"
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
<?php page_foot(); ?>