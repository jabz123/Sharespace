<?php
// category admin management page
// allows users with role = 'category_admin' to view and manage articles in their assigned category
// only accessible to users with role = 'category_admin' who have a managed category assigned

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';
require_once __DIR__ . '/../includes/controllers/CategoryController.php';

$auth      = new AuthController();
$adminCtrl = new AdminController();
$catCtrl   = new CategoryController();

$auth->requireAuth();
$user = $auth->currentUser();

// only category admins may access this page
if ($user->role !== 'category_admin') {
    header('Location: /dashboard.php');
    exit;
}

// category admins must have an assigned category
if (!$user->managedCategoryId) {
    page_head('Manage My Category');
    ?>
    <div class="dashboard-layout">
    <?php sidebar($user); ?>
    <main>
    <?php dash_header('Manage My Category', 'Category Administration'); ?>
    <div class="page-content">
        <div class="card" style="padding:48px 32px;text-align:center">
            <div style="font-size:48px;margin-bottom:16px">📋</div>
            <h3 style="font-size:18px;font-weight:700;margin-bottom:8px">No Category Assigned</h3>
            <p class="text-muted">You have not been assigned a category to manage yet. Please contact the system administrator.</p>
        </div>
    </div>
    </main>
    </div>
    <?php page_foot();
    exit;
}

// handle suspend / unsuspend POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action']     ?? '';
    $articleId = (int)($_POST['article_id'] ?? 0);

    if ($action === 'suspend_article' || $action === 'unsuspend_article') {
        // verify the article belongs to this category admin's category
        $article = $adminCtrl->getArticleById($articleId);
        if ($article && $article->categoryId === $user->managedCategoryId) {
            if ($action === 'suspend_article') {
                $adminCtrl->suspendArticle($articleId);
                redirect('/pages/manage-category.php', null, 'Article suspended.');
            } else {
                $adminCtrl->unsuspendArticle($articleId);
                redirect('/pages/manage-category.php', null, 'Article restored.');
            }
        }
        redirect('/pages/manage-category.php', 'Article not found or not in your category.');
    }
}

// load category info and articles
$category = $catCtrl->getById($user->managedCategoryId);
$articles = $adminCtrl->getArticlesByCategory($user->managedCategoryId);

page_head('Manage My Category');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header(
    'Manage My Category',
    $category ? 'Managing: ' . htmlspecialchars($category['name']) : 'Category Administration'
); ?>
<?php flash_messages(); ?>

<div class="page-content">

    <?php if ($category): ?>
    <!-- category info card -->
    <div class="card" style="padding:20px 24px;margin-bottom:24px">
        <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap">
            <div>
                <div style="font-size:13px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px">Your Assigned Category</div>
                <div style="font-size:22px;font-weight:800"><?= htmlspecialchars($category['name']) ?></div>
                <?php if (!empty($category['description'])): ?>
                <div style="font-size:14px;color:var(--muted);margin-top:4px"><?= htmlspecialchars($category['description']) ?></div>
                <?php endif; ?>
            </div>
            <div style="margin-left:auto">
                <span class="category-tag" style="font-size:14px;padding:6px 14px"><?= count($articles) ?> article<?= count($articles) !== 1 ? 's' : '' ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- articles table -->
    <div class="card" style="padding:24px">
        <h2 style="font-size:17px;font-weight:700;margin-bottom:16px">
            All Articles in This Category (<?= count($articles) ?>)
        </h2>

        <?php if (empty($articles)): ?>
            <p class="text-muted" style="text-align:center;padding:32px">No articles in this category yet.</p>
        <?php else: ?>
        <div style="overflow-x:auto">
            <table style="width:100%;border-collapse:collapse;font-size:14px">
                <thead>
                    <tr style="border-bottom:2px solid var(--border)">
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">ID</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Title</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Author</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Published</th>
                        <th style="text-align:left;padding:10px 8px;color:var(--muted);font-weight:600">Status</th>
                        <th style="text-align:right;padding:10px 8px;color:var(--muted);font-weight:600">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($articles as $article): ?>
                    <?php $isSuspended = ($article->status ?? 'published') === 'suspended'; ?>
                    <tr style="border-bottom:1px solid var(--border);<?= $isSuspended ? 'opacity:0.55' : '' ?>">
                        <td style="padding:12px 8px;color:var(--muted)">#<?= $article->id ?></td>
                        <td style="padding:12px 8px;max-width:260px">
                            <div style="font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                <?= htmlspecialchars($article->title) ?>
                            </div>
                        </td>
                        <td style="padding:12px 8px"><?= htmlspecialchars($article->authorName) ?></td>
                        <td style="padding:12px 8px;color:var(--muted);white-space:nowrap">
                            <?= $article->publishedAt ? relative_time($article->publishedAt) : '—' ?>
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
                               class="btn btn-ghost btn-sm">👁 View</a>

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

</div><!-- /.page-content -->
</main>
</div><!-- /.dashboard-layout -->
<?php page_foot(); ?>
