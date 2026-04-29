<?php
// writer articles page for category_admin users
// shows all articles by a specific writer in the admin's assigned category

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new AuthController();
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
$assignedCategory = assigned_category_for_expert((int) $user->id);

if (!$assignedCategory) {
    header('Location: /pages/category-writers.php');
    exit;
}

$writerId = (int) ($_GET['writer_id'] ?? 0);

if (!$writerId) {
    header('Location: /pages/category-writers.php');
    exit;
}

// fetch the writer's info
$writer = DB::first(
    'SELECT id, full_name, avatar_url, bio FROM users WHERE id = ?',
    [$writerId]
);

if (!$writer) {
    header('Location: /pages/category-writers.php');
    exit;
}

// fetch all articles by this writer in the admin's category
$articles = $articleCtrl->getByAuthorAndCategory($writerId, (int) $assignedCategory['id']);

$initial = strtoupper(mb_substr($writer['full_name'], 0, 1)) ?: '?';
$articleWord = count($articles) === 1 ? 'article' : 'articles';

page_head(htmlspecialchars($writer['full_name']) . ' – Articles');
?>
<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php flash_messages(); ?>

<?php
dash_header(
    htmlspecialchars($writer['full_name']),
    htmlspecialchars($assignedCategory['name']) . ' – articles by this writer'
);
?>

<div class="page-content">

    <a href="/pages/category-writers.php" class="back-link" style="margin-bottom:20px;display:inline-flex">
        <img src="/public/icons/backicon.png" alt="Back" class="back-icon">
        Back to Writers
    </a>

    <div class="writer-profile-bar">
        <div class="writer-avatar-lg">
            <?php if (!empty($writer['avatar_url'])): ?>
                <img src="/public/<?= htmlspecialchars($writer['avatar_url']) ?>"
                     alt="<?= htmlspecialchars($writer['full_name']) ?>">
            <?php else: ?>
                <?= htmlspecialchars($initial) ?>
            <?php endif; ?>
        </div>
        <div class="writer-profile-info">
            <div class="writer-name" style="font-size:18px"><?= htmlspecialchars($writer['full_name']) ?></div>
            <div class="writer-article-count">
                <?= count($articles) ?> <?= htmlspecialchars($assignedCategory['name']) ?> <?= $articleWord ?>
            </div>
            <?php if (!empty($writer['bio'])): ?>
                <div class="writer-bio" style="margin-top:6px"><?= htmlspecialchars($writer['bio']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <?php if (empty($articles)): ?>
        <p class="text-muted">No articles found for this writer in the <strong><?= htmlspecialchars($assignedCategory['name']) ?></strong> category.</p>

    <?php else: ?>
        <div class="article-grid">
            <?php foreach ($articles as $article): ?>
                <?php article_card($article, $user); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>
</main>
</div>

<?php page_foot(); ?>
