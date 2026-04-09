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
$articleRow = DB::first(
    "SELECT a.id, a.title, a.excerpt, a.content, a.category_id,
            c.name AS category_name, c.description AS category_description
     FROM articles a
     LEFT JOIN categories c ON c.id = a.category_id
     WHERE a.id = ?",
    [$articleId]
);

$payload = [
    'reporter' => ['user_id' => $userId],
    'article'  => [
        'id'          => $articleId,
        'title'       => $articleRow['title']   ?? '',
        'excerpt'     => $articleRow['excerpt'] ?? '',
        'content'     => $articleRow['content'] ?? '',
        'category_id' => $articleRow['category_id'] ?? 0,
        'category_name' => $articleRow['category_name'] ?? '',
    ],
    'category' => [
        'id'          => $articleRow['category_id']          ?? 0,
        'name'        => $articleRow['category_name']        ?? '',
        'description' => $articleRow['category_description'] ?? '',
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
$err      = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

error_log("n8n httpCode=" . $httpCode);
error_log("n8n curlErr=" . $err);
error_log("n8n response=" . $response);

// Optional: if you want to hard-fail when n8n rejects:
// if ($httpCode >= 400) { echo $response; exit; }

// ===== SUCCESS =====
echo json_encode(['ok' => true]);
