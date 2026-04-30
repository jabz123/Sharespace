<?php

//receive post feednack form from profile page
//will validate form input and insert into site_feedback as pending
//will call comment_moderation_reject in comment_moderation_rules to validate feedback
//also will call sentiment analysis webhook to get sentiment score and label

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/comment_moderation_rules.php';

DB::ensureSiteFeedbackSentimentColumns();

function feedback_word_count(string $content): int
{
    $normalized = preg_replace("/[^\p{L}\p{N}']+/u", ' ', $content) ?? '';
    $words = preg_split('/\s+/u', trim($normalized), -1, PREG_SPLIT_NO_EMPTY);

    return count(array_filter($words ?: [], static fn($word) => preg_match('/[\p{L}\p{N}]/u', $word)));
}

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

$allowedFeedbackRoles = ['free', 'premium', 'category_admin'];

if (!in_array($user->role ?? '', $allowedFeedbackRoles, true)) {
    redirect('/pages/profile.php', 'Your account type cannot submit feedback.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/profile.php');
}

$rating = (int) ($_POST['rating'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($rating < 1 || $rating > 5) {
    redirect('/pages/profile.php', 'Please select a rating from 1 to 5.');
}

if ($content === '') {
    redirect('/pages/profile.php', 'Please enter your feedback.');
}

if (mb_strlen($content) > 500) {
    redirect('/pages/profile.php', 'Feedback must be 500 characters or less.');
}

$feedbackLength = mb_strlen($content);
if ($feedbackLength < 20) {
    redirect('/pages/profile.php', 'Feedback must be at least 20 characters. Current character count: ' . $feedbackLength . '.');
}

$feedbackWordCount = feedback_word_count($content);
if ($feedbackWordCount <= 3) {
    redirect('/pages/profile.php', 'Feedback must be more than 3 words. Current word count: ' . $feedbackWordCount . '.');
}

$moderationError = comment_moderation_reject($content, 'Feedback');
if ($moderationError !== null) {
    redirect('/pages/profile.php', $moderationError);
}

$name = trim((string) ($user->fullName ?? ''));
if ($name === '') {
    $name = trim((string) ($user->email ?? 'Anonymous User'));
}

$role = trim((string) ($user->role ?? 'free'));
$roleLabel = ucfirst(str_replace('_', ' ', $role));

DB::execute(
    "INSERT INTO site_feedback (
        user_id,
        name,
        role,
        rating,
        content,
        sentiment_label,
        sentiment_score,
        sentiment_status,
        is_approved,
        created_at
     ) VALUES (?, ?, ?, ?, ?, NULL, NULL, 'pending', 0, NOW())",
    [
        $user->id,
        $name,
        $roleLabel,
        $rating,
        $content,
    ]
);

$feedbackId = DB::lastId();

$webhookUrl = '';
if (defined('N8N_FEEDBACK_SENTIMENT_WEBHOOK_URL')) {
    $webhookUrl = trim((string) N8N_FEEDBACK_SENTIMENT_WEBHOOK_URL);
}
if ($webhookUrl === '') {
    $webhookUrl = trim((string) (getenv('N8N_FEEDBACK_SENTIMENT_WEBHOOK_URL') ?: ''));
}

if ($webhookUrl !== '') {
    $payload = [
        'feedback_id' => $feedbackId,
        'rating' => $rating,
        'content' => $content,
        'name' => $name,
        'role' => $roleLabel,
        'submitted_by' => [
            'id' => $user->id,
            'role' => $user->role,
            'email' => $user->email,
        ],
        'callback' => [
            'url' => (defined('APP_BASE_URL') ? rtrim((string) APP_BASE_URL, '/') : '') . '/api/feedback-sentiment-callback.php',
            'secret' => defined('FEEDBACK_SENTIMENT_CALLBACK_SECRET') ? FEEDBACK_SENTIMENT_CALLBACK_SECRET : '',
        ],
    ];

    $ch = curl_init($webhookUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 8,
    ]);

    curl_exec($ch);
    curl_close($ch);
}

redirect('/pages/profile.php', null, 'Feedback submitted successfully! It is now pending sentiment review and approval.');
