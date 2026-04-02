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

    <div style="margin-bottom:24px">
        <h1 class="dash-title" style="font-size:20px">Category Articles</h1>
        <?php if ($assignedCategory): ?>
        <p class="dash-subtitle"><?= htmlspecialchars($assignedCategory['name']) ?> – all articles in your category</p>
        <?php endif; ?>
    </div>

    <?php if (!$assignedCategory): ?>
        <div class="alert alert-error">You are not assigned to any category.</div>

    <?php else: ?>
        <!-- Articles counter stat card -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:28px">
            <div class="card" style="padding:20px 24px;text-align:center">
                <div style="font-size:28px;margin-bottom:6px">📰</div>
                <div style="font-size:24px;font-weight:800;color:var(--primary)"><?= count($articles) ?></div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px">Articles</div>
            </div>
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
