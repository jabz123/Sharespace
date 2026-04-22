<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/UserController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

$userCtrl = new UserController();

$search = $_GET['search'] ?? null;

// pagination ready (same style as browse page)
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

$totalUsers = $userCtrl->countUsers($search, $user->id);
$totalPages = (int) ceil($totalUsers / $perPage);

$users = $userCtrl->searchUsers($search, $perPage, $offset, $user->id);

page_head('Discover Users');
?>

<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>

<main>
<?php dash_header('Discover Users', 'Find and explore other writers'); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <!-- SEARCH -->
    <div class="users-search-box">
        <form method="GET" class="users-search-form">
            <input
                type="text"
                name="search"
                placeholder="Search users..."
                value="<?= htmlspecialchars($search ?? '') ?>"
            >
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- USERS GRID -->
    <div class="users-grid">

        <?php if (empty($users)): ?>
            <div class="no-users">No users found.</div>
        <?php else: ?>

            <?php foreach ($users as $u): ?>
                <a href="/pages/user-profile.php?id=<?= $u['id'] ?>" class="user-card">

                    <div class="user-avatar">
                        <?php if (!empty($u['avatar_url'])): ?>
                            <img src="/public/<?= htmlspecialchars($u['avatar_url']) ?>">
                        <?php else: ?>
                            <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                        <?php endif; ?>
                    </div>

                <div class="user-info">
                    <div class="user-name">
                        <?= htmlspecialchars($u['full_name']) ?>
                    </div>

                    <div class="user-role <?= htmlspecialchars($u['role']) ?>">
                        <?= ucfirst($u['role']) ?>
                    </div>

                    <div class="user-articles">
                        <?= (int)$u['article_count'] ?> 
                        <?= $u['article_count'] == 1 ? 'Published Article' : 'Published Articles' ?>
                    </div>
                </div>

                </a>
            <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <!-- PAGINATION (ready style like browse) -->
    <?php if ($totalPages > 1): ?>
        <div class="pagination">

            <!-- FIRST -->
            <a class="page-btn <?= $page == 1 ? 'disabled' : '' ?>"
            href="?page=1&search=<?= urlencode($search ?? '') ?>">
                First
            </a>

            <!-- PREVIOUS -->
            <a class="page-btn <?= $page == 1 ? 'disabled' : '' ?>"
            href="?page=<?= max(1, $page - 1) ?>&search=<?= urlencode($search ?? '') ?>">
                Previous
            </a>

            <!-- PAGE INFO -->
            <div class="page-info">
                <?= $page ?> of <?= $totalPages ?>
            </div>

            <!-- NEXT -->
            <a class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>"
            href="?page=<?= min($totalPages, $page + 1) ?>&search=<?= urlencode($search ?? '') ?>">
                Next
            </a>

            <!-- LAST -->
            <a class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>"
            href="?page=<?= $totalPages ?>&search=<?= urlencode($search ?? '') ?>">
                Last
            </a>

        </div>
        <?php endif; ?>

</div>
</main>
</div>

<?php page_foot(); ?>