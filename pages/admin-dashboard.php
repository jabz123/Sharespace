<?php
// combined system admin dashboard
// tabs: articles (view/suspend/unsuspend), users (suspend/unsuspend), categories (CRUD)
// only accessible to users with role = 'system_admin'

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

$auth      = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();
$adminCtrl->requireAdmin($user); // redirects non-admins to /dashboard.php

// ── active tab ───────────────────────────────────────────────────
// ?tab=articles (default) | ?tab=users | ?tab=categories
$tab = $_GET['tab'] ?? 'articles';

// ── handle all POST actions ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ── ARTICLE ACTIONS ──────────────────────────────────────────
    if ($action === 'suspend_article') {
        $result = $adminCtrl->suspendArticle((int)($_POST['article_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article suspended.');
        }
        redirect('/pages/admin-dashboard.php?tab=articles', $result['error']);
    }

    if ($action === 'unsuspend_article') {
        $result = $adminCtrl->unsuspendArticle((int)($_POST['article_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article restored to published.');
        }
        redirect('/pages/admin-dashboard.php?tab=articles', $result['error']);
    }

    // ── USER ACTIONS ─────────────────────────────────────────────
    if ($action === 'update_user') {
        $result = $adminCtrl->updateUser((int)($_POST['user_id'] ?? 0), $_POST);
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=users', null, 'User updated successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=users', $result['error']);
    }

    // ── CATEGORY ACTIONS ─────────────────────────────────────────
    if ($action === 'create_category') {
        $result = $adminCtrl->createCategory($_POST);
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=categories', null, 'Category created successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=categories', $result['error']);
    }

    if ($action === 'update_category') {
        $result = $adminCtrl->updateCategory((int)($_POST['category_id'] ?? 0), $_POST);
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=categories', null, 'Category updated successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=categories', $result['error']);
    }

    if ($action === 'delete_category') {
        $result = $adminCtrl->deleteCategory((int)($_POST['category_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=categories', null, 'Category deleted successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=categories', $result['error']);
    }

    // ── CATEGORY EXPERT ACTIONS ──────────────────────────────────
    if ($action === 'promote_to_expert') {
        $result = $adminCtrl->promoteUserToCategoryAdmin((int)($_POST['user_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=experts', null, 'User promoted to category expert.');
        }
        redirect('/pages/admin-dashboard.php?tab=experts', $result['error']);
    }

    if ($action === 'assign_category_to_expert') {
        $result = $adminCtrl->assignCategoryToExpert(
            (int)($_POST['user_id']     ?? 0),
            (int)($_POST['category_id'] ?? 0)
        );
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=experts', null, 'Category assigned to expert.');
        }
        redirect('/pages/admin-dashboard.php?tab=experts', $result['error']);
    }

    if ($action === 'unassign_category_from_expert') {
        $result = $adminCtrl->unassignCategoryFromExpert((int)($_POST['user_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=experts', null, 'Category unassigned from expert.');
        }
        redirect('/pages/admin-dashboard.php?tab=experts', $result['error']);
    }

    if ($action === 'demote_category_admin') {
        $result = $adminCtrl->demoteCategoryAdmin((int)($_POST['user_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=experts', null, 'Expert demoted back to free user.');
        }
        redirect('/pages/admin-dashboard.php?tab=experts', $result['error']);
    }
}

// ── load data depending on tab ───────────────────────────────────
$stats      = $adminCtrl->getStats();
$categories = $adminCtrl->getAllCategories();

if ($tab === 'users') {
    $allUsers = $adminCtrl->getAllUsers();
} elseif ($tab === 'categories') {
    $allCategories = $adminCtrl->getAllCategoriesWithCount();
} elseif ($tab === 'experts') {
    $categoryExperts      = $adminCtrl->getCategoryExperts();
    $categoriesWithExperts = $adminCtrl->getCategoriesWithExperts();
    $promotableUsers      = $adminCtrl->getPromotableUsers();
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

    <!-- ── STATS CARDS ─────────────────────────────────────────── -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:16px;margin-bottom:28px">
        <?php
        $cards = [
            ['📰', 'Total Articles', $stats['totalArticles']],
            ['👥', 'Total Users',    $stats['totalUsers']],
            ['⭐', 'Premium Users',  $stats['premiumUsers']],
            ['🚫', 'Suspended',      $stats['suspended']],
        ];
        foreach ($cards as [$icon, $label, $val]): ?>
        <div class="card" style="padding:20px 24px;text-align:center">
            <div style="font-size:28px;margin-bottom:6px"><?= $icon ?></div>
            <div style="font-size:24px;font-weight:800;color:var(--primary)"><?= $val ?></div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── TAB BUTTONS ─────────────────────────────────────────── -->
    <div style="display:flex;gap:8px;margin-bottom:24px;flex-wrap:wrap">
        <a href="/pages/admin-dashboard.php?tab=articles"
           class="btn <?= $tab === 'articles'   ? 'btn-primary' : 'btn-ghost' ?>">
            📰 Manage Articles
        </a>
        <a href="/pages/admin-dashboard.php?tab=users"
           class="btn <?= $tab === 'users'      ? 'btn-primary' : 'btn-ghost' ?>">
            👥 Manage Users
        </a>
        <a href="/pages/admin-dashboard.php?tab=categories"
           class="btn <?= $tab === 'categories' ? 'btn-primary' : 'btn-ghost' ?>">
            📋 Manage Categories
        </a>
        <a href="/pages/admin-dashboard.php?tab=experts"
           class="btn <?= $tab === 'experts' ? 'btn-primary' : 'btn-ghost' ?>">
            👨‍💼 Manage Category Experts
        </a>
    </div>


    <!-- ════════════════════════════════════════════════════════════
         TAB: ARTICLES
         admin can: view, suspend, unsuspend
         admin cannot: edit
    ════════════════════════════════════════════════════════════ -->
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

                                <!-- view: opens article in new tab (read only) -->
                                <a href="/pages/article.php?id=<?= $article->id ?>" target="_blank"
                                   class="btn btn-ghost btn-sm">👁 View</a>

                                <!-- suspend / unsuspend toggle -->
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

    <?php endif; // end articles tab ?>


    <!-- ════════════════════════════════════════════════════════════
         TAB: USERS
         admin can: suspend, unsuspend
         admin cannot: delete, change role
    ════════════════════════════════════════════════════════════ -->
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
                            <?= $u->isSuspended      ? 'opacity:0.55'                : '' ?>">
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
                                    'system_admin' => 'background:var(--primary);color:#fff',
                                    'premium'      => 'background:var(--warning);color:#fff',
                                    default        => ''
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
                                    <!-- unsuspend button -->
                                    <form method="POST" style="display:inline;margin:0">
                                        <input type="hidden" name="action"  value="update_user">
                                        <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                        <!-- is_suspended omitted = 0 = unsuspend -->
                                        <button type="submit" class="btn btn-ghost btn-sm"
                                                style="color:var(--success)"
                                                onclick="return confirm('Restore access for \'<?= htmlspecialchars(addslashes($u->fullName)) ?>\'?')">
                                            ✅ Unsuspend
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <!-- suspend button -->
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

    <?php endif; // end users tab ?>


    <!-- ════════════════════════════════════════════════════════════
         TAB: CATEGORIES
         admin can: create, edit, delete
    ════════════════════════════════════════════════════════════ -->
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
                                        )">✏️ Edit</button>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"      value="delete_category">
                                    <input type="hidden" name="category_id" value="<?= $cat['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--danger)"
                                            onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($cat['name'])) ?>\'?\nThis will fail if articles still use this category.')">
                                        🗑 Delete
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
                        <input type="text" name="name" id="catName" class="form-input"
                               maxlength="100" required>
                    </div>

                    <div style="margin-bottom:20px">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="catDesc" class="form-input"
                                  maxlength="500" rows="3"></textarea>
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

    <?php endif; // end categories tab ?>


    <!-- ════════════════════════════════════════════════════════════
         TAB: MANAGE CATEGORY EXPERTS
         admin can: promote free/premium → category_admin,
                    assign / unassign categories, demote category_admin → free
    ════════════════════════════════════════════════════════════ -->
    <?php if ($tab === 'experts'): ?>

        <?php if (empty($categoryExperts)): ?>
        <!-- ── Getting-started callout (shown only when no category admins exist) ── -->
        <div style="background:var(--primary-light,#eef2ff);border:1px solid var(--primary);border-radius:10px;padding:20px 24px;margin-bottom:24px;display:flex;gap:16px;align-items:flex-start">
            <div style="font-size:28px;line-height:1">ℹ️</div>
            <div>
                <div style="font-weight:700;font-size:15px;margin-bottom:6px">No category admins yet — here's how to get started</div>
                <ol style="margin:0;padding-left:20px;font-size:14px;color:var(--muted);line-height:1.8">
                    <li><strong>Scroll down to "Promote Users to Category Expert"</strong> and promote any free or premium user to give them the category admin role.</li>
                    <li>Once promoted, they will appear in <strong>"Current Category Experts"</strong> where you can assign a category for them to manage.</li>
                </ol>
            </div>
        </div>
        <?php endif; ?>

        <!-- ── SECTION 1: Categories with their experts ─────────── -->
        <div class="card" style="padding:24px;margin-bottom:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
                Categories &amp; Their Experts
            </h2>
            <?php if (empty($categoriesWithExperts)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No categories have been created yet. Add categories first, then assign experts to them.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Category</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Assigned Experts</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categoriesWithExperts as $cat): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:12px 8px;font-weight:600"><?= htmlspecialchars($cat['name']) ?></td>
                            <td style="padding:12px 8px;color:var(--muted)">
                                <?php if ($cat['expert_count'] > 0): ?>
                                    <?= htmlspecialchars($cat['expert_names']) ?>
                                    <span style="font-size:11px;margin-left:6px;color:var(--primary)">
                                        (<?= $cat['expert_count'] ?> expert<?= $cat['expert_count'] > 1 ? 's' : '' ?>)
                                    </span>
                                <?php else: ?>
                                    <span style="font-size:12px;color:var(--muted);font-style:italic">No experts assigned</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- ── SECTION 2: Current category experts ──────────────── -->
        <div class="card" style="padding:24px;margin-bottom:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
                Current Category Experts (<?= count($categoryExperts) ?>)
            </h2>

            <?php if (empty($categoryExperts)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No category admins yet. Use the <strong>"Promote Users to Category Expert"</strong> section below to promote a user first.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Name</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Email</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Assigned Category</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categoryExperts as $expert): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:12px 8px;font-weight:600"><?= htmlspecialchars($expert['full_name']) ?></td>
                            <td style="padding:12px 8px;color:var(--muted)"><?= htmlspecialchars($expert['email']) ?></td>
                            <td style="padding:12px 8px">
                                <?php if ($expert['managed_category_id']): ?>
                                    <span class="category-tag"><?= htmlspecialchars($expert['category_name']) ?></span>
                                <?php else: ?>
                                    <!-- assign category form -->
                                    <form method="POST" style="display:flex;gap:6px;align-items:center;margin:0">
                                        <input type="hidden" name="action"  value="assign_category_to_expert">
                                        <input type="hidden" name="user_id" value="<?= $expert['id'] ?>">
                                        <select name="category_id" class="form-input" style="padding:4px 8px;font-size:13px;min-width:140px" required>
                                            <option value="">— select category —</option>
                                            <?php foreach ($categories as $cat): ?>
                                                <option value="<?= $cat->id ?>"><?= htmlspecialchars($cat->name) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-ghost btn-sm"
                                                style="color:var(--primary)">Assign</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 8px;text-align:right;white-space:nowrap">
                                <?php if ($expert['managed_category_id']): ?>
                                <!-- unassign button -->
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"  value="unassign_category_from_expert">
                                    <input type="hidden" name="user_id" value="<?= $expert['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--warning)"
                                            onclick="return confirm('Unassign category from \'<?= htmlspecialchars(addslashes($expert['full_name'])) ?>\'?')">
                                        🔓 Unassign
                                    </button>
                                </form>
                                <?php endif; ?>
                                <!-- demote button -->
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"  value="demote_category_admin">
                                    <input type="hidden" name="user_id" value="<?= $expert['id'] ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--danger)"
                                            onclick="return confirm('Demote \'<?= htmlspecialchars(addslashes($expert['full_name'])) ?>\' back to free user?')">
                                        ⬇️ Demote
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

        <!-- ── SECTION 3: Promote free/premium users to expert ──── -->
        <div class="card" style="padding:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
                Promote Users to Category Expert (<?= count($promotableUsers) ?>)
            </h2>

            <?php if (empty($promotableUsers)): ?>
                <p class="text-muted" style="text-align:center;padding:32px">No free or premium users available to promote.</p>
            <?php else: ?>
            <div style="overflow-x:auto">
                <table style="width:100%;border-collapse:collapse;font-size:14px">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border)">
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Name</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Email</th>
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Role</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($promotableUsers as $pu): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:12px 8px;font-weight:600"><?= htmlspecialchars($pu->fullName) ?></td>
                            <td style="padding:12px 8px;color:var(--muted)"><?= htmlspecialchars($pu->email) ?></td>
                            <td style="padding:12px 8px">
                                <?php
                                $roleStyle = $pu->role === 'premium'
                                    ? 'background:var(--warning);color:#fff'
                                    : '';
                                ?>
                                <span class="role-badge" style="<?= $roleStyle ?>">
                                    <?= htmlspecialchars($pu->roleLabel()) ?>
                                </span>
                            </td>
                            <td style="padding:12px 8px;text-align:right">
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"  value="promote_to_expert">
                                    <input type="hidden" name="user_id" value="<?= $pu->id ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--primary)"
                                            onclick="return confirm('Promote \'<?= htmlspecialchars(addslashes($pu->fullName)) ?>\' to category expert?')">
                                        ⬆️ Promote
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

    <?php endif; // end experts tab ?>

</div><!-- /.page-content -->
</main>
</div><!-- /.dashboard-layout -->
<?php page_foot(); ?>