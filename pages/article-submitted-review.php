<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();
$articleId = (int)($_GET['id'] ?? 0);
$homeUrl = $user->role === 'category_admin' ? '/pages/category-admin-dashboard.php' : '/dashboard.php';

page_head('Article Submitted for Review');
?>
<div class="dashboard-layout user-dashboard-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Article Submitted for Review', 'Your article is now waiting for category expert approval'); ?>

<div class="page-content">
    <div class="card" style="max-width:720px;margin:0 auto;padding:32px;text-align:center">
        <h2 style="font-size:28px;font-weight:800;font-family:Georgia,serif;margin-bottom:12px">
            Article submitted for review
        </h2>
        <p class="text-muted" style="font-size:15px;line-height:1.7;margin-bottom:24px">
            Your article passed the AI check and has been sent to a category expert for final review.
            It will appear under Pending in My Articles until an expert verifies or rejects it.
        </p>
        <div class="flex items-center justify-center gap-3" style="flex-wrap:wrap">
            <a href="<?= htmlspecialchars($homeUrl) ?>" class="btn btn-ghost">Back to Home</a>
            <a href="/pages/my-articles.php?filter=pending" class="btn btn-primary">View Pending Articles</a>
            <?php if ($articleId > 0): ?>
                <a href="/pages/write.php?id=<?= $articleId ?>" class="btn btn-ghost">Edit Article</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
</div>
<?php page_foot(); ?>
