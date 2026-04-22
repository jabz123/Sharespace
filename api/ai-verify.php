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
            'claim_key' => substr(sha1($text . '|' . $status), 0, 12),
        ];
    }

    return array_slice($normalized, 0, 8);
}

function detectSourceType(?string $url): string {
    $value = strtolower(trim((string)$url));
    if ($value === '') {
        return 'article';
    }
    if (str_contains($value, '/commentary/')) {
        return 'commentary';
    }
    if (str_contains($value, '/opinion/')) {
        return 'opinion';
    }
    if (str_contains($value, '/analysis/')) {
        return 'analysis';
    }
    return 'article';
}

function normalizeMatchedSources(array $decoded): array {
    $sources = is_array($decoded['matched_sources'] ?? null) ? $decoded['matched_sources'] : [];
    $normalized = [];

    foreach ($sources as $source) {
        if (!is_array($source)) {
            continue;
        }

        $url = trim((string)($source['url'] ?? ''));
        $title = trim((string)($source['title'] ?? $source['name'] ?? ''));
        if ($url === '' && $title === '') {
            continue;
        }

        $normalized[] = [
            'name' => trim((string)($source['name'] ?? '')),
            'title' => $title,
            'url' => $url !== '' ? $url : null,
            'match_type' => trim((string)($source['match_type'] ?? 'related')),
            'relevance_note' => trim((string)($source['relevance_note'] ?? '')),
            'snippet' => trim((string)($source['snippet'] ?? '')),
            'source_type' => detectSourceType($url),
        ];
    }

    return array_slice($normalized, 0, 6);
}

function buildClaimSummary(array $claims): array {
    $summary = [
        'supported' => 0,
        'weak' => 0,
        'contradicted' => 0,
        'total' => 0,
    ];

    foreach ($claims as $claim) {
        if (!is_array($claim)) {
            continue;
        }

        $status = trim((string)($claim['status'] ?? 'supported'));
        if (!isset($summary[$status])) {
            $status = 'supported';
        }

        $summary[$status]++;
        $summary['total']++;
    }

    return $summary;
}

