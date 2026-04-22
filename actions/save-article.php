<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

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

function buildArticleVerificationFingerprint(array $input, int $userId): string {
    $normalize = static function ($value): string {
        return trim(str_replace(["\r\n", "\r"], "\n", (string)$value));
    };

    $payload = [
        'user_id' => $userId,
        'title' => $normalize($input['title'] ?? ''),
        'excerpt' => $normalize($input['excerpt'] ?? ''),
        'content' => $normalize($input['content'] ?? ''),
        'category_id' => (int)($input['category_id'] ?? 0),
        'source_url' => $normalize($input['source_url'] ?? ''),
    ];

    return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function resolveStoredArticleVerification(?Article $article, string $fingerprint): ?array {
    if (!$article) {
        return null;
    }

    if (($article->verificationFingerprint ?? '') !== $fingerprint) {
        return null;
    }

    $rawPayload = $article->verificationPayload ?? null;
    if (!is_string($rawPayload) || trim($rawPayload) === '') {
        return null;
    }

    $decoded = json_decode($rawPayload, true);
    return is_array($decoded) ? $decoded : null;
}

function persistArticleVerification(int $articleId, int $authorId, array $verification): void {
    if ($articleId <= 0 || empty($verification['fingerprint'])) {
        return;
    }

    DB::execute(
        'UPDATE articles
         SET verification_fingerprint = ?, verification_payload = ?, verification_checked_at = NOW()
         WHERE id = ? AND author_id = ?',
        [
            (string)$verification['fingerprint'],
            json_encode($verification, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $articleId,
            $authorId,
        ]
    );
}

$auth = new AuthController();
$articleCtrl = new ArticleController();
$autoPublishTrustScore = 81;

$auth->requireAuth();
$user = $auth->currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    actionRedirect('/pages/write.php');
}

$editId = (int)($_GET['id'] ?? 0);
$article = null;
$isEdit = false;

if ($editId > 0) {
    $article = $articleCtrl->getByIdForAuthor($editId, $user->id);
    if (!$article || $article->authorId !== $user->id) {
        actionRedirect('/pages/my-articles.php', 'Article not found or permission denied.');
    }
    $isEdit = true;
}

$canUploadImage = ($user->role === 'premium' || $user->role === 'category_admin');
$action = $_POST['action'] ?? 'publish';
$imagePath = $article->imagePath ?? null;
$requestFingerprint = buildArticleVerificationFingerprint($_POST, (int)$user->id);
$requestVerification = $_SESSION['article_ai_verification'] ?? null;
$hasRequestVerification = is_array($requestVerification)
    && (($requestVerification['fingerprint'] ?? '') === $requestFingerprint);

if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
    if (!empty($article->imagePath)) {
        $filePath = __DIR__ . '/../public/' . $article->imagePath;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
    $imagePath = null;
}

if ($canUploadImage && isset($_FILES['article_image']) && $_FILES['article_image']['error'] === 0) {
    $uploadDir = __DIR__ . '/../public/uploads/articles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['article_image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['article_image']['tmp_name'], $targetPath)) {
        $imagePath = 'uploads/articles/' . $fileName;
    }
}

$_POST['image_path'] = $imagePath;

if ($action === 'draft') {
    $_POST['status'] = 'draft';

    if ($isEdit) {
        $result = $articleCtrl->update($editId, $user->id, $_POST);
    } else {
        $result = $articleCtrl->saveDraft($user->id, $_POST);
    }

    if (isset($result['ok'])) {
        $savedArticleId = $isEdit ? $editId : (int)($result['id'] ?? 0);
        if ($hasRequestVerification && $savedArticleId > 0) {
            persistArticleVerification($savedArticleId, (int)$user->id, $requestVerification);
        }
        actionRedirect('/pages/my-articles.php', null, 'Draft saved!');
    }

    actionRedirect($isEdit ? '/pages/write.php?id=' . $editId : '/pages/write.php', $result['error'] ?? 'Unable to save draft.');
}

$fingerprint = buildArticleVerificationFingerprint($_POST, (int)$user->id);
$verification = $_SESSION['article_ai_verification'] ?? null;
$storedVerification = $isEdit ? resolveStoredArticleVerification($article, $fingerprint) : null;
if (is_array($storedVerification) && (($verification['fingerprint'] ?? '') !== $fingerprint)) {
    $verification = $storedVerification;
    $_SESSION['article_ai_verification'] = $storedVerification;
}
$isVerificationCurrent = is_array($verification)
    && ($verification['fingerprint'] ?? '') === $fingerprint;
$verifiedTrustScore = $isVerificationCurrent ? (int)($verification['trust_score'] ?? 0) : 0;
$verifiedDecision = $isVerificationCurrent ? trim((string)($verification['publish_decision'] ?? '')) : '';
$hasPassingVerification = $isVerificationCurrent
    && !empty($verification['passed'])
    && $verifiedTrustScore >= $autoPublishTrustScore
    && $verifiedDecision === 'auto_publish';

if (!$hasPassingVerification) {
    actionRedirect($isEdit ? '/pages/write.php?id=' . $editId : '/pages/write.php', 'Run AI Fact Check and get an Auto Publish result at 81% or above before publishing this article.');
}

$_POST['status'] = 'published';
$_POST['trust_score'] = $verifiedTrustScore;

if ($isEdit) {
    $result = $articleCtrl->update($editId, $user->id, $_POST);
} else {
    $result = $articleCtrl->publish($user->id, $_POST);
}

if (isset($result['ok'])) {
    unset($_SESSION['article_ai_verification']);
    actionRedirect('/pages/my-articles.php', null, 'Article published successfully.');
}

actionRedirect($isEdit ? '/pages/write.php?id=' . $editId : '/pages/write.php', $result['error'] ?? 'Unable to submit article.');
