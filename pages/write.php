<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

function pageRedirect(string $url, ?string $error = null, ?string $success = null): never {
    if ($error) {
        flash_set('flash_error', $error);
    }
    if ($success) {
        flash_set('flash_success', $success);
    }

    $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8">';
    echo '<meta http-equiv="refresh" content="0;url=' . $escapedUrl . '">';
    echo '<title>Redirecting...</title></head><body>';
    echo '<script>window.top.location.href = ' . json_encode($url) . ';</script>';
    echo '<p>Redirecting... If nothing happens, <a href="' . $escapedUrl . '">continue here</a>.</p>';
    echo '</body></html>';
    exit;
}

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

function resolveStoredArticleVerification(?Article $article, string $fingerprint): ?array {
    if (!$article) {
        return null;
    }

    if (($article->verificationFingerprint ?? '') !== $fingerprint) {
        return null;
    }

    $rawPayload = $article->verificationPayload ?? null;
    if (!is_string($rawPayload) || trim($rawPayload) === '') {
        return null;
    }

    $decoded = json_decode($rawPayload, true);
    return is_array($decoded) ? $decoded : null;
}

function persistArticleVerification(int $articleId, int $authorId, array $verification): void {
    if ($articleId <= 0 || empty($verification['fingerprint'])) {
        return;
    }

    DB::execute(
        'UPDATE articles
         SET verification_fingerprint = ?, verification_payload = ?, verification_checked_at = NOW()
         WHERE id = ? AND author_id = ?',
        [
            (string)$verification['fingerprint'],
            json_encode($verification, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            $articleId,
            $authorId,
        ]
    );
}

$auth = new AuthController();
$articleCtrl = new ArticleController();
$autoPublishTrustScore = 81;
$needsReviewTrustScore = 60;

$auth->requireAuth();
$user = $auth->currentUser();
$canUploadImage = ($user->role === 'premium' || $user->role === 'category_admin');

$categories = $articleCtrl->getAllCategories();

$editId = (int)($_GET['id'] ?? 0);
$article = null;
$isEdit = false;

if ($editId) {
    $article = $articleCtrl->getByIdForAuthor($editId, $user->id);
    if (!$article || $article->authorId !== $user->id) {
        pageRedirect('/pages/my-articles.php', 'Article not found or permission denied.');
    }
    $isEdit = true;
}

$reviewNoticeToShow = null;
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $isEdit && $article && $article->reviewNoticePending && !empty($article->reviewNotice)) {
    $reviewNoticeToShow = $article->reviewNotice;
    $articleCtrl->clearReviewNotice($editId, $user->id);
    $article->reviewNoticePending = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'publish';
    $imagePath = $article->imagePath ?? null;
    $requestFingerprint = buildArticleVerificationFingerprint($_POST, (int)$user->id);
    $requestVerification = $_SESSION['article_ai_verification'] ?? null;
    $hasRequestVerification = is_array($requestVerification)
        && (($requestVerification['fingerprint'] ?? '') === $requestFingerprint);

    if (isset($_POST['remove_image']) && $_POST['remove_image'] == '1') {
        if (!empty($article->imagePath)) {
            $filePath = __DIR__ . '/../public/' . $article->imagePath;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        $imagePath = null;
    }

    if ($canUploadImage && isset($_FILES['article_image']) && $_FILES['article_image']['error'] === 0) {
        $uploadDir = __DIR__ . '/../public/uploads/articles/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['article_image']['name']);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['article_image']['tmp_name'], $targetPath)) {
            $imagePath = 'uploads/articles/' . $fileName;
        }
    }

    $_POST['image_path'] = $imagePath;

    if ($action === 'draft') {
        $_POST['status'] = 'draft';

        if ($isEdit) {
            $result = $articleCtrl->update($editId, $user->id, $_POST);
        } else {
            $result = $articleCtrl->saveDraft($user->id, $_POST);
        }

        if (isset($result['ok'])) {
            $savedArticleId = $isEdit ? $editId : (int)($result['id'] ?? 0);
            if ($hasRequestVerification && $savedArticleId > 0) {
                persistArticleVerification($savedArticleId, (int)$user->id, $requestVerification);
            }
            pageRedirect('/pages/my-articles.php', null, 'Draft saved!');
        }
    } else {
        $fingerprint = buildArticleVerificationFingerprint($_POST, (int)$user->id);
        $verification = $_SESSION['article_ai_verification'] ?? null;
        $storedVerification = $isEdit ? resolveStoredArticleVerification($article, $fingerprint) : null;
        if (is_array($storedVerification) && (($verification['fingerprint'] ?? '') !== $fingerprint)) {
            $verification = $storedVerification;
            $_SESSION['article_ai_verification'] = $storedVerification;
        }
        $isVerificationCurrent = is_array($verification)
            && ($verification['fingerprint'] ?? '') === $fingerprint;
        $verifiedTrustScore = $isVerificationCurrent ? (int)($verification['trust_score'] ?? 0) : 0;
        $verifiedDecision = $isVerificationCurrent ? trim((string)($verification['publish_decision'] ?? '')) : '';
        $hasPassingVerification = $isVerificationCurrent
            && !empty($verification['passed'])
            && $verifiedTrustScore >= $autoPublishTrustScore
            && $verifiedDecision === 'auto_publish';

        if (!$hasPassingVerification) {
            $result = ['error' => 'Run AI Fact Check and get an Auto Publish result at 81% or above before publishing this article.'];
            $_POST['trust_score'] = $verifiedTrustScore;
        } else {
            $_POST['status'] = 'published';
            $_POST['trust_score'] = $verifiedTrustScore;

            if ($isEdit) {
                $result = $articleCtrl->update($editId, $user->id, $_POST);
                if (isset($result['ok'])) {
                    unset($_SESSION['article_ai_verification']);
                    pageRedirect('/pages/my-articles.php', null, 'Article published successfully.');
                }
            } else {
                $result = $articleCtrl->publish($user->id, $_POST);
                if (isset($result['ok'])) {
                    unset($_SESSION['article_ai_verification']);
                    pageRedirect('/pages/my-articles.php', null, 'Article published successfully.');
                }
            }
        }
    }

    flash_set('flash_error', $result['error']);
}

$currentVerificationInput = [
    'title' => $_POST['title'] ?? ($article?->title ?? ''),
    'excerpt' => $_POST['excerpt'] ?? ($article?->excerpt ?? ''),
    'content' => $_POST['content'] ?? ($article?->content ?? ''),
    'category_id' => $_POST['category_id'] ?? ($article?->categoryId ?? 0),
    'source_url' => $_POST['source_url'] ?? ($article?->sourceUrl ?? ''),
];
$verification = $_SESSION['article_ai_verification'] ?? null;
$verificationFingerprint = buildArticleVerificationFingerprint($currentVerificationInput, (int)$user->id);
$storedVerification = $isEdit ? resolveStoredArticleVerification($article, $verificationFingerprint) : null;
if (is_array($storedVerification) && (($verification['fingerprint'] ?? '') !== $verificationFingerprint)) {
    $verification = $storedVerification;
    $_SESSION['article_ai_verification'] = $storedVerification;
}
$hasCurrentVerification = is_array($verification)
    && ($verification['fingerprint'] ?? '') === $verificationFingerprint;
$verificationPassed = $hasCurrentVerification
    && !empty($verification['passed'])
    && (int)($verification['trust_score'] ?? 0) >= $autoPublishTrustScore
    && trim((string)($verification['publish_decision'] ?? '')) === 'auto_publish';
$initialDecision = $hasCurrentVerification
    ? trim((string)($verification['publish_decision'] ?? ''))
    : '';
$initialTrustScore = $hasCurrentVerification
    ? (int)($verification['trust_score'] ?? 0)
    : (int)($_POST['trust_score'] ?? 0);
$initialSummary = $hasCurrentVerification
    ? trim((string)($verification['summary'] ?? ''))
    : '';
$initialVerdict = $hasCurrentVerification
    ? trim((string)($verification['verdict'] ?? ''))
    : '';
$initialMetrics = $hasCurrentVerification && is_array($verification['metrics'] ?? null)
    ? $verification['metrics']
    : [
        'factual_accuracy' => 0,
        'source_quality' => 0,
        'bias_detection' => 0,
        'logical_consistency' => 0,
        'completeness' => 0,
    ];
$initialRubricMetrics = $hasCurrentVerification && is_array($verification['rubric_metrics'] ?? null)
    ? $verification['rubric_metrics']
    : [
        'factual_accuracy' => 0,
        'source_quality' => 0,
        'bias_detection' => 0,
        'logical_consistency' => 0,
        'completeness' => 0,
    ];
$initialSourceLabel = $hasCurrentVerification
    ? trim((string)($verification['source_label'] ?? ''))
    : '';
$initialHighlights = $hasCurrentVerification && is_array($verification['misinformation_highlights'] ?? null)
    ? $verification['misinformation_highlights']
    : [];
