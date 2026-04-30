<?php
// category articles page for category_admin users
// shows all articles belonging to the category the admin is assigned to

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

$search = trim($_GET['search'] ?? '') ?: null;

// fetch all articles for the assigned category
$articles = $assignedCategory
    ? $articleCtrl->getAllByCategory((int) $assignedCategory['id'], $search)
    : [];

page_head('Category Articles');
?>
<div class="dashboard-layout user-dashboard-shell"><?php sidebar($user); ?>
    <main>
        <?php flash_messages(); ?>

        <?php //display category name in header if assigned, otherwise show generic header
        $subtitle = $assignedCategory ? htmlspecialchars($assignedCategory['name']) . ' – all articles in your category' : '';
        dash_header('Category Articles', $subtitle);
        ?>

        <div class="page-content">

            <?php if (!$assignedCategory): ?>
                <div class="alert alert-error">You are not assigned to any category.</div>

            <?php else: ?>

                <div class="filter-row">
                    <p class="article-count"><?= count($articles) ?> article<?= count($articles) !== 1 ? 's' : '' ?></p>

                    <form method="GET" class="browse-hero-search" style="max-width:340px">

                        <button type="submit" class="search-btn" aria-label="Search">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8" />
                                <path d="M21 21l-4.35-4.35" />
                            </svg>
                        </button>

                        <div class="search-input-wrapper">
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                placeholder="Search by title or writer"
                                value="<?= htmlspecialchars($search ?? '') ?>">
                            <button type="button" id="clearSearch" class="clear-btn" aria-label="Clear">
                                <img src="/public/icons/clearicon.png" alt="">
                            </button>
                        </div>

                    </form>

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