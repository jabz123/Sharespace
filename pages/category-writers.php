<?php
// category writers page for category_admin users
// shows all writers who have published at least one article in the admin's assigned category

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new AuthController();

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

// fetch all writers who have at least one published article in this category,
// along with their article count for that category
$writers = [];
if ($assignedCategory) {
    $writers = DB::query(
        'SELECT u.id, u.full_name, u.avatar_url, u.bio, COUNT(a.id) AS article_count
         FROM users u
         JOIN articles a ON a.author_id = u.id
         WHERE a.category_id = ? AND a.status = \'published\'
         GROUP BY u.id, u.full_name, u.avatar_url, u.bio
         ORDER BY article_count DESC, u.full_name ASC',
        [(int) $assignedCategory['id']]
    );
}

page_head('Category Writers');
?>

<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php flash_messages(); ?>

<?php
$subtitle = $assignedCategory
    ? htmlspecialchars($assignedCategory['name']) . ' – writers in your category'
    : '';
dash_header('Category Writers', $subtitle);
?>

<div class="page-content">

    <?php if (!$assignedCategory): ?>
        <div class="alert alert-error">You are not assigned to any category.</div>

    <?php elseif (empty($writers)): ?>
        <p class="text-muted">No writers have published articles in the <strong><?= htmlspecialchars($assignedCategory['name']) ?></strong> category yet.</p>

    <?php else: ?>
        <div class="writers-toolbar">
            <p class="article-count"><?= count($writers) ?> writer<?= count($writers) !== 1 ? 's' : '' ?></p>
            <div class="writers-search-wrapper">
                <input type="text" id="writerSearch" class="writers-search-input" placeholder="Search writers…" autocomplete="off">
                <button type="button" id="clearWriterSearch" class="writers-search-clear" style="display:none">
                    <img src="/public/icons/clearicon.png" alt="Clear">
                </button>
            </div>
        </div>

        <p id="writerNoResults" class="text-muted" style="display:none">No writers match your search.</p>

        <div class="writer-grid" id="writerGrid">
            <?php foreach ($writers as $writer):
                $initial = strtoupper(mb_substr($writer['full_name'], 0, 1)) ?: '?';
                $articleWord = (int) $writer['article_count'] === 1 ? 'article' : 'articles';
                $writerUrl = '/pages/writer-articles.php?writer_id=' . (int) $writer['id'];
                ?>
            <a href="<?= $writerUrl ?>" class="writer-card-link" data-name="<?= htmlspecialchars(strtolower($writer['full_name'])) ?>">
            <div class="writer-card">
                <div class="writer-avatar-lg">
                    <?php if (!empty($writer['avatar_url'])): ?>
                        <img src="/public/<?= htmlspecialchars($writer['avatar_url']) ?>"
                             alt="<?= htmlspecialchars($writer['full_name']) ?>">
                    <?php else: ?>
                        <?= htmlspecialchars($initial) ?>
                    <?php endif; ?>
                </div>
                <div class="writer-name"><?= htmlspecialchars($writer['full_name']) ?></div>
                <div class="writer-article-count">
                    <?= (int) $writer['article_count'] ?> <?= htmlspecialchars($assignedCategory['name']) ?> <?= $articleWord ?>
                </div>
                <?php if (!empty($writer['bio'])): ?>
                    <div class="writer-bio"><em><?= htmlspecialchars($writer['bio']) ?></em></div>
                <?php endif; ?>
            </div>
            </a>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>

</div>
</main>
</div>

<?php page_foot(); ?>