$initialSuggestions = $hasCurrentVerification && is_array($verification['improvement_suggestions'] ?? null)
    ? $verification['improvement_suggestions']
    : [];
$initialClaims = $hasCurrentVerification && is_array($verification['claims'] ?? null)
    ? $verification['claims']
    : [];
$initialClaimSummary = $hasCurrentVerification && is_array($verification['claim_summary'] ?? null)
    ? $verification['claim_summary']
    : ['supported' => 0, 'weak' => 0, 'contradicted' => 0, 'total' => 0];
$initialMatchedSources = $hasCurrentVerification && is_array($verification['matched_sources'] ?? null)
    ? $verification['matched_sources']
    : [];
$initialWhyNotPerfect = $hasCurrentVerification && is_array($verification['why_not_perfect'] ?? null)
    ? $verification['why_not_perfect']
    : [];
$initialWhyNotPerfectDetails = $hasCurrentVerification && is_array($verification['why_not_perfect_details'] ?? null)
    ? $verification['why_not_perfect_details']
    : [];

$val = [
    'title' => $_POST['title'] ?? ($article?->title ?? ''),
    'excerpt' => $_POST['excerpt'] ?? ($article?->excerpt ?? ''),
    'content' => $_POST['content'] ?? ($article?->content ?? ''),
    'category_id' => $_POST['category_id'] ?? ($article?->categoryId ?? 0),
    'source_url' => $_POST['source_url'] ?? ($article?->sourceUrl ?? ''),
    'trust_score' => $initialTrustScore,
];

//determines whether article is to be edited or written 
page_head($isEdit ? 'Edit Article' : 'Write Article');
?>

