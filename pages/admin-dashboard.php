<?php
// combined system admin dashboard
// tabs: articles (CRUD), users (manage), categories (CRUD)
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
    if ($action === 'delete_article') {
        $result = $adminCtrl->deleteArticle((int)($_POST['article_id'] ?? 0));
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article deleted successfully.');
        }
        redirect('/pages/admin-dashboard.php?tab=articles', $result['error']);
    }

    if ($action === 'update_article') {
        $articleId = (int)($_POST['article_id'] ?? 0);
        $input     = $_POST;

        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../public/uploads/articles/';
            $filename  = time() . '_' . basename($_FILES['image']['name']);
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $input['image_path'] = 'uploads/articles/' . $filename;
            }
        } else {
            $existing            = $adminCtrl->getArticleById($articleId);
            $input['image_path'] = $existing?->imagePath ?? null;
        }

        $result = $adminCtrl->updateArticle($articleId, $input);
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=articles', null, 'Article updated successfully.');
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

    if ($action === 'delete_user') {
        $result = $adminCtrl->deleteUser((int)($_POST['user_id'] ?? 0), $user->id);
        if (isset($result['ok'])) {
            redirect('/pages/admin-dashboard.php?tab=users', null, 'User deleted successfully.');
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
}

// ── load data depending on tab ───────────────────────────────────
$stats      = $adminCtrl->getStats();
$categories = $adminCtrl->getAllCategories(); // used in article edit dropdown

if ($tab === 'users') {
    $allUsers = $adminCtrl->getAllUsers();
} elseif ($tab === 'categories') {
    $allCategories = $adminCtrl->getAllCategoriesWithCount();
} else {
    $allArticles = $adminCtrl->getAllArticles();
}

// load article into edit form if ?edit=ID is present
$editArticle = null;
if ($tab === 'articles' && isset($_GET['edit'])) {
    $editArticle = $adminCtrl->getArticleById((int)$_GET['edit']);
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
    </div>


    <!-- ════════════════════════════════════════════════════════════
         TAB: ARTICLES
    ════════════════════════════════════════════════════════════ -->
    <?php if ($tab === 'articles'): ?>

        <!-- inline edit form — appears when ?edit=ID is in the URL -->
        <?php if ($editArticle): ?>
        <div class="card" id="edit-form" style="padding:28px;margin-bottom:24px">
            <h2 style="font-size:18px;font-weight:700;margin-bottom:20px">
                ✏️ Editing: <?= htmlspecialchars($editArticle->title) ?>
            </h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action"     value="update_article">
                <input type="hidden" name="article_id" value="<?= $editArticle->id ?>">
                <div style="display:grid;gap:16px">

                    <div>
                        <label class="form-label">Title</label>
                        <input type="text" name="title" class="form-input"
                               value="<?= htmlspecialchars($editArticle->title) ?>" required>
                    </div>

                    <div>
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-input" required>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>"
                                <?= $cat->id === $editArticle->categoryId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->name) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Excerpt</label>
                        <textarea name="excerpt" class="form-input" rows="3" required><?= htmlspecialchars($editArticle->excerpt) ?></textarea>
                    </div>

                    <div>
                        <label class="form-label">Content</label>
                        <textarea name="content" class="form-input" rows="12" required><?= htmlspecialchars($editArticle->content) ?></textarea>
                    </div>

                    <div>
                        <label class="form-label">Image (leave blank to keep existing)</label>
                        <?php if ($editArticle->imagePath): ?>
                            <div style="margin-bottom:8px">
                                <img src="/public/<?= htmlspecialchars($editArticle->imagePath) ?>"
                                     style="height:80px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
                            </div>
                        <?php endif; ?>
                        <input type="file" name="image" class="form-input" accept="image/*">
                    </div>

                    <div style="display:flex;gap:10px">
                        <button type="submit" class="btn btn-primary">💾 Save Changes</button>
                        <a href="/pages/admin-dashboard.php?tab=articles" class="btn btn-ghost">Cancel</a>
                    </div>

                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- articles table -->
        <div class="card" style="padding:24px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
                <h2 style="font-size:17px;font-weight:700">All Articles (<?= count($allArticles) ?>)</h2>
                <a href="/pages/write.php" class="btn btn-primary btn-sm">✏️ Write New Article</a>
            </div>

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
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Views</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allArticles as $article): ?>
                        <tr style="border-bottom:1px solid var(--border)">
                            <td style="padding:12px 8px;color:var(--muted)">#<?= $article->id ?></td>
                            <td style="padding:12px 8px;max-width:240px">
                                <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                    <a href="/pages/article.php?id=<?= $article->id ?>" target="_blank"
                                       style="color:var(--fg)"><?= htmlspecialchars($article->title) ?></a>
                                </div>
                            </td>
                            <td style="padding:12px 8px">
                                <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                            </td>
                            <td style="padding:12px 8px"><?= htmlspecialchars($article->authorName) ?></td>
                            <td style="padding:12px 8px;color:var(--muted);white-space:nowrap">
                                <?= relative_time($article->publishedAt) ?>
                            </td>
                            <td style="padding:12px 8px;color:var(--muted)">👁 <?= $article->viewCount ?></td>
                            <td style="padding:12px 8px;text-align:right;white-space:nowrap">
                                <a href="/pages/admin-dashboard.php?tab=articles&edit=<?= $article->id ?>#edit-form"
                                   class="btn btn-ghost btn-sm">✏️ Edit</a>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"     value="delete_article">
                                    <input type="hidden" name="article_id" value="<?= $article->id ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--danger)"
                                            onclick="return confirm('Delete \'<?= htmlspecialchars(addslashes($article->title)) ?>\'? This cannot be undone.')">
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

    <?php endif; // end articles tab ?>


    <!-- ════════════════════════════════════════════════════════════
         TAB: USERS
    ════════════════════════════════════════════════════════════ -->
    <?php if ($tab === 'users'): ?>

        <div class="card" style="padding:24px">
            <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">All Users (<?= count($allUsers) ?>)</h2>

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
                            <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Suspended</th>
                            <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($allUsers as $u): ?>
                        <tr style="border-bottom:1px solid var(--border);
                            <?= $u->id === $user->id ? 'background:var(--primary-lt)' : '' ?>">
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
                                    <span style="color:var(--danger);font-weight:600;font-size:12px">🚫 Yes</span>
                                <?php else: ?>
                                    <span style="color:var(--success);font-size:12px">✅ No</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:12px 8px;text-align:right">
                                <details style="display:inline-block;position:relative">
                                    <summary class="btn btn-ghost btn-sm"
                                             style="cursor:pointer;list-style:none">✏️ Edit</summary>
                                    <div style="position:absolute;right:0;top:calc(100% + 4px);
                                                background:var(--card);border:1px solid var(--border);
                                                border-radius:var(--radius);padding:16px;min-width:220px;
                                                z-index:50;box-shadow:var(--shadow-elevated)">
                                        <form method="POST">
                                            <input type="hidden" name="action"  value="update_user">
                                            <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                            <div style="margin-bottom:12px">
                                                <label class="form-label" style="font-size:12px">Role</label>
                                                <select name="role" class="form-input" style="font-size:13px">
                                                    <option value="free"
                                                        <?= $u->role === 'free'         ? 'selected' : '' ?>>Free</option>
                                                    <option value="premium"
                                                        <?= $u->role === 'premium'      ? 'selected' : '' ?>>Premium</option>
                                                    <option value="system_admin"
                                                        <?= $u->role === 'system_admin' ? 'selected' : '' ?>>System Admin</option>
                                                </select>
                                            </div>
                                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px">
                                                <input type="checkbox" name="is_suspended"
                                                       id="susp_<?= $u->id ?>"
                                                       <?= $u->isSuspended ? 'checked' : '' ?>
                                                       <?= $u->id === $user->id ? 'disabled title="Cannot suspend yourself"' : '' ?>>
                                                <label for="susp_<?= $u->id ?>" style="font-size:13px">Suspended</label>
                                            </div>
                                            <button type="submit" class="btn btn-primary btn-sm" style="width:100%">
                                                💾 Save
                                            </button>
                                        </form>
                                    </div>
                                </details>

                                <?php if ($u->id !== $user->id): ?>
                                <form method="POST" style="display:inline;margin:0">
                                    <input type="hidden" name="action"  value="delete_user">
                                    <input type="hidden" name="user_id" value="<?= $u->id ?>">
                                    <button type="submit" class="btn btn-ghost btn-sm"
                                            style="color:var(--danger)"
                                            onclick="return confirm('Delete user \'<?= htmlspecialchars(addslashes($u->fullName)) ?>\'?\nThis also deletes all their articles and cannot be undone.')">
                                        🗑 Delete
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

    <?php endif; // end users tab ?>


    <!-- ════════════════════════════════════════════════════════════
         TAB: CATEGORIES
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

</div><!-- /.page-content -->
</main>
</div><!-- /.dashboard-layout -->
<?php page_foot(); ?>