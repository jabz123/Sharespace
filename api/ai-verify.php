<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

header('Content-Type: application/json');

function buildArticleVerificationFingerprint(array $input, int $userId): string {
    $normalize = static function ($value): string {
        return trim(str_replace(["\r\n", "\r"], "\n", (string)$value));
    };

    $payload = [
        'user_id' => $userId,
        'title' => $normalize($input['title'] ?? ''),
        'excerpt' => $normalize($input['excerpt'] ?? ''),
        'content' => $normalize($input['content'] ?? ''),
        'category_id' => (int)($input['category_id'] ?? 0),
        'source_url' => $normalize($input['source_url'] ?? ''),
    ];

    return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

function buildImprovementSuggestions(array $decoded, string $sourceUrl, array $metrics, int $trustScore, string $publishDecision): array {
    $suggestions = [];
    $seen = [];

    $push = static function (string $message) use (&$suggestions, &$seen): void {
        $normalized = trim($message);
        if ($normalized === '' || isset($seen[$normalized])) {
            return;
        }

        $seen[$normalized] = true;
        $suggestions[] = $normalized;
    };

    $flags = is_array($decoded['flags'] ?? null) ? $decoded['flags'] : [];
    $claims = is_array($decoded['claims'] ?? null) ? $decoded['claims'] : [];
    $referenceValid = !empty($decoded['reference_valid']);

    if ($sourceUrl === '') {
        $push('Add an exact CNA or ST article URL in the Reference Link field so the checker has a strong evidence anchor.');
    } elseif (!$referenceValid) {
        $push('Replace the current reference link with an exact CNA or ST article URL. Homepages and section pages do not count as trusted article evidence.');
    }

    if (($metrics['source_quality'] ?? 0) < 70 || in_array('no_trusted_match', $flags, true)) {
        $push('Strengthen the sourcing by matching the draft more closely to CNA or ST coverage and using the exact article link.');
    }

    if (($metrics['factual_accuracy'] ?? 0) < 75) {
        $push('Tighten factual claims by adding dates, locations, names, and figures that directly match CNA or ST reporting.');
    }

    if (($metrics['completeness'] ?? 0) < 75 || in_array('missing_context', $flags, true)) {
        $push('Add missing context such as who said what, when it happened, where it happened, and what was officially announced.');
    }

    if (($metrics['bias_detection'] ?? 0) < 75 || in_array('high_bias', $flags, true)) {
        $push('Use more neutral phrasing and avoid loaded or interpretive wording unless you attribute it to a named source.');
    }

    if (in_array('low_information', $flags, true) || in_array('vague_claims', $flags, true)) {
        $push('Make the article more specific. Replace vague summaries with verifiable statements tied to named people, places, dates, or actions.');
    }

    $weakClaims = array_values(array_filter($claims, static function ($claim): bool {
        return is_array($claim) && (float)($claim['match_score'] ?? 1) <= 0.5;
    }));
    if (!empty($weakClaims)) {
        $example = trim((string)($weakClaims[0]['text'] ?? ''));
        $push(
            $example !== ''
                ? 'Rewrite or attribute weakly supported claims such as: "' . $example . '".'
                : 'Rewrite or attribute claims that are only partially supported by CNA or ST.'
        );
    }

    if ($publishDecision === 'needs_review' && $trustScore < 81) {
        $push('Aim to lift the trust score above 81 by grounding each key sentence in an exact CNA or ST article and removing broad unsupported conclusions.');
    }

    if ($publishDecision === 'unreliable') {
        $push('Review the highlighted misinformation or unsupported lines first, then rerun AI Fact Check after revising them.');
    }

    if (empty($suggestions)) {
        $push('Keep the draft tightly aligned with CNA or ST wording and add a precise reference article link to maximise the trust score.');
    }

    return array_slice($suggestions, 0, 5);
}

function normalizeClaims(array $decoded): array {
    $claims = is_array($decoded['claims'] ?? null) ? $decoded['claims'] : [];
    $normalized = [];

    foreach ($claims as $claim) {
        if (!is_array($claim)) {
            continue;
        }

        $text = trim((string)($claim['text'] ?? ''));
        if ($text === '') {
            continue;
        }

        $matchScore = max(0, min(1, (float)($claim['match_score'] ?? 0)));
        $confidence = max(0, min(1, (float)($claim['confidence'] ?? 0)));

        $status = 'supported';
        if ($matchScore <= 0.2) {
            $status = 'contradicted';
        } elseif ($matchScore <= 0.5) {
            $status = 'weak';
        }

        $normalized[] = [
            'text' => $text,
            'subject' => ($claim['subject'] ?? null) !== null ? trim((string)$claim['subject']) : null,
            'value' => ($claim['value'] ?? null) !== null ? trim((string)$claim['value']) : null,
            'time' => ($claim['time'] ?? null) !== null ? trim((string)$claim['time']) : null,
            'match_score' => $matchScore,
            'confidence' => $confidence,
            'source' => ($claim['source'] ?? null) !== null ? trim((string)$claim['source']) : null,
            'reason' => trim((string)($claim['reason'] ?? '')),
            'importance' => trim((string)($claim['importance'] ?? 'minor')),
            'status' => $status,
        ];
    }

    return array_slice($normalized, 0, 8);
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

$fingerprint = buildArticleVerificationFingerprint([
    'title' => $title,
    'excerpt' => $excerpt,
    'content' => $content,
    'category_id' => $categoryId,
    'source_url' => $sourceUrl,
], (int)$user->id);

$existingVerification = $_SESSION['article_ai_verification'] ?? null;
$hasCachedVerification = is_array($existingVerification)
    && ($existingVerification['fingerprint'] ?? '') === $fingerprint;

if ($hasCachedVerification) {
    echo json_encode([
        'trust_score' => max(0, min(100, (int)($existingVerification['trust_score'] ?? 0))),
        'publish_decision' => trim((string)($existingVerification['publish_decision'] ?? '')),
        'summary' => trim((string)($existingVerification['summary'] ?? 'Verification reused from the latest unchanged draft.')),
        'verdict' => trim((string)($existingVerification['verdict'] ?? 'Verification reused from the latest unchanged draft.')),
        'metrics' => is_array($existingVerification['metrics'] ?? null) ? $existingVerification['metrics'] : [
            'factual_accuracy' => 0,
            'source_quality' => 0,
            'bias_detection' => 0,
            'logical_consistency' => 0,
            'completeness' => 0,
        ],
        'source_url' => $sourceUrl,
        'source_label' => trim((string)($existingVerification['source_label'] ?? '')),
        'misinformation_highlights' => is_array($existingVerification['misinformation_highlights'] ?? null)
            ? $existingVerification['misinformation_highlights']
            : [],
        'improvement_suggestions' => is_array($existingVerification['improvement_suggestions'] ?? null)
            ? $existingVerification['improvement_suggestions']
            : [],
        'claims' => is_array($existingVerification['claims'] ?? null)
            ? $existingVerification['claims']
            : [],
        'cached_result' => true,
    ]);
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
    CURLOPT_TIMEOUT => 90,
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
        'webhook_url' => $webhookUrl,
    ]);
    exit;
}

