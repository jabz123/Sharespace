<?php

/**
 * BOUNDARY — AI Article Overview endpoint (api/ai-overview.php)
 * Accessible to any logged-in user.
 * Accepts article_id via POST JSON, calls Groq, returns structured overview.
 * this is for the AI overview for the articles
 */
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
    exit;
}

$body      = json_decode(file_get_contents('php://input'), true);
$articleId = (int)($body['article_id'] ?? 0);

if (!$articleId) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing article_id.']);
    exit;
}

// fetch article — any published article can do the AI summary
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

$apiKey = GROQ_API_KEY;  // Change from GEMINI_API_KEY to use Groq, cos its free
if (!$apiKey) {
    http_response_code(500);
    echo json_encode(['error' => 'Groq API key not configured.']);
    exit;
}

//this is for the ai overview, can change this if need be.
//maybe can make it so that admin can edit this in the page? idk
$prompt = <<<PROMPT
You are an editorial assistant for SharedSpace, a news platform.
Give the reader a quick, helpful AI overview of the following article.
 
Title: {$article['title']}
Category: {$article['category_name']}
Author: {$article['author_name']}
Excerpt: {$article['excerpt']}
 
Respond ONLY with valid JSON, no markdown, no code fences:
{
  "summary": "<3-4 sentence plain-language summary>",
  "key_points": ["<point 1>", "<point 2>", "<point 3>"],
  "tone": "<e.g. Informative / Opinion / Investigative>",
  "read_time": "<estimated read time, e.g. 3 min read>"
}
PROMPT;

// new groq shit, replaces old gemini shit
$url = 'https://api.groq.com/openai/v1/chat/completions';

$payload = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'user', 'content' => $prompt]
    ],
    'temperature' => 0.3,
    'max_tokens' => 1000,
];

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ],
    CURLOPT_TIMEOUT => 30,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);


if (!$response || $httpCode !== 200) {
    error_log('Groq API Response: ' . $response);
    error_log('Groq API Code: ' . $httpCode);
    http_response_code(500);
    echo json_encode(['error' => 'Groq API error. Code: ' . $httpCode, 'details' => json_decode($response, true)]);
    exit;
}

if (!$response || $httpCode !== 200) {
    http_response_code(500);
    echo json_encode(['error' => 'Groq API error. Code: ' . $httpCode]);
    exit;
}

// Parse response (Groq returns OpenAI format)
$data = json_decode($response, true);
$rawText = $data['choices'][0]['message']['content'] ?? '';
$rawText = trim(preg_replace('/```json|```/i', '', $rawText));
$result = json_decode($rawText, true);

if (!$result || !isset($result['summary'])) {
    http_response_code(500);
    echo json_encode(['error' => 'AI did not return a valid response. Try again.']);
    exit;
}

echo json_encode($result);
