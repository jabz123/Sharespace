<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

DB::ensureSiteFeedbackSentimentColumns();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$expectedSecret = '';
if (defined('FEEDBACK_SENTIMENT_CALLBACK_SECRET')) {
    $expectedSecret = trim((string) FEEDBACK_SENTIMENT_CALLBACK_SECRET);
}
if ($expectedSecret === '') {
    $expectedSecret = trim((string) (getenv('FEEDBACK_SENTIMENT_CALLBACK_SECRET') ?: ''));
}

if ($expectedSecret !== '') {
    $providedSecret = trim((string) ($_SERVER['HTTP_X_SHAREDSPACE_SECRET'] ?? ''));
    if (!hash_equals($expectedSecret, $providedSecret)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid callback secret.']);
        exit;
    }
}

$body = json_decode(file_get_contents('php://input'), true);
if (!is_array($body)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON body.']);
    exit;
}

$feedbackId = (int) ($body['feedback_id'] ?? 0);
$label = trim((string) ($body['sentiment_label'] ?? ''));
$score = $body['sentiment_score'] ?? null;
$status = trim((string) ($body['sentiment_status'] ?? ''));

if ($feedbackId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'feedback_id is required.']);
    exit;
}

$allowedLabels = ['positive', 'neutral', 'negative', 'mixed', 'unclear'];
if (!in_array($label, $allowedLabels, true)) {
    $label = 'unclear';
}

$allowedStatuses = ['pending', 'aligned', 'mixed', 'contradiction', 'unclear', 'error'];
if (!in_array($status, $allowedStatuses, true)) {
    $status = 'unclear';
}

$numericScore = null;
if ($score !== null && $score !== '') {
    $numericScore = max(-1, min(1, (float) $score));
}

$approval = array_key_exists('is_approved', $body)
    ? (int) ((bool) $body['is_approved'])
    : null;

if ($approval === null) {
    $approval = in_array($status, ['aligned', 'mixed'], true) ? 1 : 0;
}

DB::execute(
    'UPDATE site_feedback
     SET sentiment_label = ?,
         sentiment_score = ?,
         sentiment_status = ?,
         is_approved = ?
     WHERE id = ?',
    [$label, $numericScore, $status, $approval, $feedbackId]
);

echo json_encode([
    'ok' => true,
    'feedback_id' => $feedbackId,
    'sentiment_label' => $label,
    'sentiment_status' => $status,
    'is_approved' => $approval,
]);
