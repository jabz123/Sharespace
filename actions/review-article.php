<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';

function actionRedirect(string $url, ?string $error = null, ?string $success = null): never {
    if ($error) {
        $_SESSION['flash_error'] = $error;
    }
    if ($success) {
        $_SESSION['flash_success'] = $success;
    }

    header('Location: ' . $url, true, 303);
    exit;
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
$articleId = (int)($_POST['article_id'] ?? 0);

if (!in_array($action, ['verify_article', 'unverify_article'], true) || $articleId <= 0) {
    actionRedirect('/pages/unverified-articles.php', 'Unable to review article.');
}

$decision = $action === 'verify_article' ? 'verified' : 'unverified';
$result = $adminCtrl->reviewPendingArticle($articleId, (int)$user->id, $decision);

if (isset($result['ok'])) {
    $message = $decision === 'verified'
        ? 'Article verified. One category expert approval is enough, so it has been published.'
        : 'Article rejected. It has been moved back to draft for the author.';

    actionRedirect('/pages/unverified-articles.php', null, $message);
}

actionRedirect('/pages/unverified-articles.php', $result['error'] ?? 'Unable to review article.');
