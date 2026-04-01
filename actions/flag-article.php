<?php
require_once __DIR__ . '/../includes/auth.php';

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


// ===== VALIDATION =====
if (!$articleId || !$reason || !$details) {
    echo json_encode(['error' => 'All fields are required']);
    exit;
}

if (strlen($details) > 100) {
    echo json_encode(['error' => 'Details too long']);
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