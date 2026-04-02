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
<?php flash_messages(); ?>

<div class="page-content">

    <?php if (!$assignedCategory): ?>
        <div class="alert alert-error">You are not assigned to any category.</div>

    <?php else: ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px">
            <h1 class="dash-title" style="font-size:18px;margin:0">📰 <?= htmlspecialchars($assignedCategory['name']) ?></h1>
            <span style="font-size:12px;color:var(--muted);background:var(--secondary);border:1px solid var(--border);border-radius:999px;padding:2px 10px"><?= count($articles) ?> article<?= count($articles) !== 1 ? 's' : '' ?></span>
        </div>

        <?php if (empty($articles)): ?>
            <p class="text-muted">No articles found in the <strong><?= htmlspecialchars($assignedCategory['name']) ?></strong> category yet.</p>

        <?php else: ?>
            <div class="article-grid">
                <?php foreach ($articles as $article): ?>
                    <?php article_card($article, $user); ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>
</main>
</div>

<?php page_foot(); ?>
