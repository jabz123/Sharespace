<?php
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

// must be logged in
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId              = (int)$_SESSION['user_id'];
$articleId           = (int)($_POST['article_id'] ?? 0);
$reason              = trim((string)($_POST['reason'] ?? ''));
$details             = trim((string)($_POST['details'] ?? ''));
$suggestedCategoryId = (int)($_POST['suggested_category_id'] ?? 0);


// ===== VALIDATION =====
$ALLOWED_REASONS = ['INAPPROPRIATE_LANGUAGE', 'MISINFORMATION', 'HATE_SPEECH', 'VIOLENCE', 'ADVERTISING', 'WRONG_CATEGORY'];

if (!$articleId || !in_array($reason, $ALLOWED_REASONS, true)) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

if ($reason !== 'WRONG_CATEGORY' && $details === '') {
    echo json_encode(['error' => 'Details are required']);
    exit;
}

if ($reason === 'WRONG_CATEGORY' && $suggestedCategoryId <= 0) {
    echo json_encode(['error' => 'Suggested category is required']);
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


// ===== LOAD ARTICLE + CATEGORY =====
$article = DB::first(
    "SELECT a.id, a.title, a.excerpt, a.content, a.category_id,
            c.name AS category_name, c.description AS category_description
     FROM articles a
     JOIN categories c ON c.id = a.category_id
     WHERE a.id = ?",
    [$articleId]
);

if (!$article) {
    echo json_encode(['error' => 'Article not found']);
    exit;
}

$suggestedCategory = null;
if ($reason === 'WRONG_CATEGORY') {
    $suggestedCategory = DB::first(
        "SELECT id, name, description FROM categories WHERE id = ?",
        [$suggestedCategoryId]
    );
    if (!$suggestedCategory) {
        echo json_encode(['error' => 'Invalid suggested category']);
        exit;
    }
}


// ===== INSERT PENDING FLAG =====
DB::execute(
    "INSERT INTO article_flags (user_id, article_id, reason, details, suggested_category_id, status, created_at)
     VALUES (?, ?, ?, ?, ?, 'PENDING', NOW())",
    [
        $userId,
        $articleId,
        $reason,
        ($details !== '' ? $details : null),
        ($reason === 'WRONG_CATEGORY' ? $suggestedCategoryId : null),
    ]
);
$flagId = DB::lastId();


// ===== CALL n8n WEBHOOK =====
$n8nUrl = N8N_FLAG_WEBHOOK_URL;
$secret = N8N_SHARED_SECRET;

$payload = [
    'reporter' => ['user_id' => $userId],
    'article'  => [
        'id'            => (int)$article['id'],
        'title'         => (string)$article['title'],
        'excerpt'       => (string)$article['excerpt'],
        'content'       => (string)$article['content'],
        'category_id'   => (int)$article['category_id'],
        'category_name' => (string)$article['category_name'],
    ],
    'category' => [
        'id'          => (int)$article['category_id'],
        'name'        => (string)$article['category_name'],
        'description' => (string)($article['category_description'] ?? ''),
    ],
    'flag' => [
        'id'                     => $flagId,
        'reason'                 => $reason,
        'details'                => $details,
        'suggested_category_id'  => $suggestedCategoryId ?: null,
        'suggested_category_name'=> $suggestedCategory['name'] ?? null,
    ],
];

$ch = curl_init($n8nUrl);
curl_setopt_array($ch, [
    CURLOPT_POST           => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'x-ss-secret: ' . $secret,
    ],
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_CONNECTTIMEOUT => 3,
    CURLOPT_TIMEOUT        => 12,
]);

$raw      = curl_exec($ch);
$curlErr  = curl_error($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// n8n unavailable — route to human review
if ($raw === false || $httpCode < 200 || $httpCode >= 300) {
    DB::execute(
        "UPDATE article_flags SET status = 'NEEDS_HUMAN', ai_admin_notes = ? WHERE id = ?",
        ['AI triage unavailable: ' . ($curlErr ?: ('HTTP ' . $httpCode)), $flagId]
    );
    echo json_encode(['ok' => true, 'decision' => 'needs_human', 'message' => 'Submitted for admin review.']);
    exit;
}

$result = json_decode($raw, true);
if (!is_array($result) || empty($result['decision'])) {
    DB::execute(
        "UPDATE article_flags SET status = 'NEEDS_HUMAN', ai_admin_notes = ? WHERE id = ?",
        ['Invalid AI response', $flagId]
    );
    echo json_encode(['ok' => true, 'decision' => 'needs_human', 'message' => 'Submitted for admin review.']);
    exit;
}

// map AI decision to flag status; normalise unknown values to needs_human
$decision  = $result['decision']; // accept | reject | needs_human
$statusMap = ['accept' => 'ACCEPTED', 'reject' => 'REJECTED', 'needs_human' => 'NEEDS_HUMAN'];
if (!isset($statusMap[$decision])) {
    $decision = 'needs_human';
}
$status = $statusMap[$decision];

DB::execute(
    "UPDATE article_flags
     SET status = ?, ai_confidence = ?, ai_reason_codes_json = ?, ai_user_message = ?, ai_admin_notes = ?
     WHERE id = ?",
    [
        $status,
        isset($result['confidence'])   ? (float)$result['confidence']          : null,
        isset($result['reason_codes']) ? json_encode($result['reason_codes'])   : null,
        isset($result['user_message']) ? (string)$result['user_message']        : null,
        isset($result['admin_notes'])  ? (string)$result['admin_notes']         : null,
        $flagId,
    ]
);


// ===== RESPOND TO FRONTEND =====
echo json_encode([
    'ok'       => true,
    'decision' => $decision,
    'message'  => $result['user_message'] ?? ($decision === 'reject' ? 'Flag rejected.' : 'Flag submitted.'),
]);