if ($httpCode >= 400) {
    http_response_code(502);
    echo json_encode([
        'error' => $decoded['error'] ?? 'n8n verification failed.',
        'details' => $decoded,
        'status_code' => $httpCode,
        'webhook_url' => $webhookUrl,
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

$publishDecision = trim((string)($decoded['publish_decision'] ?? ''));
if ($publishDecision === '') {
    if ($trustScore >= 81) {
        $publishDecision = 'auto_publish';
    } elseif ($trustScore >= 60) {
        $publishDecision = 'needs_review';
    } else {
        $publishDecision = 'unreliable';
    }
}

$verdict = trim((string)($decoded['verdict'] ?? ''));
if ($verdict === '') {
    $verdict = $publishDecision === 'auto_publish'
        ? 'Reliable. Auto publish approved because the CNA/ST evidence is strong enough for direct publication.'
        : ($publishDecision === 'needs_review'
            ? 'Needs Review. Do not publish yet. The draft needs manual revision or stronger evidence before it can move forward.'
            : 'Unreliable. Do not publish. The draft contains unsupported, false, or insufficiently verified information.');
}

$summary = trim((string)($decoded['summary'] ?? ''));
if ($summary === '') {
    $summary = 'AI verification completed successfully.';
}

$misinformationHighlights = [];
if (is_array($decoded['misinformation_highlights'] ?? null)) {
    foreach ($decoded['misinformation_highlights'] as $item) {
        if (!is_array($item)) {
            continue;
        }

        $reason = trim((string)($item['reason'] ?? ''));
        $line = trim((string)($item['line'] ?? ''));
        $source = trim((string)($item['source'] ?? ''));

        if ($reason === '' && $line === '') {
            continue;
        }

        $misinformationHighlights[] = [
            'line' => $line !== '' ? $line : null,
            'reason' => $reason !== '' ? $reason : 'Unsupported or contradicted by trusted CNA/ST evidence.',
            'source' => $source !== '' ? $source : null,
        ];
    }
}

$improvementSuggestions = buildImprovementSuggestions(
    $decoded,
    $sourceUrl,
    $normalizedMetrics,
    $trustScore,
    $publishDecision
);
$claims = normalizeClaims($decoded);

$_SESSION['article_ai_verification'] = [
    'fingerprint' => $fingerprint,
    'trust_score' => $trustScore,
    'passed' => $publishDecision === 'auto_publish' && $trustScore >= 81,
    'publish_decision' => $publishDecision,
    'summary' => $summary,
    'verdict' => $verdict,
    'metrics' => $normalizedMetrics,
    'source_url' => $sourceUrl,
    'source_label' => trim((string)($decoded['source_label'] ?? '')),
    'misinformation_highlights' => $misinformationHighlights,
    'improvement_suggestions' => $improvementSuggestions,
    'claims' => $claims,
];

echo json_encode([
    'trust_score' => $trustScore,
    'publish_decision' => $publishDecision,
    'summary' => $summary,
    'verdict' => $verdict,
    'metrics' => $normalizedMetrics,
    'source_url' => $sourceUrl,
    'source_label' => trim((string)($decoded['source_label'] ?? '')),
    'misinformation_highlights' => $misinformationHighlights,
    'improvement_suggestions' => $improvementSuggestions,
    'claims' => $claims,
    'cached_result' => false,
]);
