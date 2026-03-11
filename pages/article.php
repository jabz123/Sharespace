<?php
// page that displays a full article and its comments
// retrieves article and comment data from the controllers
// checks user authentication and premium access before showing the article
// allows users to post new comments or delete their own comments
// uses layout helper functions to render the page ui

//fetches article and comment shit from controllers
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/controllers/CommentController.php';

//initialise controllers
$auth        = new AuthController();
$articleCtrl = new ArticleController();
$commentCtrl = new CommentController();

$auth->requireAuth();
$user = $auth->currentUser();

$id      = (int)($_GET['id'] ?? 0);
$article = $id ? $articleCtrl->getById($id) : null;

if (!$article) {
    redirect('/dashboard.php', 'Article not found.');
}

// block free users from viewing image articles
if ($article->imagePath && $user->role !== 'premium') {
    redirect('/dashboard.php', 'This article is available for Premium users only.');
}

//post and delete comment logic 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'comment') {
        $commentCtrl->post($article->id, $user->id, $_POST['comment_body'] ?? '');
        header('Location: /pages/article.php?id=' . $article->id . '#comments');
        exit;
    }

    if ($action === 'delete_comment') {
        $commentCtrl->delete((int)($_POST['comment_id'] ?? 0), $user->id);
        header('Location: /pages/article.php?id=' . $article->id . '#comments');
        exit;
    }
}

//get comments for article, also checks if current user can delete each comment
$comments = $commentCtrl->getForArticle($article->id);
$isAuthor = $user->id === $article->authorId;

page_head($article->title);
?>
<div class="dashboard-layout">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header(htmlspecialchars($article->categoryName), 'Article'); ?>
        <div class="page-content">
            <div class="article-detail">

                <div class="flex items-center gap-2 mb-6">
                    <?php //back button logic, if user come from my articles page then back go there, ifnot back go dashboard
                    $referer  = $_SERVER['HTTP_REFERER'] ?? '';
                    $backUrl  = str_contains($referer, 'my-articles')
                        ? '/pages/my-articles.php'
                        : '/dashboard.php';
                    ?>
                    <a href="<?= $backUrl ?>" class="btn btn-ghost btn-sm">← Back</a>
                    <?php if ($isAuthor): ?>
                        <a href="/pages/write.php?id=<?= $article->id ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                    <?= trust_badge($article->trustScore) ?>
                </div>

                <h1 style="font-size:32px;font-weight:800;font-family:Georgia,serif;line-height:1.2;margin-bottom:16px">
                    <?= htmlspecialchars($article->title) ?>
                </h1>
                 <div class="article-meta">
                    <div class="author-avatar" style="width:42px;height:42px;font-size:16px"><?= htmlspecialchars($article->authorInitial()) ?></div>
                    <div>
                        <p style="font-weight:600;font-size:14px"><?= htmlspecialchars($article->authorName) ?></p>
                        <p class="text-sm text-muted">🕐 <?= date('F j, Y g:i A', strtotime($article->publishedAt)) ?></p>
                    </div>
                </div>
                <?php if (!empty($article->imagePath)): ?>

                <div class="article-banner">
                    <img src="/public/<?= htmlspecialchars($article->imagePath) ?>" alt="Article Image">
                </div>

                <?php endif; ?>
                <?php if (!empty($article->excerpt)): ?>

                <div class="article-summary">
                    <h3 class="summary-title">Brief Summary</h3>
                    <p class="summary-text">
                        <?= htmlspecialchars($article->excerpt) ?>
                    </p>
                </div>
                <?php endif; ?>

                <h3 class="article-content-title">Article</h3>

                <div class="article-body">
                    <?= $article->renderContent() ?>
                </div>

            </div>


            <div id="comments" style="margin-top:48px;padding-top:32px;border-top:2px solid var(--border)">
            <div class="comments-container">

                <h2 style="font-size:20px;font-weight:700;font-family:Georgia,serif;margin-bottom:24px">
                    💬 Comments <span style="font-size:14px;font-weight:400;color:var(--muted)">(<?= count($comments) ?>)</span>
                </h2>


                <div class="card" style="margin-bottom:28px">
                    <form method="POST">
                        <input type="hidden" name="action" value="comment">
                        <div class="form-group" style="margin-bottom:12px">
                            <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">
                                Leave a comment as <span style="color:var(--primary)"><?= htmlspecialchars($user->fullName) ?></span>
                            </label>
                            <textarea name="comment_body" rows="3"
                                placeholder="Share your thoughts on this article…"
                                style="width:100%;resize:vertical"
                                required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                    </form>
                </div>

                <!--comment list-->
                <?php if (empty($comments)): ?>
                    <p class="text-muted" style="text-align:center;padding:32px 0">
                        No comments yet. Be the first to share your thoughts!
                    </p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:16px">
                        <?php foreach ($comments as $comment):
                            $canDelete = $comment->userId === $user->id;
                        ?>
                            <div class="card" style="padding:16px 20px">
                                <div class="flex items-center gap-3" style="margin-bottom:10px">
                                    <div class="author-avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0"><?= htmlspecialchars($comment->initial()) ?></div>
                                    <div style="flex:1">
                                        <span style="font-weight:600;font-size:14px"><?= htmlspecialchars($comment->commenterName) ?></span>
                                        <span class="text-muted" style="font-size:12px;margin-left:8px"><?= relative_time($comment->createdAt) ?></span>
                                    </div>
                                    <?php if ($canDelete): ?>
                                        <form method="POST" style="margin:0">
                                            <input type="hidden" name="action" value="delete_comment">
                                            <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                                            <button type="submit" class="btn btn-ghost btn-sm"
                                                style="font-size:11px;padding:2px 8px;color:var(--muted)"
                                                onclick="return confirm('Delete this comment?')">✕</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <p style="font-size:14px;line-height:1.6;color:var(--fg);margin:0"><?= nl2br(htmlspecialchars($comment->content)) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                </div>
            </div>

        </div>
    </main>
</div>
<?php page_foot(); ?>