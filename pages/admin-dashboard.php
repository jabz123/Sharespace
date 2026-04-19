<?php
// tabs: articles | users | categories | category experts
// only accessible to users with role = 'system_admin'

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

$auth      = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();
$adminCtrl->requireAdmin($user);

// active tab 
$tab = $_GET['tab'] ?? 'articles';

// handle all POST actions 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ARTICLE ACTIONS 
    if ($action === 'suspend_article') {
        $articleId = (int)($_POST['article_id'] ?? 0);
        $result    = $adminCtrl->suspendArticle($articleId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'suspend_article', 'Article', $articleId,
                "Suspended article pending fact-check review (ID: {$articleId})");
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article suspended.');
        }
        redirect('/pages/admin-dashboard.php?tab=articles', $result['error']);
    }

    if ($action === 'unsuspend_article') {
        $articleId = (int)($_POST['article_id'] ?? 0);
        $result    = $adminCtrl->unsuspendArticle($articleId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'unsuspend_article', 'Article', $articleId,
                "Restored article back to published (ID: {$articleId})");
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article restored to published.');
        }
        redirect('/pages/admin-dashboard.php?tab=articles', $result['error']);
    }

    if ($action === 'delete_article') {
        $articleId = (int)($_POST['article_id'] ?? 0);
        $result    = $adminCtrl->deleteArticle($articleId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'delete_article', 'Article', $articleId,
                "Deleted article (ID: {$articleId})");
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article deleted.');
        }
        redirect('/pages/admin-dashboard.php?tab=articles', $result['error']);
    }

    // USER ACTIONS
    if ($action === 'update_user') {
        $userId       = (int)($_POST['user_id'] ?? 0);
        $isSuspending = isset($_POST['is_suspended']);
        $result       = $adminCtrl->updateUser($userId, $_POST);
        if (isset($result['ok'])) {
            if ($isSuspending) {
                $adminCtrl->logAction($user->id, 'suspend_user', 'User', $userId,
                    "Suspended user for policy violation (ID: {$userId})");
            } else {
                $adminCtrl->logAction($user->id, 'reinstate_user', 'User', $userId,
                    "Reinstated user after appeal review (ID: {$userId})");
            }
            redirect('/pages/admin-dashboard.php?tab=users', null, 'User updated successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=users', $result['error']);
    }

    // CATEGORY ACTIONS 
    if ($action === 'create_category') {
        $result = $adminCtrl->createCategory($_POST);
        if (isset($result['ok'])) {
            $catName = htmlspecialchars(trim($_POST['name'] ?? ''));
            $adminCtrl->logAction($user->id, 'create_category', 'Category', null,
                "Created category: {$catName}");
            redirect('/pages/admin-dashboard.php?tab=categories', null, 'Category created successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=categories', $result['error']);
    }

    if ($action === 'update_category') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $result     = $adminCtrl->updateCategory($categoryId, $_POST);
        if (isset($result['ok'])) {
            $catName = htmlspecialchars(trim($_POST['name'] ?? ''));
            $adminCtrl->logAction($user->id, 'update_category', 'Category', $categoryId,
                "Updated {$catName} category description");
            redirect('/pages/admin-dashboard.php?tab=categories', null, 'Category updated successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=categories', $result['error']);
    }

    if ($action === 'delete_category') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $result     = $adminCtrl->deleteCategory($categoryId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'delete_category', 'Category', $categoryId,
                "Deleted category (ID: {$categoryId})");
            redirect('/pages/admin-dashboard.php?tab=categories', null, 'Category deleted successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=categories', $result['error']);
    }

    // CATEGORY EXPERT ACTIONS
    if ($action === 'assign_expert') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $expertId   = (int)($_POST['user_id']     ?? 0);
        $result     = $adminCtrl->assignExpert($categoryId, $expertId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'assign_role', 'User', $expertId,
                "Assigned category_admin role (user ID: {$expertId}, category ID: {$categoryId})");
            redirect('/pages/admin-dashboard.php?tab=experts', null, 'Category expert assigned successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=experts', $result['error']);
    }

    if ($action === 'unassign_expert') {
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $expertId   = (int)($_POST['user_id'] ?? 0);
        $result     = $adminCtrl->unassignExpert($categoryId, $expertId);
        if (isset($result['ok'])) {
            $adminCtrl->logAction($user->id, 'unassign_role', 'User', $expertId ?: null,
                "Unassigned category_admin from category (category ID: {$categoryId}, user ID: {$expertId})");
            redirect('/pages/admin-dashboard.php?tab=experts', null, 'Category expert removed.');
        }
        redirect('/pages/admin-dashboard.php?tab=experts', $result['error']);
    }
}

