<?php
// category articles page for category_admin users
// shows all articles belonging to the category the admin is assigned to

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/db.php';

$auth        = new AuthController();
$articleCtrl = new ArticleController();

// ensure user is logged in
$auth->requireAuth();

// get current logged in user
$user = $auth->currentUser();

// only category_admin users may access this page
if ($user->role !== 'category_admin') {
    header('Location: /dashboard.php');
    exit;
}

// get the category this admin is assigned to
$assignedCategory = DB::first(
    'SELECT id, name FROM categories WHERE admin_user_id = ?',
    [$user->id]
);

// fetch all articles for the assigned category
$articles = $assignedCategory
    ? $articleCtrl->getAllByCategory((int)$assignedCategory['id'])
    : [];

page_head('Category Articles');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php
$subtitle = $assignedCategory
    ? htmlspecialchars($assignedCategory['name']) . ' – all articles in your category'
    : 'No category assigned';
dash_header('Category Articles', $subtitle);
?>
<?php flash_messages(); ?>

<div class="page-content">

    <?php if (!$assignedCategory): ?>
        <div class="alert alert-error">You are not assigned to any category.</div>

    <?php elseif (empty($articles)): ?>
        <p class="text-muted">No articles found in the <strong><?= htmlspecialchars($assignedCategory['name']) ?></strong> category yet.</p>

    <?php else: ?>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                    <tr style="border-bottom:2px solid var(--border)">
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">ID</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Title</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Author</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Published</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Views</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Status</th>
                        <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $article): ?>
                    <?php $isSuspended = $article->status === 'suspended'; ?>
                    <tr style="border-bottom:1px solid var(--border);<?= $isSuspended ? 'opacity:0.55' : '' ?>">
                        <td style="padding:12px 8px;color:var(--muted)">#<?= $article->id ?></td>
                        <td style="padding:12px 8px;max-width:260px">
                            <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                <?= htmlspecialchars($article->title) ?>
                            </div>
                        </td>
                        <td style="padding:12px 8px"><?= htmlspecialchars($article->authorName) ?></td>
                        <td style="padding:12px 8px;color:var(--muted);white-space:nowrap">
                            <?= relative_time($article->publishedAt) ?>
                        </td>
                        <td style="padding:12px 8px;color:var(--muted)"><?= $article->viewCount ?></td>
                        <td style="padding:12px 8px">
                            <?php if ($isSuspended): ?>
                                <span style="font-size:11px;font-weight:600;color:var(--danger);
                                             background:#fff0f0;padding:2px 8px;border-radius:99px;
                                             border:1px solid var(--danger)">🚫 Suspended</span>
                            <?php else: ?>
                                <span style="font-size:11px;font-weight:600;color:var(--success);
                                             background:#f0fff4;padding:2px 8px;border-radius:99px;
                                             border:1px solid var(--success)">✅ Published</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px 8px;text-align:right">
                            <a href="/pages/article.php?id=<?= $article->id ?>" target="_blank"
                               class="btn btn-ghost btn-sm">👁 View</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p style="margin-top:12px;font-size:13px;color:var(--muted)">
            <?= count($articles) ?> article<?= count($articles) !== 1 ? 's' : '' ?> in
            <strong><?= htmlspecialchars($assignedCategory['name']) ?></strong>
        </p>
    <?php endif; ?>

</div>
</main>
</div>

<?php page_foot(); ?>