<div class="dashboard-layout user-dashboard-shell">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header(
            $isEdit ? 'Edit Article' : 'Write Article',
            $isEdit ? 'Update your article' : 'Share your story with the world'
        ); ?>
        <?php flash_messages(); ?>
        <?php if ($reviewNoticeToShow): ?>
            <div class="alert alert-error" style="margin:18px 28px 0;">
                <?= htmlspecialchars($reviewNoticeToShow) ?>
            </div>
        <?php endif; ?>

        <div class="page-content write-layout write-editor-shell">
            <form method="POST" action="/pages/write.php<?= $isEdit ? '?id=' . (int)$editId : '' ?>" target="_self" id="write-form" enctype="multipart/form-data" class="write-editor-form">
                <input type="hidden" name="remove_image" id="removeImageFlag" value="0">
                <input type="hidden" name="trust_score" id="trustScoreInput" value="<?= (int)$val['trust_score'] ?>">

                <?php if ($canUploadImage): ?>
                <div class="image-upload-container">
                    <div class="image-preview" id="imagePreview">
                    <?php if ($isEdit && !empty($article->imagePath)): ?>
                        <img src="/public/<?= htmlspecialchars($article->imagePath) ?>" alt="">
                    <?php else: ?>
                        <span>No image selected</span>
                    <?php endif; ?>
                    </div>

                    <input type="file" id="articleImageInput" name="article_image" hidden>

                    <div class="image-buttons">
                        <button type="button" class="btn btn-dark" onclick="selectImage()">Select Image</button>
                        <button type="button" class="btn btn-light" onclick="removeImage()">Remove Image</button>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Article Title</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($val['title']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Article Summary</label>
                    <input type="text" name="excerpt" value="<?= htmlspecialchars($val['excerpt']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">Select category</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= (int)$val['category_id'] === $cat->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Reference Link (Optional)</label>
                    <input
                        type="url"
                        name="source_url"
                        id="sourceUrlInput"
                        value="<?= htmlspecialchars($val['source_url']) ?>"
                        placeholder="https://www.straitstimes.com/"
                    >
                </div>

                <div class="form-group">
                    <label>Content</label>
                    <textarea name="content" class="write-content-input" required><?= htmlspecialchars($val['content']) ?></textarea>
                </div>

                <div class="write-actions">
                    <button type="button" onclick="runAICheck()" class="btn-ai">AI Fact Check</button>
                    <div class="write-actions-row">
                        <?php if (!$isEdit || ($article->status ?? '') === 'draft'): ?>
                            <button type="submit" name="action" value="draft" class="btn-draft">
                                <?= $isEdit ? 'Update Draft' : 'Save Draft' ?>
                            </button>
                        <?php endif; ?>

                        <button
                            type="submit"
                            name="action"
                            value="publish"
                            class="btn-publish<?= $verificationPassed ? '' : ' is-locked' ?>"
                            id="publishButton"
                            <?= $verificationPassed ? '' : 'disabled' ?>
                        >
                            <?php
                            $isDraft = !$isEdit || ($article->status === 'draft');
                            echo $isDraft ? 'Publish Article' : 'Save Changes';
                            ?>
                        </button>
                    </div>
                    <p class="publish-status text-muted" id="publishStatus">
                        <?php
                        if ($verificationPassed) {
                            echo 'AI verification passed. Publishing is unlocked for direct publication.';
                        } elseif ($initialDecision === 'needs_review') {
                            echo 'Needs Review. This draft will not publish yet. Strengthen the evidence and rerun AI Fact Check.';
                        } elseif ($initialDecision === 'unreliable') {
                            echo 'Unreliable. Fix the highlighted misinformation or unsupported claims before trying again.';
                        } else {
                            echo 'Run AI Fact Check. Only Auto Publish results at 81% or above unlock publishing.';
                        }
                        ?>
                    </p>
                </div>
            </form>

            <div class="ai-panel">
                <div class="card ai-verification-card">
                    <div class="ai-results-head">
                        <div>
                            <h3>AI Verification Results</h3>
                            <p class="ai-results-subtitle">Comprehensive analysis of this article's factual reliability.</p>
                        </div>
                        <p id="aiLastChecked" class="ai-last-checked"><?= $hasCurrentVerification ? 'Last checked: Just now' : 'Waiting for fact check' ?></p>
                    </div>

                    <div id="ai-empty" class="ai-state-card" style="display:<?= $hasCurrentVerification ? 'none' : 'block' ?>;">
                        <p>Click "AI Fact Check" to analyze your article's credibility before publishing.</p>
                        <p class="text-muted">Add an exact CNA or ST article URL if you have one. 81%+ unlocks publish, 60-80% stays in Needs Review, and anything below 60% is Unreliable.</p>
                    </div>

                    <div id="ai-loading" class="ai-state-card" style="display:none;">
                        <p>AI Fact Checking...</p>
                        <p class="text-muted">Checking claim support, source quality, bias, logic, and completeness.</p>
                    </div>

                    <div id="ai-error" style="display:none;">
                        <div class="alert alert-error" id="aiErrorMessage" style="margin-top:14px;"></div>
                    </div>

                    <div id="ai-result" class="ai-results-dashboard" style="display:<?= $hasCurrentVerification ? 'block' : 'none' ?>;">
                        <section class="ai-overview-card">
                            <div class="ai-score-ring" id="aiScoreRing" style="--score: <?= max(0, min(100, (int)$initialTrustScore)) ?>;">
                                <div class="ai-score-ring-inner">
                                    <span id="aiTrustScore"><?= (int)$initialTrustScore ?>%</span>
                                    <span>Trust Score</span>
                                </div>
                            </div>

                            <div class="ai-overview-copy">
                                <h4 id="aiVerdictHeadline"><?=
                                    htmlspecialchars(
                                        $initialDecision === 'auto_publish'
                                            ? 'Reliable'
                                            : ($initialDecision === 'needs_review'
                                                ? 'Needs Review'
                                                : ($initialDecision === 'unreliable' ? 'Unreliable' : 'Waiting for verification'))
                                    )
                                ?></h4>
                                <p id="aiSummary"><?= $initialSummary !== '' ? htmlspecialchars($initialSummary) : 'Run AI Fact Check to see a real verification summary from n8n.' ?></p>
                                <p id="aiSourceLabel" class="ai-source-label" style="display:<?= $initialSourceLabel !== '' ? 'block' : 'none' ?>;"><?= htmlspecialchars($initialSourceLabel) ?></p>

                                <div id="aiClaimSummaryBox" class="ai-pill-row" style="display:<?= $initialClaimSummary['total'] > 0 ? 'flex' : 'none' ?>;">
                                    <span id="aiClaimSupportedBadge" class="ai-pill ai-pill-supported"><?= (int)($initialClaimSummary['supported'] ?? 0) ?> Supported</span>
                                    <span id="aiClaimContradictedBadge" class="ai-pill ai-pill-contradicted"><?= (int)($initialClaimSummary['contradicted'] ?? 0) ?> Contradicted</span>
                                    <span id="aiClaimWeakBadge" class="ai-pill ai-pill-weak"><?= (int)($initialClaimSummary['weak'] ?? 0) ?> Needs Support</span>
                                </div>
                            </div>
                        </section>

                        <section id="aiBreakdownSection" class="ai-section" style="display:none;">
                            <div class="ai-section-head">
                                <h4>Breakdown</h4>
                            </div>
                            <div class="ai-breakdown-grid">
                                <div class="ai-stat-card ai-stat-supported">
                                    <strong id="aiSupportedCount"><?= (int)($initialClaimSummary['supported'] ?? 0) ?></strong>
                                    <span>Supported</span>
                                    <p>Claims backed by reliable sources</p>
                                </div>
                                <div class="ai-stat-card ai-stat-contradicted">
                                    <strong id="aiContradictedCount"><?= (int)($initialClaimSummary['contradicted'] ?? 0) ?></strong>
                                    <span>Contradicted</span>
                                    <p>Claims contradicted by credible sources</p>
                                </div>
                                <div class="ai-stat-card ai-stat-weak">
                                    <strong id="aiWeakCount"><?= (int)($initialClaimSummary['weak'] ?? 0) ?></strong>
                                    <span>Needs Support</span>
                                    <p>Claims need more evidence or context</p>
                                </div>
                                <div class="ai-stat-card ai-stat-total">
                                    <strong id="aiTotalClaimsCount"><?= (int)($initialClaimSummary['total'] ?? 0) ?></strong>
                                    <span>Total Claims</span>
                                    <p>AI identified factual claims in this article</p>
                                </div>
                            </div>
                        </section>

                        <section id="aiAreasBox" class="ai-section ai-areas-section" style="display:none;">
                            <div class="ai-section-head ai-areas-head">
                                <div>
                                    <h4>Areas to Improve</h4>
                                    <p id="aiAreasSubtitle">These issues are affecting your score. Addressing them can improve the accuracy and reliability of your article.</p>
                                </div>
                                <div class="ai-impact-card">
                                    <span class="ai-impact-label">Score Impact</span>
                                    <strong id="aiImpactValue">-0%</strong>
                                    <span class="ai-impact-note">from perfect score</span>
                                </div>
                            </div>
                            <div id="aiAreasSummary" class="ai-areas-summary"></div>
                            <div id="aiAreasGroups" class="ai-areas-groups"></div>
                            <div id="aiImproveGuideCard" class="ai-improve-guide-card" style="display:none;">
                                <div class="ai-improve-guide-copy">
                                    <span class="ai-improve-guide-kicker">How to improve your score</span>
                                    <p id="aiImproveGuideText">Use more direct quotes and data from trusted CNA/ST sources, add missing context, and ensure neutral, factual wording throughout the article.</p>
                                </div>
                                <button type="button" id="aiImproveGuideButton" class="ai-guide-button">View Guide</button>
                            </div>
                        </section>

                        <section id="aiSourcesBox" class="ai-section" style="display:<?= !empty($initialMatchedSources) ? 'block' : 'none' ?>;">
                            <details id="aiSourcesDisclosure" class="ai-sources-disclosure">
                                <summary class="ai-sources-summary">
                                    <span class="ai-sources-summary-text">
                                        <strong>Matched Sources</strong>
                                        <small id="aiSourcesCountLabel"><?= count($initialMatchedSources) ?> sources matched</small>
                                    </span>
                                    <span class="ai-sources-summary-icon" aria-hidden="true"></span>
                                </summary>
                                <div id="aiSourcesList" class="ai-sources-grid">
                                <?php foreach ($initialMatchedSources as $source): ?>
                                    <div class="ai-source-card">
                                        <div class="ai-source-topline">
                                            <span class="ai-source-name"><?= htmlspecialchars((string)($source['name'] ?? 'Trusted source')) ?></span>
                                            <span class="ai-source-match"><?= htmlspecialchars((string)($source['match_type'] ?? 'related')) ?></span>
                                        </div>
                                        <strong><?= htmlspecialchars((string)($source['title'] ?? $source['name'] ?? '')) ?></strong>
                                        <p class="text-muted" style="font-size:12px; margin:0 0 6px; line-height:1.5;">
                                            <?= htmlspecialchars((string)($source['name'] ?? '')) ?>
                                            <?php if (!empty($source['source_type'])): ?>
                                                · <?= htmlspecialchars((string)$source['source_type']) ?>
                                            <?php endif; ?>
                                        </p>
                                        <?php if (!empty($source['source_type'])): ?>
                                            <p class="ai-source-type"><?= htmlspecialchars((string)$source['source_type']) ?></p>
                                        <?php endif; ?>
                                        <?php if (!empty($source['url'])): ?>
                                            <a href="<?= htmlspecialchars((string)$source['url']) ?>" target="_blank" rel="noopener noreferrer" style="font-size:12px; word-break:break-all;">View source</a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>

                        <section id="aiClaimsBox" class="ai-section" style="display:none;">
                            <div class="ai-section-head">
                                <h4>Issues to Review</h4>
                            </div>
                            <div id="aiClaimsList" class="ai-issues-list">
                                <?php foreach ($initialClaims as $claim): ?>
                                    <?php
                                    $status = trim((string)($claim['status'] ?? 'supported'));
                                    $label = $status === 'contradicted' ? 'Contradicted' : ($status === 'weak' ? 'Needs Support' : 'Supported');
                                    $statusClass = $status === 'contradicted' ? 'is-contradicted' : ($status === 'weak' ? 'is-weak' : 'is-supported');
                                    $claimKey = (string)($claim['claim_key'] ?? substr(sha1((string)($claim['text'] ?? '') . '|' . (string)($claim['status'] ?? 'supported')), 0, 12));
                                    ?>
                                    <button
                                        type="button"
                                        class="ai-issue-card <?= $statusClass ?>"
                                        data-claim-key="<?= htmlspecialchars($claimKey) ?>"
                                        data-highlight-key="<?= htmlspecialchars((string)($claim['sentence_key'] ?? trim(preg_replace('/\s+/', ' ', (string)($claim['draft_sentence'] ?? ''))))) ?>"
                                    >
                                        <span class="ai-issue-badge"><?= $label ?></span>
                                        <span class="ai-issue-text"><?= htmlspecialchars((string)($claim['text'] ?? '')) ?></span>
                                        <span class="ai-issue-meta">
                                            <?php if (!empty($claim['reason'])): ?>
                                                <?= htmlspecialchars((string)$claim['reason']) ?>
                                            <?php else: ?>
                                                Match Score: <?= htmlspecialchars((string)round((float)($claim['match_score'] ?? 0), 2)) ?>
                                            <?php endif; ?>
                                        </span>
                                        <span class="ai-issue-action">Jump to sentence</span>
                                    </button>
                                <?php endforeach; ?>
                                </div>
                            </details>
                        </section>

                        <section id="aiImproveBox" class="ai-section" style="display:none;">
                            <div class="ai-section-head">
                                <h4>How To Improve</h4>
                            </div>
                            <ul id="aiImproveList" class="ai-guidance-list">
                                <?php foreach ($initialSuggestions as $item): ?>
                                    <li><?= htmlspecialchars((string)$item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>

                        <section id="aiMisinformationBox" class="ai-section" style="display:<?= !empty($initialHighlights) ? 'block' : 'none' ?>;">
                            <div class="ai-section-head">
                                <h4>Misinformation Highlights</h4>
                            </div>
                            <ul id="aiMisinformationList" class="ai-guidance-list ai-guidance-list-danger">
                                <?php foreach ($initialHighlights as $item): ?>
                                    <li>
                                        <?php if (!empty($item['line'])): ?>
                                            <strong><?= htmlspecialchars((string)$item['line']) ?></strong><br>
                                        <?php endif; ?>
                                        <?= htmlspecialchars((string)($item['reason'] ?? 'Unsupported or contradicted by trusted CNA/ST evidence.')) ?>
                                        <?php if (!empty($item['source'])): ?>
                                            <span class="text-muted">(<?= htmlspecialchars((string)$item['source']) ?>)</span>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </section>

                        <section id="aiMetricsBox" class="ai-section">
                            <div class="ai-section-head">
                                <h4>Metric Breakdown</h4>
                            </div>
                            <div class="ai-metric-list">
                                <div class="ai-metric-row">
                                    <div class="ai-metric-label-line"><span>Factual Accuracy</span><span id="metricFactualAccuracyPoints" class="text-muted"><?= (int)($initialRubricMetrics['factual_accuracy'] ?? 0) ?>/45</span></div>
                                    <div class="progress-bar"><div id="metricFactualAccuracy" style="width:<?= max(0, min(100, (int)($initialMetrics['factual_accuracy'] ?? 0))) ?>%"></div></div>
                                </div>
                                <div class="ai-metric-row">
                                    <div class="ai-metric-label-line"><span>Source Quality</span><span id="metricSourceQualityPoints" class="text-muted"><?= (int)($initialRubricMetrics['source_quality'] ?? 0) ?>/25</span></div>
                                    <div class="progress-bar"><div id="metricSourceQuality" style="width:<?= max(0, min(100, (int)($initialMetrics['source_quality'] ?? 0))) ?>%"></div></div>
                                </div>
                                <div class="ai-metric-row">
                                    <div class="ai-metric-label-line"><span>Bias Detection</span><span id="metricBiasDetectionPoints" class="text-muted"><?= (int)($initialRubricMetrics['bias_detection'] ?? 0) ?>/10</span></div>
                                    <div class="progress-bar"><div id="metricBiasDetection" style="width:<?= max(0, min(100, (int)($initialMetrics['bias_detection'] ?? 0))) ?>%"></div></div>
                                </div>
                                <div class="ai-metric-row">
                                    <div class="ai-metric-label-line"><span>Logical Consistency</span><span id="metricLogicalConsistencyPoints" class="text-muted"><?= (int)($initialRubricMetrics['logical_consistency'] ?? 0) ?>/10</span></div>
                                    <div class="progress-bar"><div id="metricLogicalConsistency" style="width:<?= max(0, min(100, (int)($initialMetrics['logical_consistency'] ?? 0))) ?>%"></div></div>
                                </div>
                                <div class="ai-metric-row">
                                    <div class="ai-metric-label-line"><span>Completeness</span><span id="metricCompletenessPoints" class="text-muted"><?= (int)($initialRubricMetrics['completeness'] ?? 0) ?>/10</span></div>
                                    <div class="progress-bar"><div id="metricCompleteness" style="width:<?= max(0, min(100, (int)($initialMetrics['completeness'] ?? 0))) ?>%"></div></div>
                                </div>
                            </div>
                        </section>

                        <section id="aiWhyNotPerfectBox" class="ai-section" style="display:none;">
                            <div class="ai-section-head">
                                <h4>Why Not 100?</h4>
                            </div>
                            <ul id="aiWhyNotPerfectList" class="ai-guidance-list">
                                <?php foreach ($initialWhyNotPerfectDetails as $item): ?>
                                    <?php $examples = is_array($item['examples'] ?? null) ? $item['examples'] : []; ?>
                                    <li class="ai-why-item">
                                        <span class="ai-why-message"><?= htmlspecialchars((string)($item['message'] ?? '')) ?></span>
                                        <?php if (!empty($examples)): ?>
                                            <div class="ai-why-examples">
                                                <?php foreach ($examples as $example): ?>
                                                    <?php
                                                    $highlightKey = trim((string)($example['highlight_key'] ?? ''));
                                                    $url = trim((string)($example['url'] ?? ''));
                                                    $tag = $highlightKey !== '' ? 'button' : ($url !== '' ? 'a' : 'div');
                                                    ?>
                                                    <<?= $tag ?>
                                                        class="ai-why-example<?= $highlightKey !== '' ? ' is-clickable' : '' ?>"
                                                        <?php if ($highlightKey !== ''): ?>
                                                            type="button"
                                                            data-highlight-key="<?= htmlspecialchars($highlightKey) ?>"
                                                        <?php elseif ($url !== ''): ?>
                                                            href="<?= htmlspecialchars($url) ?>"
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                        <?php endif; ?>
                                                    >
                                                        <span class="ai-why-example-label"><?= htmlspecialchars((string)($example['label'] ?? 'Example')) ?></span>
                                                        <strong><?= htmlspecialchars((string)($example['text'] ?? '')) ?></strong>
                                                        <?php if (!empty($example['reason'])): ?>
                                                            <span class="ai-why-example-reason"><?= htmlspecialchars((string)$example['reason']) ?></span>
                                                        <?php endif; ?>
                                                    </<?= $tag ?>>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                                <?php if (empty($initialWhyNotPerfectDetails)): ?>
                                    <?php foreach ($initialWhyNotPerfect as $item): ?>
                                        <li class="ai-why-item"><span class="ai-why-message"><?= htmlspecialchars((string)$item) ?></span></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </section>

                        <div class="ai-results-footer">
                            <div class="ai-success-box" id="aiVerdictBox">
                                <?= $initialVerdict !== '' ? htmlspecialchars($initialVerdict) : 'Waiting for verification.' ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
function selectImage() {
    document.getElementById('articleImageInput').click();
}

document.getElementById('articleImageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('imagePreview');
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(event) {
        preview.innerHTML = `<img src="${event.target.result}" alt="">`;
    };
    reader.readAsDataURL(file);
});

function removeImage() {
    document.getElementById('articleImageInput').value = '';
    document.getElementById('imagePreview').innerHTML = '<span>No image selected</span>';
    document.getElementById('removeImageFlag').value = '1';
}

function escapeHtml(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#39;');
}

function setMetricBar(id, value) {
    const safeValue = Math.max(0, Math.min(100, Number(value) || 0));
    document.getElementById(id).style.width = `${safeValue}%`;
}

function setMetricPoints(id, value, max) {
    const safeValue = Math.max(0, Math.min(max, Number(value) || 0));
    document.getElementById(id).textContent = `${safeValue}/${max}`;
}

function setScoreRing(value) {
    const safeValue = Math.max(0, Math.min(100, Number(value) || 0));
    aiScoreRing.style.setProperty('--score', safeValue);
}

function headlineForDecision(decision, trustScore) {
    if (decision === 'auto_publish' && trustScore >= autoPublishThreshold) {
        return 'Reliable';
    }
    if (decision === 'needs_review' && trustScore >= needsReviewThreshold) {
        return 'Needs Review';
    }
    if (decision === 'unreliable') {
        return 'Unreliable';
    }
    return 'Awaiting Review';
}

function setLastCheckedLabel(message) {
    aiLastChecked.textContent = message;
}

function applyVerdictState(decision, text) {
    const verdictBox = document.getElementById('aiVerdictBox');
    verdictBox.textContent = text || 'Verification completed.';

    if (decision === 'auto_publish') {
        verdictBox.style.background = '#22c55e';
        verdictBox.style.color = '#08111f';
        verdictBox.style.borderColor = 'rgba(34,197,94,0.28)';
        return;
    }

    if (decision === 'needs_review') {
        verdictBox.style.background = '#f59e0b';
        verdictBox.style.color = '#111827';
        verdictBox.style.borderColor = 'rgba(245,158,11,0.26)';
        return;
    }

    if (decision === 'unreliable') {
        verdictBox.style.background = '#ef4444';
        verdictBox.style.color = '#fff';
        verdictBox.style.borderColor = 'rgba(239,68,68,0.28)';
        return;
    }

    verdictBox.style.background = 'rgba(46,223,154,0.10)';
    verdictBox.style.color = '#2edf9a';
    verdictBox.style.borderColor = 'rgba(46,223,154,0.24)';
}

const autoPublishThreshold = <?= (int)$autoPublishTrustScore ?>;
const needsReviewThreshold = <?= (int)$needsReviewTrustScore ?>;
const publishButton = document.getElementById('publishButton');
const publishStatus = document.getElementById('publishStatus');
const trustScoreInput = document.getElementById('trustScoreInput');
const initialPublishUnlocked = <?= $verificationPassed ? 'true' : 'false' ?>;
const initialDecision = <?= json_encode($initialDecision) ?>;
const initialContentText = <?= json_encode((string)$val['content']) ?>;
const aiLastChecked = document.getElementById('aiLastChecked');
const aiScoreRing = document.getElementById('aiScoreRing');
const aiVerdictHeadline = document.getElementById('aiVerdictHeadline');
const claimSummaryBox = document.getElementById('aiClaimSummaryBox');
const claimSupportedBadge = document.getElementById('aiClaimSupportedBadge');
const claimWeakBadge = document.getElementById('aiClaimWeakBadge');
const claimContradictedBadge = document.getElementById('aiClaimContradictedBadge');
const supportedCount = document.getElementById('aiSupportedCount');
const weakCount = document.getElementById('aiWeakCount');
const contradictedCount = document.getElementById('aiContradictedCount');
const totalClaimsCount = document.getElementById('aiTotalClaimsCount');
const areasBox = document.getElementById('aiAreasBox');
const areasSummary = document.getElementById('aiAreasSummary');
const areasGroups = document.getElementById('aiAreasGroups');
const areasSubtitle = document.getElementById('aiAreasSubtitle');
const impactValue = document.getElementById('aiImpactValue');
const improveGuideCard = document.getElementById('aiImproveGuideCard');
const improveGuideText = document.getElementById('aiImproveGuideText');
const improveGuideButton = document.getElementById('aiImproveGuideButton');
const whyNotPerfectBox = document.getElementById('aiWhyNotPerfectBox');
const whyNotPerfectList = document.getElementById('aiWhyNotPerfectList');
const sourcesBox = document.getElementById('aiSourcesBox');
const sourcesList = document.getElementById('aiSourcesList');
const sourcesCountLabel = document.getElementById('aiSourcesCountLabel');
const contentInput = document.querySelector('textarea[name="content"]');
const contentFormGroup = contentInput ? contentInput.closest('.form-group') : null;
const claimsBox = document.getElementById('aiClaimsBox');
const claimsList = document.getElementById('aiClaimsList');
const improveBox = document.getElementById('aiImproveBox');
const improveList = document.getElementById('aiImproveList');
const misinformationBox = document.getElementById('aiMisinformationBox');
const misinformationList = document.getElementById('aiMisinformationList');
let contentSentenceMap = new Map();

function setPublishLockState(isUnlocked, message) {
    publishButton.disabled = !isUnlocked;
    publishButton.classList.toggle('is-locked', !isUnlocked);
    publishButton.setAttribute('aria-disabled', isUnlocked ? 'false' : 'true');
    publishStatus.textContent = message;
}

function invalidateVerification(message = 'Content changed. Run AI Fact Check again. Only Auto Publish results at 81% or above unlock publishing.') {
    trustScoreInput.value = '0';
    setPublishLockState(false, message);
    contentSentenceMap = new Map();
    setLastCheckedLabel('Last checked: Outdated - rerun AI Fact Check');
}

function resetAIStates() {
    document.getElementById('ai-empty').style.display = 'none';
    document.getElementById('ai-loading').style.display = 'none';
    document.getElementById('ai-error').style.display = 'none';
    document.getElementById('ai-result').style.display = 'none';
}

function showAIError(message) {
    resetAIStates();
    document.getElementById('ai-error').style.display = 'block';
    document.getElementById('aiErrorMessage').textContent = message;
    setLastCheckedLabel('Last checked: Error');
}

function formatVerifyError(data, fallbackMessage) {
    const parts = [];
    const baseError = data && data.error ? String(data.error) : String(fallbackMessage || 'AI verification failed.');
    parts.push(baseError);

    if (data && data.details) {
        if (typeof data.details === 'string') {
            parts.push(`Details: ${data.details}`);
        } else {
            try {
                parts.push(`Details: ${JSON.stringify(data.details)}`);
            } catch (error) {
                parts.push('Details: [unserializable error payload]');
            }
        }
    }

    if (data && data.webhook_url) {
        parts.push(`Webhook: ${data.webhook_url}`);
    }

    if (data && data.status_code) {
        parts.push(`Status: ${data.status_code}`);
    }

    return parts.join(' ');
}

function renderMisinformationHighlights(items) {
    if (!Array.isArray(items) || items.length === 0) {
        misinformationList.innerHTML = '';
        misinformationBox.style.display = 'none';
        return;
    }

    misinformationList.innerHTML = items.map((item) => {
        const line = item && item.line ? `<strong>${escapeHtml(item.line)}</strong><br>` : '';
        const reason = escapeHtml(item && item.reason ? item.reason : 'Unsupported or contradicted by trusted CNA/ST evidence.');
        const source = item && item.source ? ` <span class="text-muted">(${escapeHtml(item.source)})</span>` : '';
        return `<li>${line}${reason}${source}</li>`;
    }).join('');
    misinformationBox.style.display = 'block';
}

function renderClaimSummary(summary) {
    const supported = Math.max(0, Number(summary && summary.supported ? summary.supported : 0));
    const weak = Math.max(0, Number(summary && summary.weak ? summary.weak : 0));
    const contradicted = Math.max(0, Number(summary && summary.contradicted ? summary.contradicted : 0));
    const total = Math.max(0, Number(summary && summary.total ? summary.total : (supported + weak + contradicted)));

    if (total === 0) {
        claimSummaryBox.style.display = 'none';
        supportedCount.textContent = '0';
        contradictedCount.textContent = '0';
        weakCount.textContent = '0';
        totalClaimsCount.textContent = '0';
        return;
    }

    claimSupportedBadge.textContent = `${supported} Supported`;
    claimWeakBadge.textContent = `${weak} Needs Support`;
    claimContradictedBadge.textContent = `${contradicted} Contradicted`;
    supportedCount.textContent = String(supported);
    contradictedCount.textContent = String(contradicted);
    weakCount.textContent = String(weak);
    totalClaimsCount.textContent = String(total);
    claimSummaryBox.style.display = 'flex';
}

function truncateIssueText(value, maxLength = 120) {
    const text = String(value || '').trim();
    if (text.length <= maxLength) {
        return text;
    }
    return `${text.slice(0, maxLength - 1).trimEnd()}…`;
}

function normalizeWhyItems(items) {
    if (!Array.isArray(items)) {
        return [];
    }

    return items.map((item) => {
        if (item && typeof item === 'object' && !Array.isArray(item)) {
            return {
                message: String(item.message || '').trim(),
                examples: Array.isArray(item.examples) ? item.examples : []
            };
        }

        return {
            message: String(item || '').trim(),
            examples: []
        };
    }).filter((item) => item.message);
}

function truncateIssuePreview(value, maxLength = 120) {
    const text = String(value || '').trim();
    if (text.length <= maxLength) {
        return text;
    }
    return `${text.slice(0, Math.max(0, maxLength - 3)).trimEnd()}...`;
}

function findMatchedSourceForClaim(claim, matchedSources) {
    if (!Array.isArray(matchedSources) || matchedSources.length === 0) {
        return null;
    }

    const sourceLabel = String(claim && claim.source ? claim.source : '').toLowerCase();
    if (sourceLabel.includes('cna+st')) {
        return matchedSources[0] || null;
    }
    if (sourceLabel) {
        const directMatch = matchedSources.find((source) => {
            const name = String(source && source.name ? source.name : '').toLowerCase();
            return name && sourceLabel.includes(name);
        });
        if (directMatch) {
            return directMatch;
        }
    }

    const claimText = String(claim && (claim.text || claim.draft_sentence) ? (claim.text || claim.draft_sentence) : '').toLowerCase();
    return matchedSources.find((source) => {
        const title = String(source && source.title ? source.title : '').toLowerCase();
        return title && claimText && (title.includes(claimText.slice(0, 28)) || claimText.includes(title.slice(0, 28)));
    }) || matchedSources[0] || null;
}

function buildClaimIssueItem(claim, matchedSources) {
    const status = String(claim && claim.status ? claim.status : 'supported');
    const matchedSource = findMatchedSourceForClaim(claim, matchedSources);
    const highlightKey = claim && claim.sentence_key
        ? String(claim.sentence_key)
        : normalizeForSentenceMatch(claim && claim.draft_sentence ? claim.draft_sentence : '');
    const draftSentence = String(claim && (claim.draft_sentence || claim.text) ? (claim.draft_sentence || claim.text) : '').trim();
    const description = String(
        claim && claim.reason
            ? claim.reason
            : (status === 'contradicted'
                ? 'This claim appears to conflict with trusted CNA/ST reporting.'
                : 'This claim needs stronger or more direct CNA/ST support.')
    ).trim();
    const examples = [];

    if (draftSentence) {
        examples.push({
            label: 'Draft sentence',
            text: draftSentence,
            reason: status === 'contradicted' ? 'Review and correct this sentence.' : 'Review and strengthen this sentence.',
            highlight_key: highlightKey || null,
            url: null
        });
    }

    if (matchedSource && matchedSource.url) {
        examples.push({
            label: 'Source example',
            text: String(matchedSource.title || matchedSource.name || 'Matched source'),
            reason: `${String(matchedSource.name || 'Trusted source')} - ${String(matchedSource.match_type || 'related')}`,
            highlight_key: null,
            url: String(matchedSource.url)
        });
    }

    return {
        title: status === 'contradicted' ? 'Contradicting claim(s) lower the score.' : 'This claim needs stronger support.',
        description,
        status,
        examples
    };
}

function categorizeWhyMessage(message) {
    const lower = String(message || '').toLowerCase();
    if (/(contradict|false|unsupported|misinformation)/.test(lower)) {
        return 'contradicted';
    }
    if (/(source quality|support|supported|corroborat|confirmed|evidence|reference|trusted source|matched cna\/st)/.test(lower)) {
        return 'weak';
    }
    return 'clarity';
}

function suggestionCategory(text) {
    const lower = String(text || '').toLowerCase();
    if (/(source|cna|st|reference|quote|data|evidence|support)/.test(lower)) {
        return 'weak';
    }
    return 'clarity';
}

function dedupeIssueItems(items) {
    const seen = new Set();
    return items.filter((item) => {
        const key = `${item.status}|${String(item.title || '').toLowerCase()}|${String(item.description || '').toLowerCase()}`;
        if (seen.has(key)) {
            return false;
        }
        seen.add(key);
        return true;
    });
}

function renderAreaExamples(examples) {
    if (!Array.isArray(examples) || examples.length === 0) {
        return '';
    }

    return `<div class="ai-area-examples">${examples.map((example) => {
        const label = escapeHtml(example && example.label ? example.label : 'Example');
        const text = escapeHtml(truncateIssuePreview(example && example.text ? example.text : '', 140));
        const reason = example && example.reason ? `<span class="ai-area-example-reason">${escapeHtml(example.reason)}</span>` : '';
        const highlightKey = example && example.highlight_key ? escapeHtml(example.highlight_key) : '';
        const url = example && example.url ? escapeHtml(example.url) : '';

        if (highlightKey) {
            return `<button type="button" class="ai-area-example is-clickable" data-highlight-key="${highlightKey}">
                <span class="ai-area-example-label">${label}</span>
                <strong>${text}</strong>
                ${reason}
                <span class="ai-area-example-action">Jump to sentence</span>
            </button>`;
        }

        if (url) {
            return `<a class="ai-area-example" href="${url}" target="_blank" rel="noopener noreferrer">
                <span class="ai-area-example-label">${label}</span>
                <strong>${text}</strong>
                ${reason}
                <span class="ai-area-example-action">View source</span>
            </a>`;
        }

        return `<div class="ai-area-example">
            <span class="ai-area-example-label">${label}</span>
            <strong>${text}</strong>
            ${reason}
        </div>`;
    }).join('')}</div>`;
}

function renderAreaGroup(title, count, status, impactLabel, items, openByDefault = false) {
    if (!Array.isArray(items) || items.length === 0) {
        return '';
    }

    return `<details class="ai-area-group is-${status}" ${openByDefault ? 'open' : ''}>
        <summary class="ai-area-summary">
            <span class="ai-area-summary-title">${escapeHtml(title)} (${count})</span>
            <span class="ai-area-summary-impact">${escapeHtml(impactLabel)}</span>
        </summary>
        <div class="ai-area-body">
            ${items.map((item) => `<article class="ai-area-item is-${status}">
                <div class="ai-area-item-copy">
                    <h5>${escapeHtml(item.title || '')}</h5>
                    <p>${escapeHtml(item.description || '')}</p>
                </div>
                ${renderAreaExamples(item.examples)}
            </article>`).join('')}
        </div>
    </details>`;
}

function bindAreaActions() {
    areasBox.querySelectorAll('[data-highlight-key]').forEach((element) => {
        element.addEventListener('click', () => {
            const highlightKey = element.getAttribute('data-highlight-key');
            if (!highlightKey) {
                return;
            }
            jumpToClaimHighlight(highlightKey);
        });
    });
}

function renderAreasToImprove({ trustScore = 0, claims = [], whyItems = [], suggestions = [], matchedSources = [], highlights = [] } = {}) {
    const contradictedItems = [];
    const supportItems = [];
    const clarityItems = [];

    (Array.isArray(claims) ? claims : []).forEach((claim) => {
        const status = String(claim && claim.status ? claim.status : 'supported');
        if (status === 'contradicted') {
            contradictedItems.push(buildClaimIssueItem(claim, matchedSources));
        } else if (status === 'weak') {
            supportItems.push(buildClaimIssueItem(claim, matchedSources));
        }
    });

    normalizeWhyItems(whyItems).forEach((item) => {
        const category = categorizeWhyMessage(item.message);
        const issue = {
            title: item.message,
            description: category === 'contradicted'
                ? 'This reason signals a direct factual conflict that should be fixed first.'
                : (category === 'weak'
                    ? 'This part of the article needs stronger sourcing or more direct corroboration.'
                    : 'This is more about wording, context, or explanation than a hard factual conflict.'),
            status: category,
            examples: item.examples || []
        };

        if (category === 'contradicted') {
            contradictedItems.push(issue);
        } else if (category === 'weak') {
            supportItems.push(issue);
        } else {
            clarityItems.push(issue);
        }
    });

    (Array.isArray(suggestions) ? suggestions : []).slice(0, 4).forEach((suggestion) => {
        const category = suggestionCategory(suggestion);
        const issue = {
            title: category === 'weak' ? 'Stronger sourcing would improve this draft.' : 'Clearer wording would improve this draft.',
            description: String(suggestion || ''),
            status: category,
            examples: []
        };
        if (category === 'weak') {
            supportItems.push(issue);
        } else {
            clarityItems.push(issue);
        }
    });

    if (Array.isArray(highlights) && highlights.length && contradictedItems.length === 0) {
        highlights.slice(0, 3).forEach((item) => {
            contradictedItems.push({
                title: 'Unsupported or false line detected.',
                description: String(item && item.reason ? item.reason : 'Trusted CNA/ST evidence does not support this line.'),
                status: 'contradicted',
                examples: [{
                    label: 'Draft line',
                    text: String(item && item.line ? item.line : ''),
                    reason: item && item.source ? String(item.source) : 'Review this line.',
                    highlight_key: normalizeForSentenceMatch(item && item.line ? item.line : ''),
                    url: null
                }]
            });
        });
    }

    const contradictionList = dedupeIssueItems(contradictedItems);
    const supportList = dedupeIssueItems(supportItems);
    const clarityList = dedupeIssueItems(clarityItems);
    const totalIssues = contradictionList.length + supportList.length + clarityList.length;

    if (totalIssues === 0) {
        areasSummary.innerHTML = '';
        areasGroups.innerHTML = '';
        areasBox.style.display = 'none';
        improveGuideCard.style.display = 'none';
        return;
    }

    const scoreDelta = Math.max(0, 100 - (Number(trustScore) || 0));
    impactValue.textContent = `-${scoreDelta}%`;
    areasSubtitle.textContent = contradictionList.length > 0
        ? 'These issues are affecting your score. Fix the contradictions first, then strengthen weaker evidence.'
        : 'These issues are affecting your score. Addressing them can improve the accuracy and reliability of your article.';

    areasSummary.innerHTML = `
        <div class="ai-area-stat is-contradicted">
            <strong>${contradictionList.length}</strong>
            <span>Contradictions</span>
            <small>High impact</small>
        </div>
        <div class="ai-area-stat is-weak">
            <strong>${supportList.length}</strong>
            <span>Need More Support</span>
            <small>Medium impact</small>
        </div>
        <div class="ai-area-stat is-clarity">
            <strong>${clarityList.length}</strong>
            <span>Clarity Issues</span>
            <small>Low impact</small>
        </div>
        <div class="ai-area-stat is-total">
            <strong>${totalIssues}</strong>
            <span>Total Issues</span>
            <small>Across all categories</small>
        </div>
    `;

    const firstOpen = contradictionList.length > 0 ? 'contradicted' : (supportList.length > 0 ? 'weak' : 'clarity');
    areasGroups.innerHTML = [
        renderAreaGroup('Contradictions', contradictionList.length, 'contradicted', 'High impact', contradictionList, firstOpen === 'contradicted'),
        renderAreaGroup('Need More Support', supportList.length, 'weak', 'Medium impact', supportList, firstOpen === 'weak'),
        renderAreaGroup('Clarity Issues', clarityList.length, 'clarity', 'Low impact', clarityList, firstOpen === 'clarity')
    ].filter(Boolean).join('');

    const guideLines = (Array.isArray(suggestions) ? suggestions : []).slice(0, 3);
    if (guideLines.length > 0) {
        improveGuideText.textContent = guideLines.join(' ');
        improveGuideCard.style.display = 'flex';
    } else {
        improveGuideCard.style.display = 'none';
    }

    areasBox.style.display = 'block';
    bindAreaActions();
}

function renderWhyNotPerfect(items) {
    if (!Array.isArray(items) || items.length === 0) {
        whyNotPerfectList.innerHTML = '';
        whyNotPerfectBox.style.display = 'none';
        return;
    }

    whyNotPerfectList.innerHTML = items.map((item) => {
        if (item && typeof item === 'object' && !Array.isArray(item)) {
            const message = escapeHtml(item.message || '');
            const examples = Array.isArray(item.examples) ? item.examples : [];
            const exampleHtml = examples.map((example) => {
                const label = escapeHtml(example && example.label ? example.label : 'Example');
                const text = escapeHtml(example && example.text ? example.text : '');
                const reason = example && example.reason ? `<span class="ai-why-example-reason">${escapeHtml(example.reason)}</span>` : '';
                const highlightKey = example && example.highlight_key ? escapeHtml(example.highlight_key) : '';
                const url = example && example.url ? escapeHtml(example.url) : '';

                if (highlightKey) {
                    return `<button type="button" class="ai-why-example is-clickable" data-highlight-key="${highlightKey}">
                        <span class="ai-why-example-label">${label}</span>
                        <strong>${text}</strong>
                        ${reason}
                    </button>`;
                }

                if (url) {
                    return `<a class="ai-why-example" href="${url}" target="_blank" rel="noopener noreferrer">
                        <span class="ai-why-example-label">${label}</span>
                        <strong>${text}</strong>
                        ${reason}
                    </a>`;
                }

                return `<div class="ai-why-example">
                    <span class="ai-why-example-label">${label}</span>
                    <strong>${text}</strong>
                    ${reason}
                </div>`;
            }).join('');

            return `<li class="ai-why-item"><span class="ai-why-message">${message}</span>${exampleHtml ? `<div class="ai-why-examples">${exampleHtml}</div>` : ''}</li>`;
        }

        return `<li class="ai-why-item"><span class="ai-why-message">${escapeHtml(item)}</span></li>`;
    }).join('');
    whyNotPerfectBox.style.display = 'block';
    bindWhyNotPerfectClicks();
}

function renderMatchedSources(items) {
    if (!Array.isArray(items) || items.length === 0) {
        sourcesList.innerHTML = '';
        sourcesBox.style.display = 'none';
        if (sourcesCountLabel) {
            sourcesCountLabel.textContent = '0 sources matched';
        }
        return;
    }

    if (sourcesCountLabel) {
        sourcesCountLabel.textContent = `${items.length} source${items.length === 1 ? '' : 's'} matched`;
    }

    sourcesList.innerHTML = items.map((source) => {
        const title = escapeHtml(source && (source.title || source.name) ? (source.title || source.name) : '');
        const matchType = escapeHtml(source && source.match_type ? source.match_type : 'related');
        const name = escapeHtml(source && source.name ? source.name : '');
        const sourceType = source && source.source_type ? ` · ${escapeHtml(source.source_type)}` : '';
        const url = source && source.url ? `<a href="${escapeHtml(source.url)}" target="_blank" rel="noopener noreferrer">View source</a>` : '';
        const sourceTypeLabel = source && source.source_type ? escapeHtml(source.source_type) : '';

        return `<div class="ai-source-card">
            <div class="ai-source-topline">
                <span class="ai-source-name">${name}</span>
                <span class="ai-source-match">${matchType}</span>
            </div>
            <strong>${title}</strong>
            <p class="ai-source-type">${name}${sourceTypeLabel ? ` - ${sourceTypeLabel}` : ''}</p>
            ${url}
        </div>`;
    }).join('');
    sourcesBox.style.display = 'block';
}

function renderImprovementSuggestions(items) {
    if (!Array.isArray(items) || items.length === 0) {
        improveList.innerHTML = '';
        improveBox.style.display = 'none';
        return;
    }

    improveList.innerHTML = items.map((item) => `<li>${escapeHtml(item)}</li>`).join('');
    improveBox.style.display = 'block';
}

function renderClaims(items) {
    if (!Array.isArray(items) || items.length === 0) {
        claimsList.innerHTML = '';
        claimsBox.style.display = 'none';
        return;
    }

    claimsList.innerHTML = items.map((claim) => {
        const status = claim && claim.status ? String(claim.status) : 'supported';
        const label = status === 'contradicted' ? 'Contradicted' : (status === 'weak' ? 'Needs Support' : 'Supported');
        const statusClass = status === 'contradicted' ? 'is-contradicted' : (status === 'weak' ? 'is-weak' : 'is-supported');
        const text = escapeHtml(claim && claim.text ? claim.text : '');
        const reason = claim && claim.reason ? escapeHtml(claim.reason) : `Match Score: ${(Number(claim && claim.match_score ? claim.match_score : 0)).toFixed(2)}`;
        const source = claim && claim.source ? ` Source: ${escapeHtml(claim.source)}` : '';
        const claimKey = claim && claim.claim_key ? escapeHtml(claim.claim_key) : '';
        const highlightKey = claim && claim.sentence_key
            ? escapeHtml(claim.sentence_key)
            : escapeHtml(normalizeForSentenceMatch(claim && claim.draft_sentence ? claim.draft_sentence : ''));

        return `<button type="button" class="ai-issue-card ${statusClass}" data-claim-key="${claimKey}" data-highlight-key="${highlightKey}">
            <span class="ai-issue-badge">${label}</span>
            <span class="ai-issue-text">${text}</span>
            <span class="ai-issue-meta">${reason}${source}</span>
            <span class="ai-issue-action">Jump to sentence</span>
        </button>`;
    }).join('');
    claimsBox.style.display = 'block';
    bindClaimCardClicks();
}

function normalizeForSentenceMatch(value) {
    return String(value || '')
        .replace(/\r\n?/g, '\n')
        .trim()
        .replace(/\s+/g, ' ');
}

function renderContentHighlights(content, claims, whyItems = []) {
    const rawContent = String(content || '').trim();
    contentSentenceMap = new Map();
    if (!rawContent) {
        return;
    }

    const normalizedClaims = Array.isArray(claims) ? claims : [];
    const sentenceClaimMap = new Map();
    const statusPriority = { contradicted: 4, weak: 3, clarity: 2, supported: 1 };
    const registerSentenceMatch = (keys, entry) => {
        const currentPriority = statusPriority[String(entry && entry.status ? entry.status : 'supported')] || 0;

        keys.filter(Boolean).forEach((mapKey) => {
            const existing = sentenceClaimMap.get(mapKey);
            const existingPriority = existing ? (statusPriority[String(existing.status || 'supported')] || 0) : 0;

            if (!existing || currentPriority >= existingPriority) {
                sentenceClaimMap.set(mapKey, entry);
            }
        });
    };

    normalizedClaims.forEach((claim) => {
        const sentenceKey = claim && claim.sentence_key ? String(claim.sentence_key) : '';
        const sentenceIndex = claim && Number.isInteger(Number(claim.sentence_index))
            ? `index:${Number(claim.sentence_index)}`
            : '';
        const draftSentence = claim && claim.draft_sentence ? normalizeForSentenceMatch(claim.draft_sentence) : '';
        if (!sentenceKey && !sentenceIndex && !draftSentence) {
            return;
        }

        registerSentenceMatch([sentenceKey, sentenceIndex, draftSentence], claim);
    });

    normalizeWhyItems(whyItems).forEach((item) => {
        const issueStatus = categorizeWhyMessage(item.message);
        (Array.isArray(item.examples) ? item.examples : []).forEach((example) => {
            const sentenceKey = example && example.highlight_key ? String(example.highlight_key) : '';
            const draftSentence = example && example.text ? normalizeForSentenceMatch(example.text) : '';
            if (!sentenceKey && !draftSentence) {
                return;
            }

            registerSentenceMatch([sentenceKey, draftSentence], {
                status: issueStatus,
                sentence_key: sentenceKey || null,
                draft_sentence: example && example.text ? String(example.text) : '',
                reason: item.message
            });
        });
    });

    const paragraphs = rawContent.split(/\n{2,}/).filter(Boolean);
    let sentenceCursor = 0;
    let searchCursor = 0;
    paragraphs.forEach((paragraph) => {
        const sentences = paragraph.split(/(?<=[.!?])\s+/).filter(Boolean);
        sentences.forEach((sentence) => {
            const normalizedSentence = normalizeForSentenceMatch(sentence);
            const currentSentenceIndex = sentenceCursor++;
            const matchedClaim = sentenceClaimMap.get(`index:${currentSentenceIndex}`)
                || sentenceClaimMap.get(normalizedSentence);
            const sentenceStart = rawContent.indexOf(sentence, searchCursor);
            const start = sentenceStart >= 0 ? sentenceStart : searchCursor;
            const end = start + sentence.length;
            searchCursor = end;

            if (!matchedClaim) {
                return;
            }

            const highlightKey = matchedClaim && matchedClaim.sentence_key
                ? String(matchedClaim.sentence_key)
                : normalizedSentence;
            const selectionMeta = {
                start,
                end,
                status: String(matchedClaim.status || 'supported')
            };

            [highlightKey, normalizedSentence].filter(Boolean).forEach((key) => {
                contentSentenceMap.set(key, selectionMeta);
            });
        });
    });
}

function jumpToClaimHighlight(claimKey) {
    if (!claimKey || !contentInput) {
        return;
    }

    const directKey = String(claimKey);
    const fallbackKey = normalizeForSentenceMatch(claimKey);
    const target = contentSentenceMap.get(directKey) || contentSentenceMap.get(fallbackKey);
    if (!target) {
        return;
    }

    if (contentFormGroup) {
        contentFormGroup.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    contentInput.focus();
    contentInput.setSelectionRange(target.start, target.end);

    const lineHeight = parseFloat(window.getComputedStyle(contentInput).lineHeight) || 28;
    const linesBefore = contentInput.value.slice(0, target.start).split('\n').length - 1;
    contentInput.scrollTop = Math.max(0, (linesBefore * lineHeight) - (contentInput.clientHeight * 0.35));

    contentInput.classList.remove('ai-content-focus', 'ai-content-focus-supported', 'ai-content-focus-weak', 'ai-content-focus-clarity', 'ai-content-focus-contradicted');
    contentInput.classList.add('ai-content-focus');
    contentInput.classList.add(`ai-content-focus-${target.status}`);

    setTimeout(() => {
        contentInput.classList.remove('ai-content-focus', 'ai-content-focus-supported', 'ai-content-focus-weak', 'ai-content-focus-clarity', 'ai-content-focus-contradicted');
    }, 1800);
}

function bindClaimCardClicks() {
    claimsList.querySelectorAll('[data-highlight-key]').forEach((card) => {
        card.addEventListener('click', () => {
            jumpToClaimHighlight(card.getAttribute('data-highlight-key'));
        });
    });
}

function bindWhyNotPerfectClicks() {
    whyNotPerfectList.querySelectorAll('[data-highlight-key]').forEach((item) => {
        item.addEventListener('click', () => {
            jumpToClaimHighlight(item.getAttribute('data-highlight-key'));
        });
    });
}

function messageForDecision(decision) {
    if (decision === 'auto_publish') {
        return 'AI verification passed. Publishing is unlocked for direct publication.';
    }
    if (decision === 'needs_review') {
        return 'Needs Review. This draft will not publish yet. Strengthen the evidence and rerun AI Fact Check.';
    }
    if (decision === 'unreliable') {
        return 'Unreliable. Fix the highlighted misinformation or unsupported claims before trying again.';
    }
    return 'Run AI Fact Check. Only Auto Publish results at 81% or above unlock publishing.';
}

async function runAICheck() {
    const title = document.querySelector('input[name="title"]').value.trim();
    const excerpt = document.querySelector('input[name="excerpt"]').value.trim();
    const content = document.querySelector('textarea[name="content"]').value.trim();
    const categorySelect = document.querySelector('select[name="category_id"]');
    const sourceUrl = document.getElementById('sourceUrlInput').value.trim();
    const button = document.querySelector('.btn-ai');

    if (!title || !excerpt || !content || !categorySelect.value) {
        showAIError('Fill in the title, summary, category, and content before running AI verification.');
        return;
    }

    resetAIStates();
    document.getElementById('ai-loading').style.display = 'block';
    setLastCheckedLabel('Last checked: Running now');
    button.disabled = true;
    button.textContent = 'Running AI Fact Check...';

    try {
        const response = await fetch('/api/ai-verify.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                article_id: <?= $isEdit ? (int)$editId : 0 ?>,
                title,
                excerpt,
                content,
                category_id: Number(categorySelect.value) || 0,
                category: categorySelect.options[categorySelect.selectedIndex].text,
                source_url: sourceUrl
            })
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(formatVerifyError(data, 'AI verification failed.'));
        }

        const metrics = data.metrics || {};
        const rubricMetrics = data.rubric_metrics || {};
        const claimSummary = data.claim_summary || {};
        const trustScore = Math.max(0, Math.min(100, Number(data.trust_score) || 0));
        const decision = String(
            data.publish_decision ||
            (trustScore >= autoPublishThreshold
                ? 'auto_publish'
                : (trustScore >= needsReviewThreshold ? 'needs_review' : 'unreliable'))
        );
        const sourceLabel = document.getElementById('aiSourceLabel');

        document.getElementById('trustScoreInput').value = trustScore;
        document.getElementById('aiTrustScore').textContent = `${trustScore}%`;
        setScoreRing(trustScore);
        aiVerdictHeadline.textContent = headlineForDecision(decision, trustScore);
        document.getElementById('aiSummary').textContent = data.summary || 'Verification completed.';
        setLastCheckedLabel(data.cached_result ? 'Last checked: Just now (cached)' : 'Last checked: Just now');

        if (data.source_label) {
            sourceLabel.style.display = 'block';
            sourceLabel.textContent = data.cached_result
                ? `${data.source_label} Reused cached verification for the unchanged draft.`
                : data.source_label;
        } else if (sourceUrl) {
            sourceLabel.style.display = 'block';
            sourceLabel.innerHTML = data.cached_result
                ? `Reference used: <a href="${escapeHtml(sourceUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(sourceUrl)}</a> Reused cached verification for the unchanged draft.`
                : `Reference used: <a href="${escapeHtml(sourceUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(sourceUrl)}</a>`;
        } else {
            sourceLabel.style.display = data.cached_result ? 'block' : 'none';
            sourceLabel.textContent = data.cached_result ? 'Reused cached verification for the unchanged draft.' : '';
        }

        setMetricBar('metricFactualAccuracy', metrics.factual_accuracy);
        setMetricBar('metricSourceQuality', metrics.source_quality);
        setMetricBar('metricBiasDetection', metrics.bias_detection);
        setMetricBar('metricLogicalConsistency', metrics.logical_consistency);
        setMetricBar('metricCompleteness', metrics.completeness);
        setMetricPoints('metricFactualAccuracyPoints', rubricMetrics.factual_accuracy, 45);
        setMetricPoints('metricSourceQualityPoints', rubricMetrics.source_quality, 25);
        setMetricPoints('metricBiasDetectionPoints', rubricMetrics.bias_detection, 10);
        setMetricPoints('metricLogicalConsistencyPoints', rubricMetrics.logical_consistency, 10);
        setMetricPoints('metricCompletenessPoints', rubricMetrics.completeness, 10);

        applyVerdictState(decision, data.verdict || 'Verification completed.');
        renderClaimSummary(claimSummary);
        renderAreasToImprove({
            trustScore,
            claims: data.claims || [],
            whyItems: data.why_not_perfect_details || data.why_not_perfect || [],
            suggestions: data.improvement_suggestions || [],
            matchedSources: data.matched_sources || [],
            highlights: data.misinformation_highlights || []
        });
        renderWhyNotPerfect(data.why_not_perfect_details || data.why_not_perfect || []);
        renderMatchedSources(data.matched_sources || []);
        renderClaims(data.claims || []);
        renderContentHighlights(content, data.claims || [], data.why_not_perfect_details || data.why_not_perfect || []);
        renderImprovementSuggestions(data.improvement_suggestions || []);
        renderMisinformationHighlights(data.misinformation_highlights || []);

        if (decision === 'auto_publish' && trustScore >= autoPublishThreshold) {
            setPublishLockState(true, messageForDecision(decision));
        } else if (decision === 'needs_review' && trustScore >= needsReviewThreshold) {
            setPublishLockState(false, messageForDecision(decision));
        } else {
            setPublishLockState(false, messageForDecision('unreliable'));
        }

        resetAIStates();
        document.getElementById('ai-result').style.display = 'block';
    } catch (error) {
        showAIError(error.message || 'AI verification failed.');
    } finally {
        button.disabled = false;
        button.textContent = 'AI Fact Check';
    }
}

