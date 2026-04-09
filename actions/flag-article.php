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

// ===== CALL N8N FOR AI TRIAGE =====
$payload = [
    'reporter' => ['user_id' => $userId],
    'article'  => [
        'id' => $articleId,
        // ideally include title/content/category too (see note below)
    ],
    'flag' => [
        'reason'  => $reason,
        'details' => $details,
    ],
];

$ch = curl_init(N8N_FLAG_WEBHOOK_URL);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-ss-secret: ' . N8N_SHARED_SECRET,
    ],
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_TIMEOUT => 15,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    // Optional: log curl_error($ch)
}
curl_close($ch);

$response = curl_exec($ch);
$err = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

error_log("n8n httpCode=" . $httpCode);
error_log("n8n curlErr=" . $err);
error_log("n8n response=" . $response);

// Optional: if you want to hard-fail when n8n rejects:
// if ($httpCode >= 400) { echo $response; exit; }

// ===== SUCCESS =====
echo json_encode(['ok' => true]);