function buildWhyNotPerfect(array $rubricMetrics, array $metrics, array $flags, array $claims, array $matchedSources): array {
    $reasons = [];
    $seen = [];
    $maxima = [
        'factual_accuracy' => 45,
        'source_quality' => 25,
        'bias_detection' => 10,
        'logical_consistency' => 10,
        'completeness' => 10,
    ];

    $push = static function (string $message) use (&$reasons, &$seen): void {
        $message = trim($message);
        if ($message === '' || isset($seen[$message])) {
            return;
        }
        $seen[$message] = true;
        $reasons[] = $message;
    };

    $weakClaims = count(array_filter($claims, static fn($claim): bool => is_array($claim) && ($claim['status'] ?? '') === 'weak'));
    $contradictedClaims = count(array_filter($claims, static fn($claim): bool => is_array($claim) && ($claim['status'] ?? '') === 'contradicted'));
    $hasCommentaryEvidence = count(array_filter($matchedSources, static fn($source): bool => is_array($source) && in_array(($source['source_type'] ?? 'article'), ['commentary', 'opinion', 'analysis'], true))) > 0;

    if ($contradictedClaims > 0) {
        $push('Contradicted claim(s) still lowered the score.');
    }
    if ($weakClaims > 0) {
        $push($weakClaims . ' claim(s) are only partially supported by CNA/ST.');
    }
    if (($maxima['source_quality'] - (int)($rubricMetrics['source_quality'] ?? 0)) > 0) {
        $push('Source quality is not at the maximum, so the evidence could still be stronger or more directly corroborated.');
    }
    if (($maxima['completeness'] - (int)($rubricMetrics['completeness'] ?? 0)) > 0 || in_array('missing_context', $flags, true)) {
        $push('Some context or reporting detail is still missing.');
    }
    if (($maxima['bias_detection'] - (int)($rubricMetrics['bias_detection'] ?? 0)) > 0 || in_array('high_bias', $flags, true)) {
        $push('Some wording is still interpretive or less neutral than ideal.');
    }
    if (($maxima['logical_consistency'] - (int)($rubricMetrics['logical_consistency'] ?? 0)) > 0) {
        $push('The article can still be clearer in how it connects evidence to conclusions.');
    }
    if (($maxima['factual_accuracy'] - (int)($rubricMetrics['factual_accuracy'] ?? 0)) > 0) {
        $push('Not every important claim is fully or exactly confirmed by the matched CNA/ST sources.');
    }
    if ($hasCommentaryEvidence) {
        $push('Some supporting evidence is commentary or analysis rather than straight news reporting.');
    }
    if (in_array('low_information', $flags, true) || in_array('vague_claims', $flags, true)) {
        $push('Some parts of the draft remain too vague to verify precisely.');
    }

    if (empty($reasons) && array_sum($metrics) < 500) {
        $push('The draft is strong overall, but it still loses a few points because the evidence and reporting detail are not completely perfect.');
    }

    return array_slice($reasons, 0, 5);
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
        'rubric_metrics' => is_array($existingVerification['rubric_metrics'] ?? null) ? $existingVerification['rubric_metrics'] : [
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
        'claim_summary' => is_array($existingVerification['claim_summary'] ?? null)
            ? $existingVerification['claim_summary']
            : ['supported' => 0, 'weak' => 0, 'contradicted' => 0, 'total' => 0],
        'matched_sources' => is_array($existingVerification['matched_sources'] ?? null)
            ? $existingVerification['matched_sources']
            : [],
        'why_not_perfect' => is_array($existingVerification['why_not_perfect'] ?? null)
            ? $existingVerification['why_not_perfect']
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
$rawRubricMetrics = is_array($decoded['rubric_metrics'] ?? null) ? $decoded['rubric_metrics'] : [];
$normalizedRubricMetrics = [
    'factual_accuracy' => max(0, min(45, (int)($rawRubricMetrics['A_factual_accuracy'] ?? round(($normalizedMetrics['factual_accuracy'] / 100) * 45)))),
    'source_quality' => max(0, min(25, (int)($rawRubricMetrics['S_source_quality'] ?? round(($normalizedMetrics['source_quality'] / 100) * 25)))),
    'bias_detection' => max(0, min(10, (int)($rawRubricMetrics['B_bias'] ?? round(($normalizedMetrics['bias_detection'] / 100) * 10)))),
    'logical_consistency' => max(0, min(10, (int)($rawRubricMetrics['L_logic'] ?? round(($normalizedMetrics['logical_consistency'] / 100) * 10)))),
    'completeness' => max(0, min(10, (int)($rawRubricMetrics['C_completeness'] ?? round(($normalizedMetrics['completeness'] / 100) * 10)))),
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
$claimSummary = buildClaimSummary($claims);
$matchedSources = normalizeMatchedSources($decoded);
$whyNotPerfect = buildWhyNotPerfect(
    $normalizedRubricMetrics,
    $normalizedMetrics,
    is_array($decoded['flags'] ?? null) ? $decoded['flags'] : [],
    $claims,
    $matchedSources
);

$_SESSION['article_ai_verification'] = [
    'fingerprint' => $fingerprint,
    'trust_score' => $trustScore,
    'passed' => $publishDecision === 'auto_publish' && $trustScore >= 81,
    'publish_decision' => $publishDecision,
    'summary' => $summary,
    'verdict' => $verdict,
    'metrics' => $normalizedMetrics,
    'rubric_metrics' => $normalizedRubricMetrics,
    'source_url' => $sourceUrl,
    'source_label' => trim((string)($decoded['source_label'] ?? '')),
    'misinformation_highlights' => $misinformationHighlights,
    'improvement_suggestions' => $improvementSuggestions,
    'claims' => $claims,
    'claim_summary' => $claimSummary,
    'matched_sources' => $matchedSources,
    'why_not_perfect' => $whyNotPerfect,
];

echo json_encode([
    'trust_score' => $trustScore,
    'publish_decision' => $publishDecision,
    'summary' => $summary,
    'verdict' => $verdict,
    'metrics' => $normalizedMetrics,
    'rubric_metrics' => $normalizedRubricMetrics,
    'source_url' => $sourceUrl,
    'source_label' => trim((string)($decoded['source_label'] ?? '')),
    'misinformation_highlights' => $misinformationHighlights,
    'improvement_suggestions' => $improvementSuggestions,
    'claims' => $claims,
    'claim_summary' => $claimSummary,
    'matched_sources' => $matchedSources,
    'why_not_perfect' => $whyNotPerfect,
    'cached_result' => false,
]);
