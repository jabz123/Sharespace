<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/article_flag_rules.php';

header('Content-Type: application/json');

// must be logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId    = $_SESSION['user_id'];
$articleId = (int)($_POST['article_id'] ?? 0);
$reason    = trim($_POST['reason'] ?? '');
$details   = trim($_POST['details'] ?? '');
$allowedReasons = article_flag_reason_options();

// ===== VALIDATION =====
if (!$articleId) {
    echo json_encode(['error' => 'Invalid article']);
    exit;
}

if ($reason === '' || $details === '') {
    echo json_encode(['error' => 'Reason and details are required']);
    exit;
}

if (!in_array($reason, $allowedReasons, true)) {
    echo json_encode(['error' => 'Invalid report reason']);
    exit;
}

$articleExists = DB::first(
    "SELECT id FROM articles WHERE id = ? AND status = 'published'",
    [$articleId]
);

if (!$articleExists) {
    echo json_encode(['error' => 'Article not found']);
    exit;
}

$detailsError = validate_article_flag_details($details);
if ($detailsError !== null) {
    echo json_encode(['error' => $detailsError]);
    exit;
}


// ===== PREVENT DUPLICATE FLAG =====
$existing = DB::first(
    "SELECT id FROM article_flags WHERE user_id = ? AND article_id = ?",
    [$userId, $articleId]
);

if ($existing) {
    echo json_encode(['error' => 'You already flagged this article']);
    exit;
}


// ===== INSERT FLAG =====
DB::execute(
    "INSERT INTO article_flags (user_id, article_id, reason, details, created_at)
     VALUES (?, ?, ?, ?, NOW())",
    [$userId, $articleId, $reason, $details]
);

// ===== SUCCESS =====
echo json_encode(['ok' => true]);