document.querySelectorAll('input[name="title"], input[name="excerpt"], textarea[name="content"], select[name="category_id"], #sourceUrlInput')
    .forEach((field) => {
        field.addEventListener('input', () => invalidateVerification());
        field.addEventListener('change', () => invalidateVerification());
    });

setPublishLockState(
    initialPublishUnlocked,
    initialPublishUnlocked ? messageForDecision('auto_publish') : messageForDecision(initialDecision)
);
setScoreRing(<?= (int)$initialTrustScore ?>);
aiVerdictHeadline.textContent = headlineForDecision(initialDecision, <?= (int)$initialTrustScore ?>);
setLastCheckedLabel(<?= json_encode($hasCurrentVerification ? 'Last checked: Just now' : 'Waiting for fact check') ?>);
applyVerdictState(initialDecision, <?= json_encode($initialVerdict !== '' ? $initialVerdict : 'Waiting for verification.') ?>);
renderClaimSummary(<?= json_encode($initialClaimSummary) ?>);
renderAreasToImprove({
    trustScore: <?= (int)$initialTrustScore ?>,
    claims: <?= json_encode($initialClaims) ?>,
    whyItems: <?= json_encode(!empty($initialWhyNotPerfectDetails) ? $initialWhyNotPerfectDetails : $initialWhyNotPerfect) ?>,
    suggestions: <?= json_encode($initialSuggestions) ?>,
    matchedSources: <?= json_encode($initialMatchedSources) ?>,
    highlights: <?= json_encode($initialHighlights) ?>
});
renderWhyNotPerfect(<?= json_encode(!empty($initialWhyNotPerfectDetails) ? $initialWhyNotPerfectDetails : $initialWhyNotPerfect) ?>);
renderMatchedSources(<?= json_encode($initialMatchedSources) ?>);
renderClaims(<?= json_encode($initialClaims) ?>);
renderContentHighlights(initialContentText, <?= json_encode($initialClaims) ?>, <?= json_encode(!empty($initialWhyNotPerfectDetails) ? $initialWhyNotPerfectDetails : $initialWhyNotPerfect) ?>);
renderImprovementSuggestions(<?= json_encode($initialSuggestions) ?>);
renderMisinformationHighlights(<?= json_encode($initialHighlights) ?>);

if (improveGuideButton) {
    improveGuideButton.addEventListener('click', () => {
        const targetSection = contentFormGroup || (sourcesBox.style.display !== 'none' ? sourcesBox : areasBox);
        targetSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}
</script>

<?php page_foot(); ?>
