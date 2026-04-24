<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role !== 'category_admin') {
    header('Location: /dashboard.php');
    exit;
}

$assignedCategory = assigned_category_for_expert((int)$user->id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $assignedCategory) {
    $action = $_POST['action'] ?? '';
    $articleId = (int)($_POST['article_id'] ?? 0);

    $belongs = $articleId ? DB::first(
        'SELECT id FROM articles WHERE id = ? AND category_id = ?',
        [$articleId, (int)$assignedCategory['id']]
    ) : null;

    if (!$belongs) {
        redirect('/pages/flagged-articles.php', 'Article not found in your category.');
    }

    if ($action === 'dismiss_flags') {
        $result = $adminCtrl->dismissFlags($articleId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'review_category_content', 'Article', $articleId, 'Dismissed flags for category article ID ' . $articleId);
            redirect('/pages/flagged-articles.php', null, 'Flags dismissed. Article remains published.');
        }
        redirect('/pages/flagged-articles.php', $result['error']);
    }

    if ($action === 'confirm_flag') {
        $result = $adminCtrl->confirmFlag($articleId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'review_category_content', 'Article', $articleId, 'Confirmed flag and hid category article ID ' . $articleId);
            redirect('/pages/flagged-articles.php', null, 'Article has been hidden from public.');
        }
        redirect('/pages/flagged-articles.php', $result['error']);
    }

    if ($action === 'restore_and_dismiss') {
        $result = $adminCtrl->restoreAndDismissFlags($articleId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'review_category_content', 'Article', $articleId, 'Restored category article and dismissed flags for ID ' . $articleId);
            redirect('/pages/flagged-articles.php', null, 'Flags dismissed. Article has been restored to published.');
        }
        redirect('/pages/flagged-articles.php', $result['error']);
    }
}

$detailId = (int)($_GET['id'] ?? 0);
$detailArticle = null;
$flagReports = [];

if ($detailId && $assignedCategory) {
    $detailArticle = $adminCtrl->getArticleById($detailId);

    if (!$detailArticle || $detailArticle->categoryId !== (int)$assignedCategory['id']) {
        $detailArticle = null;
    } else {
        $flagReports = $adminCtrl->getFlagsByArticle($detailId);
    }
}

$flaggedArticles = $assignedCategory
    ? $adminCtrl->getFlaggedArticlesByCategory((int)$assignedCategory['id'])
    : [];

page_head('Flagged Articles');
?>

<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php flash_messages(); ?>

