<?php
//for category admins to decide whether articles get verified or not
//post will be sent here fropm unverified articles page with article id and action (verify or unverify)
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

function actionRedirect(string $url, ?string $error = null, ?string $success = null): never
{
    redirect($url, $error, $success);
}

$auth = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role !== 'category_admin') {
    actionRedirect('/dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    actionRedirect('/pages/unverified-articles.php');
}

$action = $_POST['action'] ?? '';
$articleId = (int) ($_POST['article_id'] ?? 0);

if (!in_array($action, ['verify_article', 'unverify_article'], true) || $articleId <= 0) {
    actionRedirect('/pages/unverified-articles.php', 'Unable to review article.');
}
//decision based on which button clicked
$decision = $action === 'verify_article' ? 'verified' : 'unverified';
try {
    $result = $adminCtrl->reviewPendingArticle($articleId, (int) $user->id, $decision);
} catch (Throwable $error) {
    error_log('Expert article review failed: ' . $error->getMessage());
    $latestArticle = $adminCtrl->getArticleById($articleId);
    $reviewApplied = $latestArticle
        && (
            ($decision === 'verified' && $latestArticle->status === 'published')
            || ($decision === 'unverified' && $latestArticle->status === 'draft')
        );
    if ($reviewApplied) {
        $message = $decision === 'verified'
            ? 'Article verified. One category expert approval is enough, so it has been published.'
            : 'Article rejected. It has been moved back to draft for the author.';
        actionRedirect('/pages/unverified-articles.php', null, $message);
    }
    actionRedirect('/pages/unverified-articles.php', 'Unable to review article. Please try again.');
}
//redirect back to unverified articles page with success msg if verified
if (isset($result['ok'])) {
    $message = $decision === 'verified'
        ? 'Article verified. One category expert approval is enough, so it has been published.'
        : 'Article rejected. It has been moved back to draft for the author.';

    actionRedirect('/pages/unverified-articles.php', null, $message);
}

actionRedirect('/pages/unverified-articles.php', $result['error'] ?? 'Unable to review article.');
