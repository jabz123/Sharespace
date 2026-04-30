<?php
//this is for the ai article overview.
//accepts article_id via POST JSON and calls openrouter to get ai summary key points and return as json


require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

$auth = new AuthController();
$user = $auth->currentUser();

// must be logged in
if (!$user) {
    http_response_code(403);
    echo json_encode(['error' => 'You must be logged in.']);
    exit;
}
//only allow post with json body
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$articleId = (int) ($body['article_id'] ?? 0);

// check for valid article ID in db
if (!$articleId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing article_id.']);
    exit;
}

// fetch article — any published/suspended article can do the AI summary
//article must be published or suspended only.
$article = DB::first(
    "SELECT a.title, a.excerpt, a.content, a.trust_score,
            u.full_name AS author_name, c.name AS category_name
     FROM articles a
     JOIN users u ON u.id = a.author_id
     JOIN categories c ON c.id = a.category_id
     WHERE a.id = ? AND a.status IN ('published', 'suspended')",
    [$articleId]
);

if (!$article) {
    http_response_code(404);
    echo json_encode(['error' => 'Article not found.']);
    exit;
}
//check for api kry
$apiKey = defined('SUMMARY_API_KEY') ? SUMMARY_API_KEY : '';
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'API key not configured.']);
    exit;
}



//file cache. reduces token usage and redundant calls.

$cacheDir = dirname(__DIR__) . '/tmp';
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0775, true);
}
$cacheFile = $cacheDir . '/ai_overview_' . $articleId . '.json';
$cacheTtlSeconds = 1800; // 30 minutes

if (is_file($cacheFile)) {
    $cachedRaw = @file_get_contents($cacheFile);
    $cached = json_decode((string) $cachedRaw, true);

    if (
        is_array($cached) &&
        isset($cached['created_at'], $cached['data']) &&
        (time() - (int) $cached['created_at'] <= $cacheTtlSeconds)
    ) {
        echo json_encode($cached['data']);
        exit;
    }
}

// cap excerpt size to reduce token usage and rate limit pressure
//should reduce error 429 shit
$excerptRaw = (string) ($article['excerpt'] ?? '');
$safeExcerpt = function_exists('mb_substr')
    ? mb_substr($excerptRaw, 0, 700)
    : substr($excerptRaw, 0, 700);

// prompt for ai
$prompt = <<<PROMPT
You are an editorial assistant for SharedSpace, a news platform.
Give the reader a quick, helpful AI overview of the following article.

Title: {$article['title']}
Category: {$article['category_name']}
Author: {$article['author_name']}
Excerpt: {$safeExcerpt}

Respond ONLY with valid JSON, no markdown, no code fences:
{
  "summary": "<3-4 sentence plain-language summary>",
  "key_points": ["<point 1>", "<point 2>", "<point 3>"],
  "tone": "<e.g. Informative / Opinion / Investigative>",
  "read_time": "<estimated read time, e.g. 3 min read>"
}
PROMPT;

$url = 'https://api.groq.com/openai/v1/chat/completions';

// model fallback chain
$models = [
    'llama-3.3-70b-versatile',
    'llama-3.1-8b-instant',
];

$finalResponse = null;
$finalHttpCode = 0;
$lastErrorBody = null;

foreach ($models as $model) {
    // retry each model up to 3 times for transient errors
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens' => 260,
        ];

        // OpenRouter API call with cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT => 30,
        ]);
        //execute and capture response and errors
        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $finalResponse = $response;
        $finalHttpCode = $httpCode;
        $lastErrorBody = json_decode((string) $response, true);

        if ($curlErr) {
            error_log("Groq cURL error [{$model} attempt {$attempt}]: {$curlErr}");
            continue;
        }

        if ($response && $httpCode === 200) {
            break 2; // success across both loops
        }

        // Retry transient statuses
        if (in_array($httpCode, [429, 500, 502, 503, 504], true)) {
            // exponential-ish backoff: 1.5s, 3s, 6s
            $sleepMicros = (int) (pow(2, $attempt) * 750000);
            usleep($sleepMicros);
            continue;
        }

        // non-retryable -> next model
        break;
    }
}

// 429 shit will keep happening if too many requests
if (!$finalResponse || $finalHttpCode !== 200) {
    if ($finalHttpCode === 429) {
        http_response_code(429);
        echo json_encode(['error' => 'AI is busy right now. Please retry in 30-60 seconds.']);
        exit;
    }

    error_log('Groq API HTTP code: ' . $finalHttpCode);
    error_log('Groq API response: ' . (string) $finalResponse);

    http_response_code(502);
    echo json_encode([
        'error' => 'AI provider is unavailable right now. Please try again later.',
        'code' => $finalHttpCode,
        'details' => $lastErrorBody,
    ]);
    exit;
}

// Parse response
$data = json_decode((string) $finalResponse, true);
$rawText = $data['choices'][0]['message']['content'] ?? '';
$rawText = trim((string) $rawText);

// strip possible code fences
$rawText = preg_replace('/^```json\s*/i', '', $rawText);
$rawText = preg_replace('/^```\s*/i', '', $rawText);
$rawText = preg_replace('/\s*```$/', '', $rawText);

$result = json_decode($rawText, true);

if (!$result || !isset($result['summary'])) {
    error_log('AI invalid JSON payload: ' . $rawText);
    http_response_code(500);
    echo json_encode(['error' => 'AI did not return a valid response. Try again.']);
    exit;
}

// normalize output
$out = [
    'summary' => (string) ($result['summary'] ?? ''),
    'key_points' => is_array($result['key_points'] ?? null) ? $result['key_points'] : [],
    'tone' => (string) ($result['tone'] ?? 'Informative'),
    'read_time' => (string) ($result['read_time'] ?? '3 min read'),
];

// cache result (best effort)
@file_put_contents($cacheFile, json_encode([
    'created_at' => time(),
    'data' => $out,
]), LOCK_EX);

echo json_encode($out);