<?php if ($detailArticle): ?>

    <?php
    $subtitle = htmlspecialchars($assignedCategory['name']) . ' - flag reports';
    dash_header(htmlspecialchars($detailArticle->title), $subtitle);
    ?>

    <div class="page-content">
        <div class="flex gap-2 mb-6">
            <a href="/pages/flagged-articles.php" class="btn btn-ghost btn-sm">Back to Flagged Articles</a>
        </div>

        <div class="card mb-6" style="padding:20px 24px">
            <div class="flex items-center justify-between mb-2">
                <span class="category-tag <?= category_theme_class($detailArticle->categoryName) ?>"><?= htmlspecialchars($detailArticle->categoryName) ?></span>
                <?= trust_badge($detailArticle->trustScore) ?>
            </div>
            <h2 style="font-size:22px;font-weight:700;font-family:Georgia,serif;margin-bottom:8px">
                <?= htmlspecialchars($detailArticle->title) ?>
            </h2>
            <p class="text-muted" style="font-size:13px;margin-bottom:4px">
                By <strong><?= htmlspecialchars($detailArticle->authorName) ?></strong>
                &nbsp; / &nbsp; <?= date('F j, Y', strtotime($detailArticle->publishedAt)) ?>
                &nbsp; / &nbsp; <?= count($flagReports) ?> flag<?= count($flagReports) !== 1 ? 's' : '' ?>
                &nbsp; / &nbsp; Status: <strong><?= htmlspecialchars($detailArticle->status) ?></strong>
            </p>
            <p style="font-size:14px;margin-top:8px"><?= htmlspecialchars($detailArticle->excerpt) ?></p>

            <div class="flex items-center gap-3 mt-6">
                <a href="/pages/article.php?id=<?= $detailArticle->id ?>&return=<?= urlencode('/pages/flagged-articles.php?id=' . $detailArticle->id) ?>"
                   class="btn btn-ghost btn-sm" target="_blank">
                    View Article
                </a>

                <?php if ($detailArticle->status === 'published'): ?>
                    <form method="POST" style="margin:0"
                          onsubmit="return confirm('Dismiss all flags? The article will remain published.')">
                        <input type="hidden" name="action" value="dismiss_flags">
                        <input type="hidden" name="article_id" value="<?= $detailArticle->id ?>">
                        <button type="submit" class="btn btn-secondary btn-sm">Dismiss Flags</button>
                    </form>

                    <form method="POST" style="margin:0"
                          onsubmit="return confirm('Confirm flag? This will hide the article from the public.')">
                        <input type="hidden" name="action" value="confirm_flag">
                        <input type="hidden" name="article_id" value="<?= $detailArticle->id ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="white-space:nowrap">Confirm Flag and Hide Article</button>
                    </form>
                <?php else: ?>
                    <span class="alert alert-error" style="display:inline-block;padding:6px 14px;font-size:13px">
                        Article is already hidden.
                    </span>

                    <form method="POST" style="margin:0"
                          onsubmit="return confirm('Dismiss all flags and restore the article to published?')">
                        <input type="hidden" name="action" value="restore_and_dismiss">
                        <input type="hidden" name="article_id" value="<?= $detailArticle->id ?>">
                        <button type="submit" class="btn btn-secondary btn-sm">Dismiss Flags and Restore</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <h3 style="font-size:16px;font-weight:700;margin-bottom:16px">Flag Reports (<?= count($flagReports) ?>)</h3>

        <?php if (empty($flagReports)): ?>
            <p class="text-muted">No flag reports found for this article.</p>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:12px">
                <?php foreach ($flagReports as $report): ?>
                    <div class="card" style="padding:16px 20px">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="author-avatar" style="width:32px;height:32px;font-size:12px;flex-shrink:0">
                                <?= htmlspecialchars(strtoupper(mb_substr($report['reporter_name'], 0, 1)) ?: '?') ?>
                            </div>
                            <div>
                                <span style="font-weight:600;font-size:14px"><?= htmlspecialchars($report['reporter_name']) ?></span>
                                <span class="text-muted" style="font-size:12px;margin-left:8px">
                                    <?= relative_time($report['created_at']) ?>
                                </span>
                            </div>
                            <span class="category-tag" style="margin-left:auto"><?= htmlspecialchars($report['reason']) ?></span>
                        </div>
                        <?php if (!empty($report['details'])): ?>
                            <p style="font-size:14px;color:var(--fg);margin:0">
                                <?= htmlspecialchars($report['details']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

<?php else: ?>

    <?php
    $subtitle = $assignedCategory
        ? htmlspecialchars($assignedCategory['name']) . ' - flagged articles requiring review'
        : '';
    dash_header('Flagged Articles', $subtitle);
    ?>

    <div class="page-content">

        <?php if (!$assignedCategory): ?>
            <div class="alert alert-error">You are not assigned to any category.</div>

        <?php elseif (empty($flaggedArticles)): ?>
            <p class="text-muted">No flagged articles in the <strong><?= htmlspecialchars($assignedCategory['name']) ?></strong> category.</p>

        <?php else: ?>
            <p class="article-count" style="margin-bottom:16px">
                <?= count($flaggedArticles) ?> flagged article<?= count($flaggedArticles) !== 1 ? 's' : '' ?>
            </p>

            <div style="display:flex;flex-direction:column;gap:12px">
                <?php foreach ($flaggedArticles as $article): ?>
                    <div class="card" style="padding:18px 22px">
                        <div class="flex items-center justify-between" style="flex-wrap:wrap;gap:12px">

                            <div style="flex:1;min-width:0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="category-tag <?= category_theme_class($article->categoryName) ?>"><?= htmlspecialchars($article->categoryName) ?></span>
                                    <?= trust_badge($article->trustScore) ?>
                                    <?php if ($article->status === 'suspended'): ?>
                                        <span class="role-badge" style="background:var(--danger);color:#fff;font-size:11px">Hidden</span>
                                    <?php endif; ?>
                                </div>
                                <h3 style="font-size:16px;font-weight:700;font-family:Georgia,serif;margin-bottom:4px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <?= htmlspecialchars($article->title) ?>
                                </h3>
                                <p class="text-muted" style="font-size:13px">
                                    By <?= htmlspecialchars($article->authorName) ?>
                                    &nbsp; / &nbsp; <?= relative_time($article->publishedAt) ?>
                                    &nbsp; / &nbsp; <span style="color:var(--danger);font-weight:600"><?= $article->flagCount ?> flag<?= $article->flagCount !== 1 ? 's' : '' ?></span>
                                </p>
                            </div>

                            <div class="flex gap-2" style="flex-shrink:0">
                                <a href="/pages/flagged-articles.php?id=<?= $article->id ?>" class="btn btn-ghost btn-sm">
                                    Review
                                </a>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>

<?php endif; ?>

</main>
</div>

<?php page_foot(); ?>
