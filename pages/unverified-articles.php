<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

function isAjaxRequest(): bool {
    return strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';
}

function pageRedirect(string $url, ?string $error = null, ?string $success = null): never {
    if ($error) {
        flash_set('flash_error', $error);
    }
    if ($success) {
        flash_set('flash_success', $success);
    }

    $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta http-equiv="refresh" content="0;url=' . $escapedUrl . '">';
    echo '<title>Redirecting...</title></head><body>';
    echo '<script>window.top.location.href = ' . json_encode($url) . ';</script>';
    echo '<p>Redirecting... If nothing happens, <a href="' . $escapedUrl . '">continue here</a>.</p>';
    echo '</body></html>';
    exit;
}

$auth = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role !== 'category_admin') {
    pageRedirect('/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $articleId = (int)($_POST['article_id'] ?? 0);

    if (in_array($action, ['verify_article', 'unverify_article'], true) && $articleId > 0) {
        $decision = $action === 'verify_article' ? 'verified' : 'unverified';
        $result = $adminCtrl->reviewPendingArticle($articleId, (int)$user->id, $decision);

        if (isset($result['ok'])) {
            $message = $decision === 'verified'
                ? 'Article verified. It will publish automatically once every assigned category expert verifies it.'
                : 'Article rejected. It has been moved back to draft for the author.';
            if (isAjaxRequest()) {
                header('Content-Type: application/json');
                echo json_encode([
                    'ok' => true,
                    'redirect' => '/pages/unverified-articles.php',
                    'message' => $message,
                ]);
                exit;
            }
            pageRedirect('/pages/unverified-articles.php', null, $message);
        }

        if (isAjaxRequest()) {
            header('Content-Type: application/json', true, 400);
            echo json_encode([
                'ok' => false,
                'error' => $result['error'] ?? 'Unable to review article.',
            ]);
            exit;
        }
        pageRedirect('/pages/unverified-articles.php', $result['error'] ?? 'Unable to review article.');
    }
}

$detailId = (int)($_GET['id'] ?? 0);
$assignedCategories = $adminCtrl->getAssignedCategoriesForExpert((int)$user->id);
$unverifiedArticles = $adminCtrl->getUnverifiedArticlesForExpert((int)$user->id);
$detailRow = $detailId ? $adminCtrl->getUnverifiedArticleForExpert((int)$user->id, $detailId) : null;
$detailArticle = $detailRow ? new Article($detailRow) : null;
$reviewProgress = $detailArticle ? $adminCtrl->getExpertReviewProgress($detailArticle->id) : [];

page_head('Unverified Articles');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php flash_messages(); ?>

<?php if ($detailArticle): ?>
    <?php dash_header(htmlspecialchars($detailArticle->title), 'Final category expert verification'); ?>

    <div class="page-content">
        <div class="flex gap-2 mb-6">
            <a href="/pages/unverified-articles.php" class="btn btn-ghost btn-sm">Back to Unverified Articles</a>
        </div>

        <div class="card mb-6" style="padding:20px 24px">
            <div class="flex items-center justify-between mb-2" style="flex-wrap:wrap;gap:12px">
                <span class="category-tag <?= category_theme_class($detailArticle->categoryName) ?>"><?= htmlspecialchars($detailArticle->categoryName) ?></span>
                <?= trust_badge($detailArticle->trustScore) ?>
            </div>
            <h2 style="font-size:22px;font-weight:700;font-family:Georgia,serif;margin-bottom:8px">
                <?= htmlspecialchars($detailArticle->title) ?>
            </h2>
            <p class="text-muted" style="font-size:13px;margin-bottom:4px">
                By <strong><?= htmlspecialchars($detailArticle->authorName) ?></strong>
                &nbsp; / &nbsp; Status: <strong><?= htmlspecialchars($detailArticle->status) ?></strong>
            </p>
            <p style="font-size:14px;margin-top:8px"><?= htmlspecialchars($detailArticle->excerpt) ?></p>

            <div class="flex items-center gap-3 mt-6" style="flex-wrap:wrap">
                <form method="POST" action="/pages/unverified-articles.php" data-review-form style="margin:0" onsubmit="return confirm('Verify this article? It will publish once every assigned expert verifies it.')">
                    <input type="hidden" name="action" value="verify_article">
                    <input type="hidden" name="article_id" value="<?= $detailArticle->id ?>">
                    <button type="submit" class="btn btn-secondary btn-sm">Verify</button>
                </form>

                <form method="POST" action="/pages/unverified-articles.php" data-review-form style="margin:0" onsubmit="return confirm('Reject this article? It will be moved back to draft for the author.')">
                    <input type="hidden" name="action" value="unverify_article">
                    <input type="hidden" name="article_id" value="<?= $detailArticle->id ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Unverify</button>
                </form>
            </div>
        </div>

        <div class="card mb-6" style="padding:20px 24px">
            <h3 style="font-size:16px;font-weight:700;margin-bottom:12px">Expert Review Progress</h3>
            <div style="display:flex;flex-direction:column;gap:10px">
                <?php foreach ($reviewProgress as $reviewer): ?>
                    <?php
                    $status = strtolower((string)($reviewer['status'] ?? 'pending'));
                    $badgeStyle = $status === 'verified'
                        ? 'background:#22c55e;color:#111827;'
                        : ($status === 'unverified'
                            ? 'background:#ef4444;color:#ffffff;'
                            : 'background:#f59e0b;color:#111827;');
                    ?>
                    <div class="flex items-center justify-between" style="gap:12px;flex-wrap:wrap">
                        <div>
                            <div style="font-weight:600"><?= htmlspecialchars($reviewer['full_name']) ?></div>
                            <div class="text-muted" style="font-size:13px"><?= htmlspecialchars($reviewer['email']) ?></div>
                        </div>
                        <span class="role-badge" style="<?= $badgeStyle ?>">
                            <?= htmlspecialchars(ucfirst($status)) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card" style="padding:20px 24px">
            <?php if (!empty($detailArticle->imagePath)): ?>
                <div class="article-banner" style="margin-bottom:20px">
                    <img src="/public/<?= htmlspecialchars($detailArticle->imagePath) ?>" alt="Article image">
                </div>
            <?php endif; ?>

            <h3 class="article-content-title">Article</h3>
            <div class="article-body">
                <?= $detailArticle->renderContent() ?>
            </div>
        </div>
    </div>

<?php else: ?>
    <?php dash_header('Unverified Articles', 'Articles waiting for your final category expert review'); ?>

    <div class="page-content">
        <?php if (empty($assignedCategories)): ?>
            <div class="alert alert-error">You are not assigned to any category yet.</div>
        <?php elseif (empty($unverifiedArticles)): ?>
            <p class="text-muted">There are no unverified articles waiting for your review right now.</p>
        <?php else: ?>
            <p class="article-count" style="margin-bottom:16px">
                <?= count($unverifiedArticles) ?> article<?= count($unverifiedArticles) !== 1 ? 's' : '' ?> waiting for your review
            </p>

            <div style="display:flex;flex-direction:column;gap:12px">
                <?php foreach ($unverifiedArticles as $article): ?>
                    <div class="card" style="padding:18px 22px">
                        <div class="flex items-center justify-between" style="flex-wrap:wrap;gap:12px">
                            <div style="flex:1;min-width:0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="category-tag <?= category_theme_class($article['category_name']) ?>"><?= htmlspecialchars($article['category_name']) ?></span>
                                    <?= trust_badge((int)$article['trust_score']) ?>
                                    <span class="role-badge" style="background:#f59e0b;color:#111827">
                                        <?= (int)($article['verified_reviews'] ?? 0) ?>/<?= (int)($article['total_reviews'] ?? 0) ?> verified
                                    </span>
                                </div>
                                <h3 style="font-size:16px;font-weight:700;font-family:Georgia,serif;margin-bottom:4px">
                                    <?= htmlspecialchars($article['title']) ?>
                                </h3>
                                <p class="text-muted" style="font-size:13px;margin-bottom:6px">
                                    By <?= htmlspecialchars($article['author_name']) ?>
                                </p>
                                <p style="margin:0;color:var(--muted)">
                                    <?= htmlspecialchars(mb_substr($article['excerpt'], 0, 180)) ?>
                                </p>
                            </div>

                            <div class="flex items-center gap-2" style="flex-wrap:wrap">
                                <a href="/pages/unverified-articles.php?id=<?= (int)$article['id'] ?>" class="btn btn-ghost btn-sm">Review</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
</main>
</div>

<script>
document.querySelectorAll('form[data-review-form]').forEach((form) => {
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitter = event.submitter;
        if (submitter) {
            submitter.disabled = true;
        }

        try {
            const response = await fetch(form.action || window.location.pathname, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form, submitter || undefined)
            });

            const data = await response.json();
            if (!response.ok || !data.ok) {
                throw new Error(data.error || 'Unable to review article.');
            }

            window.location.assign(data.redirect || '/pages/unverified-articles.php');
        } catch (error) {
            alert(error.message || 'Unable to review article.');
            if (submitter) {
                submitter.disabled = false;
            }
        }
    });
});
</script>

<?php page_foot(); ?>
