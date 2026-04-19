<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

header('Content-Type: application/json');

function buildArticleVerificationFingerprint(array $input, int $userId): string {
    $payload = [
        'user_id' => $userId,
        'title' => trim((string)($input['title'] ?? '')),
        'excerpt' => trim((string)($input['excerpt'] ?? '')),
        'content' => trim((string)($input['content'] ?? '')),
        'category_id' => (int)($input['category_id'] ?? 0),
        'source_url' => trim((string)($input['source_url'] ?? '')),
    ];

    return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

$auth = new AuthController();
$user = $auth->currentUser();

if (!$user) {
    http_response_code(403);
    echo json_encode(['error' => 'You must be logged in.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);

if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request body.']);
    exit;
}

$title = trim((string)($body['title'] ?? ''));
$excerpt = trim((string)($body['excerpt'] ?? ''));
$content = trim((string)($body['content'] ?? ''));
$category = trim((string)($body['category'] ?? ''));
$categoryId = (int)($body['category_id'] ?? 0);
$sourceUrl = trim((string)($body['source_url'] ?? ''));

if ($title === '' || $excerpt === '' || $content === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Title, summary, and content are required for AI verification.']);
    exit;
}

if ($sourceUrl !== '' && !filter_var($sourceUrl, FILTER_VALIDATE_URL)) {
    http_response_code(422);
    echo json_encode(['error' => 'Source URL must be a valid link.']);
    exit;
}

$webhookUrl = '';
if (defined('N8N_VERIFY_WEBHOOK_URL')) {
    $webhookUrl = trim((string)N8N_VERIFY_WEBHOOK_URL);
}
if ($webhookUrl === '') {
    $webhookUrl = trim((string)(getenv('N8N_VERIFY_WEBHOOK_URL') ?: ''));
}
if ($webhookUrl === '') {
    $webhookUrl = 'https://n8n.srv1502312.hstgr.cloud/webhook/sharedspace-ai-verify';
}

$payload = [
    'title' => $title,
    'excerpt' => $excerpt,
    'content' => $content,
    'category' => $category,
    'source_url' => $sourceUrl,
    'requested_by' => [
        'id' => $user->id,
        'name' => $user->fullName,
        'role' => $user->role,
    ],
];

$ch = curl_init($webhookUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_TIMEOUT => 45,
]);

$response = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

if ($response === false || $curlError !== '') {
    http_response_code(502);
    echo json_encode([
        'error' => 'Unable to reach n8n verification workflow.',
        'details' => $curlError ?: 'Unknown connection error.',
        'webhook_url' => $webhookUrl,
    ]);
    exit;
}

$decoded = json_decode($response, true);
if (!is_array($decoded)) {
    http_response_code(502);
    echo json_encode([
        'error' => 'n8n returned an invalid response.',
        'details' => $response,
        'status_code' => $httpCode,
    ]);
    exit;
}

if ($httpCode >= 400) {
    http_response_code(502);
    echo json_encode([
        'error' => $decoded['error'] ?? 'n8n verification failed.',
        'details' => $decoded,
        'status_code' => $httpCode,
    ]);
    exit;
}

$metrics = $decoded['metrics'] ?? [];
$normalizedMetrics = [
    'factual_accuracy' => max(0, min(100, (int)($metrics['factual_accuracy'] ?? $decoded['factual_accuracy'] ?? 0))),
    'source_quality' => max(0, min(100, (int)($metrics['source_quality'] ?? $decoded['source_quality'] ?? 0))),
    'bias_detection' => max(0, min(100, (int)($metrics['bias_detection'] ?? $decoded['bias_detection'] ?? 0))),
    'logical_consistency' => max(0, min(100, (int)($metrics['logical_consistency'] ?? $decoded['logical_consistency'] ?? 0))),
    'completeness' => max(0, min(100, (int)($metrics['completeness'] ?? $decoded['completeness'] ?? 0))),
];

$trustScore = (int)($decoded['trust_score'] ?? array_sum($normalizedMetrics) / 5);
$trustScore = max(0, min(100, $trustScore));

$verdict = trim((string)($decoded['verdict'] ?? ''));
if ($verdict === '') {
    $verdict = $trustScore >= 60
        ? 'Trust score is above 60%. Article can be published.'
        : 'Trust score is below 60%. Revise the article and run verification again.';
}

$summary = trim((string)($decoded['summary'] ?? ''));
if ($summary === '') {
    $summary = 'AI verification completed successfully.';
}

$_SESSION['article_ai_verification'] = [
    'fingerprint' => buildArticleVerificationFingerprint([
        'title' => $title,
        'excerpt' => $excerpt,
        'content' => $content,
        'category_id' => $categoryId,
        'source_url' => $sourceUrl,
    ], (int)$user->id),
    'trust_score' => $trustScore,
    'passed' => $trustScore >= 60,
    'summary' => $summary,
    'verdict' => $verdict,
    'metrics' => $normalizedMetrics,
    'source_url' => $sourceUrl,
    'source_label' => trim((string)($decoded['source_label'] ?? '')),
];

echo json_encode([
    'trust_score' => $trustScore,
    'summary' => $summary,
    'verdict' => $verdict,
    'metrics' => $normalizedMetrics,
    'source_url' => $sourceUrl,
    'source_label' => trim((string)($decoded['source_label'] ?? '')),
]);
