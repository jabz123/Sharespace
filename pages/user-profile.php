<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/UserController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$auth->requireAuth();
$currentUser = $auth->currentUser();

$userCtrl = new UserController();
$articleCtrl = new ArticleController();

$userId = (int)($_GET['id'] ?? 0);

if (!$userId) {
    redirect('/pages/users.php', 'Invalid user.');
}

$profile = $userCtrl->getUserById($userId);

if (!$profile) {
    redirect('/pages/users.php', 'User not found.');
}

// pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// total articles count
$totalArticles = $articleCtrl->countByAuthor($userId);
$totalPages = (int) ceil($totalArticles / $perPage);

// paginated articles
$articles = $articleCtrl->getByAuthorPaginated($userId, $perPage, $offset);

//uses profile nmame as page title 
page_head($profile['full_name']);
?>
<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($currentUser); ?>

<main>
<?php dash_header($profile['full_name'], 'User Profile'); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <!-- PROFILE HEADER -->
    <div class="profile-header">

        <div class="profile-avatar">
            <?php if (!empty($profile['avatar_url'])): ?>
                <img src="/public/<?= htmlspecialchars($profile['avatar_url']) ?>">
            <?php else: ?>
                <?= strtoupper(substr($profile['full_name'], 0, 1)) ?>
            <?php endif; ?>
        </div>

        <div class="profile-info">
            <h2><?= htmlspecialchars($profile['full_name']) ?></h2>

            <div class="profile-role <?= htmlspecialchars($profile['role']) ?>">
                <?= ucfirst($profile['role']) ?>
            </div>

            <div class="profile-stats">
                <?= (int)$profile['article_count'] ?>
                <?= $profile['article_count'] == 1 ? 'Article' : 'Articles' ?>
            </div>

            <?php if (!empty($profile['bio'])): ?>
                <p class="profile-bio">
                    <?= nl2br(htmlspecialchars($profile['bio'])) ?>
                </p>
            <?php endif; ?>
        </div>

    </div>

    <!-- ARTICLES -->
    <div class="profile-articles">

        <h3>Published Articles</h3>

        <?php if (empty($articles)): ?>
            <p class="no-articles">No articles yet.</p>
        <?php else: ?>
            <div class="article-grid">
                
                <?php foreach ($articles as $article):
                    article_card($article, $currentUser);
                endforeach; ?>
            </div>
            <?php if ($totalPages > 1): ?>
        <div class="pagination">

            <a class="page-btn <?= $page == 1 ? 'disabled' : '' ?>"
            href="?id=<?= $userId ?>&page=1">
                First
            </a>

            <a class="page-btn <?= $page == 1 ? 'disabled' : '' ?>"
            href="?id=<?= $userId ?>&page=<?= max(1, $page - 1) ?>">
                Previous
            </a>

            <div class="page-info">
                <?= $page ?> of <?= $totalPages ?>
            </div>

            <a class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>"
            href="?id=<?= $userId ?>&page=<?= min($totalPages, $page + 1) ?>">
                Next
            </a>

            <a class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>"
            href="?id=<?= $userId ?>&page=<?= $totalPages ?>">
                Last
            </a>

        </div>
        <?php endif; ?>
        <?php endif; ?>

    </div>

</div>
</main>
</div>

<?php page_foot(); ?>