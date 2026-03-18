<?php
// system admin dashboard
// provides full CRUD for all articles and user management
// only accessible to users with role = 'system_admin'

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

$auth      = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();
$adminCtrl->requireAdmin($user); // blocks anyone who is not system_admin

// ── which tab are we on? ─────────────────────────────────────────
// default is articles. switch to users with ?tab=users
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

        // handle image upload if a new image was chosen
        if (!empty($_FILES['image']['name'])) {
            $uploadDir = __DIR__ . '/../public/uploads/articles/';
            $filename  = time() . '_' . basename($_FILES['image']['name']);
            $dest      = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $input['image_path'] = 'uploads/articles/' . $filename;
            }
        } else {
            // keep the existing image if no new one was uploaded
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
}

// ── load data for the current tab ───────────────────────────────
$stats      = $adminCtrl->getStats();
$categories = $adminCtrl->getAllCategories();

if ($tab === 'users') {
    $allUsers = $adminCtrl->getAllUsers();
} else {
    $allArticles = $adminCtrl->getAllArticles();
}

// ── if ?edit=ID is in the URL, load that article for editing ────
$editArticle = null;
if ($tab === 'articles' && isset($_GET['edit'])) {
    $editArticle = $adminCtrl->getArticleById((int)$_GET['edit']);
}

page_head('Admin Dashboard');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Admin Dashboard', 'Manage all articles and users'); ?>
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
    <div style="display:flex;gap:8px;margin-bottom:24px">
        <a href="/pages/admin-dashboard.php?tab=articles"
           class="btn <?= $tab === 'articles' ? 'btn-primary' : 'btn-ghost' ?>">
            📰 Manage Articles
        </a>
        <a href="/pages/admin-dashboard.php?tab=users"
           class="btn <?= $tab === 'users' ? 'btn-primary' : 'btn-ghost' ?>">
            👥 Manage Users
        </a>
    </div>


    <!-- ════════════════════════════════════════════════════════════
         ARTICLES TAB
    ════════════════════════════════════════════════════════════ -->
    <?php if ($tab === 'articles'): ?>

        <!-- edit form — only shows when ?edit=ID is in the URL -->
        <?php if ($editArticle): ?>
        <div class="card" id="edit-form" style="padding:28px;margin-bottom:24px">
            <h2 style="font-size:18px;font-weight:700;margin-bottom:20px">✏️ Editing: <?= htmlspecialchars($editArticle->title) ?></h2>
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
                                       style="color:var(--fg)">
                                        <?= htmlspecialchars($article->title) ?>
                                    </a>
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
         USERS TAB
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

                                <!-- edit dropdown using HTML details — no JS needed -->
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

                                <!-- no delete button on your own row -->
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

</div><!-- /.page-content -->
</main>
</div><!-- /.dashboard-layout -->
<?php page_foot(); ?>