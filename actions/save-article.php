<?php
//for saving article as draft or publishing.
//post will be sent here from create article page with article id (if editing), title, content, category and action (save or publish)
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

function actionRedirect(string $url, ?string $error = null, ?string $success = null): never
{
    if ($error) {
        $_SESSION['flash_error'] = $error;
    }
    if ($success) {
        $_SESSION['flash_success'] = $success;
    }

    header('Location: ' . $url, true, 303);//303 to prevent form resubmission 
    exit;
}
//build unique fingerprint for article based on user input and id. so that users cannot change content after getting verified
function buildArticleVerificationFingerprint(array $input, int $userId): string
{
    $normalize = static function ($value): string {
        return trim(str_replace(["\r\n", "\r"], "\n", (string) $value));
    };

    $payload = [
        'user_id' => $userId,
        'title' => $normalize($input['title'] ?? ''),
        'excerpt' => $normalize($input['excerpt'] ?? ''),
        'content' => $normalize($input['content'] ?? ''),
        'category_id' => (int) ($input['category_id'] ?? 0),
        'source_url' => $normalize($input['source_url'] ?? ''),
    ];

    return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}
//check if there is stored verification result for article and check if fingerprint matches
function resolveStoredArticleVerification(?Article $article, string $fingerprint): ?array
{
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
//store verification result in db so it can be reused if user edits article with no changes
function persistArticleVerification(int $articleId, int $authorId, array $verification): void
{
    if ($articleId <= 0 || empty($verification['fingerprint'])) {
        return;
    }

    DB::execute(
        'UPDATE articles
         SET verification_fingerprint = ?, verification_payload = ?, verification_checked_at = NOW()
         WHERE id = ? AND author_id = ?',
        [
            (string) $verification['fingerprint'],
            json_encode($verification, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $articleId,
            $authorId,
        ]
    );
}

function validateArticleImageUpload(array $file): ?string
{
    $maxBytes = 5 * 1024 * 1024; // 5MB
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif'];
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/avif'];

    $errorCode = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($errorCode === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($errorCode !== UPLOAD_ERR_OK) {
        return 'Image upload failed. Please try another file.';
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    $originalName = (string) ($file['name'] ?? '');
    $size = (int) ($file['size'] ?? 0);

    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        return 'Invalid upload detected. Please try again.';
    }
    if ($size <= 0 || $size > $maxBytes) {
        return 'Image must be between 1 byte and 5MB.';
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExtensions, true)) {
        return 'Unsupported image format. Use JPG, PNG, WEBP, GIF, or AVIF.';
    }

    $mimeType = mime_content_type($tmpName) ?: '';
    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        return 'Invalid image file type. Please upload a real image.';
    }

    if (@getimagesize($tmpName) === false) {
        return 'Uploaded file is not a valid image.';
    }

    return null;
}

$auth = new AuthController();
$articleCtrl = new ArticleController();
$autoPublishTrustScore = 81;

$auth->requireAuth();
$user = $auth->currentUser();
//send to write if not post request 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    actionRedirect('/pages/write.php');
}

$editId = (int) ($_GET['id'] ?? 0);
$article = null;
$isEdit = false;
//check if article belongs to user if edit id provided. if not redirect back to my articles 
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
$requestFingerprint = buildArticleVerificationFingerprint($_POST, (int) $user->id);
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
//handle image upload logic. If image uploaded, save to server.
if ($canUploadImage && isset($_FILES['article_image'])) {
    $uploadValidationError = validateArticleImageUpload($_FILES['article_image']);
    if ($uploadValidationError !== null) {
        actionRedirect($isEdit ? '/pages/write.php?id=' . (int) $editId : '/pages/write.php', $uploadValidationError);
    }

    if ($_FILES['article_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../public/uploads/articles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . '_' . basename($_FILES['article_image']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['article_image']['tmp_name'], $targetPath)) {
        $imagePath = 'uploads/articles/' . $fileName;
    } else {
        actionRedirect($isEdit ? '/pages/write.php?id=' . (int) $editId : '/pages/write.php', 'Unable to store uploaded image. Please try again.');
    }
    }
}

$_POST['image_path'] = $imagePath;
//save as draft logic. 
if ($action === 'draft') {
    $_POST['status'] = 'draft';

    if ($isEdit) {
        $result = $articleCtrl->update($editId, $user->id, $_POST);
    } else {
        $result = $articleCtrl->saveDraft($user->id, $_POST);
    }

    if (isset($result['ok'])) {
        $savedArticleId = $isEdit ? $editId : (int) ($result['id'] ?? 0);
        if ($hasRequestVerification && $savedArticleId > 0) {
            persistArticleVerification($savedArticleId, (int) $user->id, $requestVerification);
        }
        actionRedirect('/pages/my-articles.php', null, 'Draft saved!');
    }

    actionRedirect($isEdit ? '/pages/write.php?id=' . $editId : '/pages/write.php', $result['error'] ?? 'Unable to save draft.');
}

//article publish logic. must pass ai verification. if no changes, use back prev verification results
$fingerprint = buildArticleVerificationFingerprint($_POST, (int) $user->id);
$verification = $_SESSION['article_ai_verification'] ?? null;
$storedVerification = $isEdit ? resolveStoredArticleVerification($article, $fingerprint) : null;
if (is_array($storedVerification) && (($verification['fingerprint'] ?? '') !== $fingerprint)) {
    $verification = $storedVerification;
    $_SESSION['article_ai_verification'] = $storedVerification;
}
//check if there is valid verification results from current request
$isVerificationCurrent = is_array($verification)
    && ($verification['fingerprint'] ?? '') === $fingerprint;
$verifiedTrustScore = $isVerificationCurrent ? (int) ($verification['trust_score'] ?? 0) : 0;
$verifiedDecision = $isVerificationCurrent ? trim((string) ($verification['publish_decision'] ?? '')) : '';

$hasPassingVerification = $isVerificationCurrent
    && !empty($verification['passed'])
    && $verifiedTrustScore >= $autoPublishTrustScore
    && $verifiedDecision === 'auto_publish';

if (!$hasPassingVerification) { //redirect back to write if no valid verification
    actionRedirect($isEdit ? '/pages/write.php?id=' . $editId : '/pages/write.php', 'Run AI Fact Check and get an Auto Publish result at 81% or above before publishing this article.');
}

$_POST['status'] = 'published';
$_POST['trust_score'] = $verifiedTrustScore;

if ($isEdit) {
    $result = $articleCtrl->update($editId, $user->id, $_POST);
} else {
    $result = $articleCtrl->publish($user->id, $_POST);
}
//store verification result if published successfully. If article edited without changes, reuse prev results.
if (isset($result['ok'])) {
    unset($_SESSION['article_ai_verification']);
    actionRedirect('/pages/my-articles.php', null, 'Article published successfully.');
}

actionRedirect($isEdit ? '/pages/write.php?id=' . $editId : '/pages/write.php', $result['error'] ?? 'Unable to submit article.');