// load data depending on tab 
$stats      = $adminCtrl->getExtendedStats();
$categories = $adminCtrl->getAllCategories();

if ($tab === 'users') {
    $allUsers = $adminCtrl->getAllUsers();
} elseif ($tab === 'categories') {
    $allCategories = $adminCtrl->getAllCategoriesWithCount();
} elseif ($tab === 'experts') {
    $expertCategories  = $adminCtrl->getCategoriesWithExperts();
    $eligibleExperts   = $adminCtrl->getEligibleExperts();
} else {
    $allArticles = $adminCtrl->getAllArticles();
}

page_head('Admin Dashboard');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Admin Dashboard', 'System Administration Panel'); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <!-- ANALYTICS QUICK-STATS BAR -->
    <?php
    $statItems = [
        ['icon' => '📄', 'value' => $stats['totalArticles'],     'label' => 'Total Articles'],
        ['icon' => '📁', 'value' => $stats['totalCategories'],   'label' => 'Total Categories'],
        ['icon' => '👥', 'value' => $stats['totalUsers'],        'label' => 'Total Users'],
        ['icon' => '📈', 'value' => $stats['premiumUsers'],      'label' => 'Total Premium Users'],
        ['icon' => '🚩', 'value' => $stats['flaggedArticles'],   'label' => 'Flagged Articles'],
        ['icon' => '🛡️', 'value' => $stats['suspendedArticles'], 'label' => 'Suspended Articles'],
    ];
    ?>
    <div style="display:flex;gap:0;margin-bottom:28px;background:var(--primary);border-radius:12px;overflow:hidden;flex-wrap:wrap">
        <?php foreach ($statItems as $i => $item): ?>
        <div style="flex:1;min-width:120px;padding:18px 20px;display:flex;align-items:center;gap:12px;
                    <?= $i < count($statItems) - 1 ? 'border-right:1px solid rgba(0,0,0,0.12)' : '' ?>">
            <span style="font-size:22px;line-height:1"><?= $item['icon'] ?></span>
            <div>
                <div style="font-size:22px;font-weight:800;color:#1a1a1a;line-height:1.1"><?= $item['value'] ?></div>
                <div style="font-size:11px;color:#3d2e00;margin-top:3px;white-space:nowrap"><?= $item['label'] ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- TAB BUTTONS -->
    <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
        <a href="/pages/admin-dashboard.php?tab=articles"
           class="btn <?= $tab === 'articles'   ? 'btn-primary' : 'btn-ghost' ?>">
            Manage Articles
        </a>
        <a href="/pages/admin-dashboard.php?tab=users"
           class="btn <?= $tab === 'users'      ? 'btn-primary' : 'btn-ghost' ?>">
            Manage Users
        </a>
        <a href="/pages/admin-dashboard.php?tab=categories"
           class="btn <?= $tab === 'categories' ? 'btn-primary' : 'btn-ghost' ?>">
            Manage Categories
        </a>
        <a href="/pages/admin-dashboard.php?tab=experts"
           class="btn <?= $tab === 'experts'    ? 'btn-primary' : 'btn-ghost' ?>">
            Manage Category Experts
        </a>
    </div>


    <!-- TAB: ARTICLES -->
    <?php if ($tab === 'articles'): ?>

        <div class="card" style="padding:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
                All Articles (<?= count($allArticles) ?>)
            </h2>

            <?php if (empty($allArticles)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No articles found.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">ID</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Title</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Category</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Author</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Published</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Status</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allArticles as $article): ?>
                        <?php $isSuspended = ($article->status ?? 'published') === 'suspended'; ?>
                        <tr style="border-bottom:1px solid var(--border);<?= $isSuspended ? 'opacity:0.55' : '' ?>">
                            <td style="padding:12px 8px;color:var(--muted)">#<?= $article->id ?></td>
                            <td style="padding:12px 8px;max-width:240px">
                                <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <?= htmlspecialchars($article->title) ?>
                                </div>
                            </td>
                            <td style="padding:12px 8px">
                                <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                            </td>
                            <td style="padding:12px 8px"><?= htmlspecialchars($article->authorName) ?></td>
                            <td style="padding:12px 8px;color:var(--muted);white-space:nowrap">
                                <?= relative_time($article->publishedAt) ?>
                            </td>
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
                            <td style="padding:12px 8px;text-align:right;white-space:nowrap">
                                <a href="/pages/article.php?id=<?= $article->id ?>" target="_blank"
                                   class="btn btn-ghost btn-sm">View</a>

                                <?php if ($isSuspended): ?>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"     value="unsuspend_article">
                                    <input type="hidden" name="article_id" value="<?= $article->id ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--success)"
                                            onclick="return confirm('Restore \'<?= htmlspecialchars(addslashes($article->title)) ?>\' back to published?')">
                                        ✅ Restore
                                    </button>
                                </form>
                                <?php else: ?>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"     value="suspend_article">
                                    <input type="hidden" name="article_id" value="<?= $article->id ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--warning)"
                                            onclick="return confirm('Suspend \'<?= htmlspecialchars(addslashes($article->title)) ?>\'? It will be hidden from all users.')">
                                        🚫 Suspend
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>


    <!-- TAB: USERS -->
    <?php if ($tab === 'users'): ?>

        <div class="card" style="padding:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
                All Users (<?= count($allUsers) ?>)
            </h2>

            <?php if (empty($allUsers)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No users found.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">ID</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Name</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Email</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Role</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Status</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allUsers as $u): ?>
                        <tr style="border-bottom:1px solid var(--border);
                            <?= $u->id === $user->id ? 'background:var(--primary-lt)' : '' ?>
                            <?= $u->isSuspended      ? ';opacity:0.55'               : '' ?>">
                            <td style="padding:12px 8px;color:var(--muted)">#<?= $u->id ?></td>
                            <td style="padding:12px 8px;font-weight:600">
                                <?= htmlspecialchars($u->fullName) ?>
                                <?php if ($u->id === $user->id): ?>
                                    <span style="font-size:11px;color:var(--muted);font-weight:400"> (you)</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 8px;color:var(--muted)"><?= htmlspecialchars($u->email) ?></td>
                            <td style="padding:12px 8px">
                                <?php
                                $roleStyle = match($u->role) {
                                    'system_admin'   => 'background:var(--primary);color:#fff',
                                    'premium'        => 'background:var(--warning);color:#fff',
                                    'category_admin' => 'background:#7c3aed;color:#fff',
                                    default          => ''
                                };
                                ?>
                                <span class="role-badge" style="<?= $roleStyle ?>">
                                    <?= htmlspecialchars($u->roleLabel()) ?>
                                </span>
                            </td>
                            <td style="padding:12px 8px">
                                <?php if ($u->isSuspended): ?>
                                    <span style="font-size:11px;font-weight:600;color:var(--danger);
                                                 background:#fff0f0;padding:2px 8px;border-radius:99px;
                                                 border:1px solid var(--danger)">🚫 Suspended</span>
                                <?php else: ?>
                                    <span style="font-size:11px;font-weight:600;color:var(--success);
                                                 background:#f0fff4;padding:2px 8px;border-radius:99px;
                                                 border:1px solid var(--success)">✅ Active</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 8px;text-align:right">
                                <?php if ($u->id !== $user->id): ?>
                                    <?php if ($u->isSuspended): ?>
                                    <form method="POST" style="display:inline;margin:0">
                                        <input type="hidden" name="action"  value="update_user">
                                        <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                        <button type="submit" class="btn btn-ghost btn-sm"
                                                style="color:var(--success)"
                                                onclick="return confirm('Restore access for \'<?= htmlspecialchars(addslashes($u->fullName)) ?>\'?')">
                                            ✅ Unsuspend
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" style="display:inline;margin:0">
                                        <input type="hidden" name="action"       value="update_user">
                                        <input type="hidden" name="user_id"      value="<?= $u->id ?>">
                                        <input type="hidden" name="is_suspended" value="1">
                                        <button type="submit" class="btn btn-ghost btn-sm"
                                                style="color:var(--danger)"
                                                onclick="return confirm('Suspend \'<?= htmlspecialchars(addslashes($u->fullName)) ?>\'? They will not be able to log in.')">
                                            🚫 Suspend
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="font-size:12px;color:var(--muted)">— (you)</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>


    <!-- TAB: CATEGORIES --> 
    <?php if ($tab === 'categories'): ?>

        <div style="margin-bottom:16px">
            <button type="button" class="btn btn-primary" onclick="openCreateModal()">➕ Add Category</button>
        </div>

        <div class="card" style="padding:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
                All Categories (<?= count($allCategories) ?>)
            </h2>

            <?php if (empty($allCategories)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No categories yet.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">ID</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Name</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Description</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Articles</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allCategories as $cat): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:12px 8px;color:var(--muted)">#<?= $cat['id'] ?></td>
                            <td style="padding:12px 8px;font-weight:600"><?= htmlspecialchars($cat['name']) ?></td>
                            <td style="padding:12px 8px;color:var(--muted);font-size:13px">
                                <?= htmlspecialchars(mb_substr($cat['description'] ?? '', 0, 60)) ?>
                                <?= strlen($cat['description'] ?? '') > 60 ? '...' : '' ?>
                            </td>
                            <td style="padding:12px 8px">
                                <span class="category-tag"><?= $cat['article_count'] ?> articles</span>
                            </td>
                            <td style="padding:12px 8px;text-align:right;white-space:nowrap">
                                <button type="button" class="btn btn-ghost btn-sm"
                                        onclick="openEditModal(
                                            <?= $cat['id'] ?>,
                                            '<?= htmlspecialchars(addslashes($cat['name'])) ?>',
                                            '<?= htmlspecialchars(addslashes($cat['description'] ?? '')) ?>'
                                        )">Edit</button>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"      value="delete_category">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--danger)"
                                            onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?\nThis will fail if articles still use this category.')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- create / edit modal -->
        <div id="categoryModal"
             style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;
                    background:rgba(0,0,0,0.5);z-index:1000;
                    align-items:center;justify-content:center">
            <div class="card" style="max-width:500px;width:90%;padding:28px">
                <h3 style="font-size:18px;font-weight:700;margin-bottom:16px" id="modalTitle">Add Category</h3>
                <form method="POST" id="categoryForm">
                    <input type="hidden" name="action"      id="formAction" value="create_category">
                    <input type="hidden" name="category_id" id="categoryId" value="">
                    <div style="margin-bottom:14px">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="name" id="catName" class="form-input" maxlength="100" required>
                    </div>
                    <div style="margin-bottom:20px">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="catDesc" class="form-input" maxlength="500" rows="3"></textarea>
                    </div>
                    <div style="display:flex;gap:8px;justify-content:flex-end">
                        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">Add</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function openCreateModal() {
            document.getElementById('modalTitle').textContent = 'Add Category';
            document.getElementById('formAction').value       = 'create_category';
            document.getElementById('submitBtn').textContent  = 'Add';
            document.getElementById('categoryId').value       = '';
            document.getElementById('catName').value          = '';
            document.getElementById('catDesc').value          = '';
            document.getElementById('categoryModal').style.display = 'flex';
        }
        function openEditModal(id, name, description) {
            document.getElementById('modalTitle').textContent = 'Edit Category';
            document.getElementById('formAction').value       = 'update_category';
            document.getElementById('submitBtn').textContent  = 'Save';
            document.getElementById('categoryId').value       = id;
            document.getElementById('catName').value          = name;
            document.getElementById('catDesc').value          = description;
            document.getElementById('categoryModal').style.display = 'flex';
        }
        function closeModal() {
            document.getElementById('categoryModal').style.display = 'none';
        }
        window.onclick = function(e) {
            if (e.target === document.getElementById('categoryModal')) closeModal();
        }
        </script>

    <?php endif;?>


    <!-- TAB: CATEGORY EXPERTS -->
    <?php if ($tab === 'experts'): ?>

        <div class="card" style="padding:24px">
            <div style="margin-bottom:20px">
                <h2 style="font-size:17px;font-weight:700;margin-bottom:6px">
                    Manage Category Experts
                </h2>
                <p style="font-size:13px;color:var(--muted)">
                    Assign one or more experts per category. Experts get the <strong style="font-weight:600">Category Admin</strong> role
                    and can manage articles in their assigned category.
                </p>
            </div>

            <?php if (empty($expertCategories)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No categories found.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Category</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Assigned Experts</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Summary</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($expertCategories as $cat): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:14px 8px">
                                <span class="category-tag"><?= htmlspecialchars($cat['name']) ?></span>
                            </td>
                            <td style="padding:14px 8px;font-weight:600">
                                <?php if (!empty($cat['experts'])): ?>
                                    <div style="display:flex;flex-direction:column;gap:8px">
                                        <?php foreach ($cat['experts'] as $assignedExpert): ?>
                                            <div>
                                                <div style="font-weight:600">
                                                    <?= htmlspecialchars($assignedExpert['full_name']) ?>
                                                    <span style="font-size:10px;font-weight:500;padding:1px 7px;border-radius:99px;
                                                                 background:#ede9fe;color:#7c3aed;margin-left:6px">
                                                        Category Admin
                                                    </span>
                                                </div>
                                                <div style="font-size:13px;color:var(--muted)">
                                                    <?= htmlspecialchars($assignedExpert['email']) ?>
                                                </div>
                                                <form method="POST" style="margin-top:6px">
                                                    <input type="hidden" name="action" value="unassign_expert">
                                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                                    <input type="hidden" name="user_id" value="<?= (int)$assignedExpert['user_id'] ?>">
                                                    <button type="submit" class="btn btn-ghost btn-sm"
                                                            style="color:var(--danger)"
                                                            onclick="return confirm('Remove \'<?= htmlspecialchars(addslashes($assignedExpert['full_name'])) ?>\' as expert for \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?')">
                                                        Remove This Expert
                                                    </button>
                                                </form>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span style="color:var(--muted);font-weight:400;font-style:italic">No experts assigned</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 8px;color:var(--muted)">
                                <?= $cat['admin_user_id'] ? htmlspecialchars($cat['expert_email']) : '—' ?>
                            </td>
                            <td style="padding:14px 8px;text-align:right;white-space:nowrap">

                                <!-- assign / reassign: dropdown to pick a user -->
                                <details style="display:inline-block;position:relative">
                                    <summary class="btn btn-ghost btn-sm"
                                             style="cursor:pointer;list-style:none">
                                        <?= $cat['admin_user_id'] ? '🔄 Reassign' : '➕ Assign' ?>
                                    </summary>
                                    <div style="position:absolute;right:0;top:calc(100% + 4px);
                                                background:var(--card);border:1px solid var(--border);
                                                border-radius:var(--radius);padding:16px;min-width:260px;
                                                z-index:50;box-shadow:var(--shadow-elevated)">
                                        <form method="POST">
                                            <input type="hidden" name="action"      value="assign_expert">
                                            <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">

                                            <div style="margin-bottom:12px">
                                                <label class="form-label" style="font-size:12px">
                                                    Select user to assign as expert for
                                                    <strong style="font-weight:600"><?= htmlspecialchars($cat['name']) ?></strong>
                                                </label>
                                                <select name="user_id" class="form-input" style="font-size:13px" required>
                                                    <option value="">— choose a user —</option>
                                                    <?php foreach ($eligibleExperts as $expert): ?>
                                                    <?php
                                                    $alreadyAssigned = false;
                                                    foreach ($cat['experts'] as $assignedExpert) {
                                                        if ((int)$assignedExpert['user_id'] === (int)$expert['id']) {
                                                            $alreadyAssigned = true;
                                                            break;
                                                        }
                                                    }
                                                    ?>
                                                    <option value="<?= $expert['id'] ?>"
                                                        <?= $alreadyAssigned ? 'disabled' : ($expert['id'] == $cat['admin_user_id'] ? 'selected' : '') ?>>
                                                        <?= htmlspecialchars($expert['full_name']) ?>
                                                        (<?= htmlspecialchars($expert['email']) ?>)<?= $alreadyAssigned ? ' - already assigned' : '' ?>
                                                    </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%">
                                                ✅ Confirm Assignment
                                            </button>
                                        </form>
                                    </div>
                                </details>

                                <!-- unassign button — only shown if an expert is assigned -->
                                <?php if ($cat['admin_user_id']): ?>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"      value="unassign_expert">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--danger)"
                                            onclick="return confirm('Remove \'<?= htmlspecialchars(addslashes($cat['expert_name'])) ?>\' as expert for \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?')">
                                        ✕ Remove
                                    </button>
                                </form>
                                <?php endif; ?>

                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

</div>
</main>
</div>
<?php page_foot(); ?>
