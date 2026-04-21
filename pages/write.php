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
            pageRedirect('/pages/my-articles.php', null, 'Draft saved!');
        }
    } else {
        $verification = $_SESSION['article_ai_verification'] ?? null;
        $fingerprint = buildArticleVerificationFingerprint($_POST, (int)$user->id);
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
    'source_url' => $_POST['source_url'] ?? '',
];
$verification = $_SESSION['article_ai_verification'] ?? null;
$verificationFingerprint = buildArticleVerificationFingerprint($currentVerificationInput, (int)$user->id);
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

$val = [
    'title' => $_POST['title'] ?? ($article?->title ?? ''),
    'excerpt' => $_POST['excerpt'] ?? ($article?->excerpt ?? ''),
    'content' => $_POST['content'] ?? ($article?->content ?? ''),
    'category_id' => $_POST['category_id'] ?? ($article?->categoryId ?? 0),
    'source_url' => $_POST['source_url'] ?? '',
    'trust_score' => $initialTrustScore,
];

page_head($isEdit ? 'Edit Article' : 'Write Article');
?>

<link rel="stylesheet" href="/public/css/write.css"/>

<div class="dashboard-layout">
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
                    <h3>AI Verification</h3>

                    <div id="ai-empty" style="display:<?= $hasCurrentVerification ? 'none' : 'block' ?>;">
                        <p class="text-muted" style="margin-top:10px;">
                            Click "AI Fact Check" to analyze your article's credibility before publishing.
                        </p>
                        <p class="text-muted" style="font-size:13px;">
                            Add an exact CNA or ST article URL if you have one. 81%+ unlocks publish, 60-80% stays in Needs Review, and anything below 60% is Unreliable.
                        </p>
                    </div>

                    <div id="ai-loading" style="display:none;">
                        <p class="text-muted" style="margin-top:10px;">
                            AI Fact Checking...
                        </p>
                    </div>

                    <div id="ai-error" style="display:none;">
                        <div class="alert alert-error" id="aiErrorMessage" style="margin-top:14px;"></div>
                    </div>

                    <div id="ai-result" style="display:<?= $hasCurrentVerification ? 'block' : 'none' ?>;">
                        <div style="text-align:center; margin:20px 0;">
                            <h2 id="aiTrustScore" style="font-size:28px; font-weight:700;"><?= (int)$val['trust_score'] ?>%</h2>
                            <p class="text-muted">Trust Score</p>
                        </div>

                        <p id="aiSummary" style="font-size:13px; color:#555; margin-bottom:16px; line-height:1.5;">
                            <?= $initialSummary !== '' ? htmlspecialchars($initialSummary) : 'Run AI Fact Check to see a real verification summary from n8n.' ?>
                        </p>

                        <p id="aiSourceLabel" class="text-muted" style="font-size:12px; display:<?= $initialSourceLabel !== '' ? 'block' : 'none' ?>;"><?= htmlspecialchars($initialSourceLabel) ?></p>

                        <div id="aiClaimsBox" style="display:<?= !empty($initialClaims) ? 'block' : 'none' ?>; margin:14px 0;">
                            <p style="font-weight:700; margin-bottom:8px;">Claim Review</p>
                            <div id="aiClaimsList" style="display:grid; gap:10px;">
                                <?php foreach ($initialClaims as $claim): ?>
                                    <?php
                                    $status = trim((string)($claim['status'] ?? 'supported'));
                                    $label = $status === 'contradicted' ? 'Contradicted' : ($status === 'weak' ? 'Needs Support' : 'Supported');
                                    $bg = $status === 'contradicted' ? '#3a1820' : ($status === 'weak' ? '#3b2a12' : '#132f22');
                                    $fg = $status === 'contradicted' ? '#fecdd3' : ($status === 'weak' ? '#fde68a' : '#bbf7d0');
                                    ?>
                                    <div style="border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:10px 12px; background:rgba(255,255,255,0.02);">
                                        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:6px;">
                                            <strong style="font-size:13px; line-height:1.5;"><?= htmlspecialchars((string)($claim['text'] ?? '')) ?></strong>
                                            <span style="font-size:11px; padding:4px 8px; border-radius:999px; background:<?= $bg ?>; color:<?= $fg ?>; white-space:nowrap;"><?= $label ?></span>
                                        </div>
                                        <?php if (!empty($claim['reason'])): ?>
                                            <p class="text-muted" style="font-size:12px; margin:0 0 6px; line-height:1.5;"><?= htmlspecialchars((string)$claim['reason']) ?></p>
                                        <?php endif; ?>
                                        <p class="text-muted" style="font-size:11px; margin:0;">
                                            Match Score: <?= htmlspecialchars((string)round((float)($claim['match_score'] ?? 0), 2)) ?>
                                            <?php if (!empty($claim['source'])): ?>
                                                · Source: <?= htmlspecialchars((string)$claim['source']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div id="aiImproveBox" style="display:<?= !empty($initialSuggestions) ? 'block' : 'none' ?>; margin:14px 0;">
                            <p style="font-weight:700; margin-bottom:8px;">How To Improve</p>
                            <ul id="aiImproveList" style="padding-left:18px; margin:0;">
                                <?php foreach ($initialSuggestions as $item): ?>
                                    <li style="margin-bottom:8px;"><?= htmlspecialchars((string)$item) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div id="aiMisinformationBox" style="display:<?= !empty($initialHighlights) ? 'block' : 'none' ?>; margin:14px 0;">
                            <p style="font-weight:700; margin-bottom:8px;">Misinformation Highlights</p>
                            <ul id="aiMisinformationList" style="padding-left:18px; margin:0;">
                                <?php foreach ($initialHighlights as $item): ?>
                                    <li style="margin-bottom:8px;">
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
                        </div>

                        <p style="display:flex; justify-content:space-between; gap:12px;"><span>Factual Accuracy</span><span id="metricFactualAccuracyPoints" class="text-muted"><?= (int)($initialRubricMetrics['factual_accuracy'] ?? 0) ?>/45</span></p>
                        <div class="progress-bar"><div id="metricFactualAccuracy" style="width:<?= max(0, min(100, (int)($initialMetrics['factual_accuracy'] ?? 0))) ?>%"></div></div>

                        <p style="display:flex; justify-content:space-between; gap:12px;"><span>Source Quality</span><span id="metricSourceQualityPoints" class="text-muted"><?= (int)($initialRubricMetrics['source_quality'] ?? 0) ?>/25</span></p>
                        <div class="progress-bar"><div id="metricSourceQuality" style="width:<?= max(0, min(100, (int)($initialMetrics['source_quality'] ?? 0))) ?>%"></div></div>

                        <p style="display:flex; justify-content:space-between; gap:12px;"><span>Bias Detection</span><span id="metricBiasDetectionPoints" class="text-muted"><?= (int)($initialRubricMetrics['bias_detection'] ?? 0) ?>/10</span></p>
                        <div class="progress-bar"><div id="metricBiasDetection" style="width:<?= max(0, min(100, (int)($initialMetrics['bias_detection'] ?? 0))) ?>%"></div></div>

                        <p style="display:flex; justify-content:space-between; gap:12px;"><span>Logical Consistency</span><span id="metricLogicalConsistencyPoints" class="text-muted"><?= (int)($initialRubricMetrics['logical_consistency'] ?? 0) ?>/10</span></p>
                        <div class="progress-bar"><div id="metricLogicalConsistency" style="width:<?= max(0, min(100, (int)($initialMetrics['logical_consistency'] ?? 0))) ?>%"></div></div>

                        <p style="display:flex; justify-content:space-between; gap:12px;"><span>Completeness</span><span id="metricCompletenessPoints" class="text-muted"><?= (int)($initialRubricMetrics['completeness'] ?? 0) ?>/10</span></p>
                        <div class="progress-bar"><div id="metricCompleteness" style="width:<?= max(0, min(100, (int)($initialMetrics['completeness'] ?? 0))) ?>%"></div></div>

                        <div
                            class="ai-success-box"
                            id="aiVerdictBox"
                            style="<?=
                                $hasCurrentVerification
                                    ? (
                                        $initialDecision === 'auto_publish'
                                            ? 'background:#22c55e;color:#000;'
                                            : ($initialDecision === 'needs_review'
                                                ? 'background:#f59e0b;color:#111827;'
                                                : 'background:#ef4444;color:#fff;')
                                    )
                                    : ''
                            ?>"
                        >
                            <?= $initialVerdict !== '' ? htmlspecialchars($initialVerdict) : 'Waiting for verification.' ?>
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

const autoPublishThreshold = <?= (int)$autoPublishTrustScore ?>;
const needsReviewThreshold = <?= (int)$needsReviewTrustScore ?>;
const publishButton = document.getElementById('publishButton');
const publishStatus = document.getElementById('publishStatus');
const trustScoreInput = document.getElementById('trustScoreInput');
const initialPublishUnlocked = <?= $verificationPassed ? 'true' : 'false' ?>;
const initialDecision = <?= json_encode($initialDecision) ?>;
const claimsBox = document.getElementById('aiClaimsBox');
const claimsList = document.getElementById('aiClaimsList');
const improveBox = document.getElementById('aiImproveBox');
const improveList = document.getElementById('aiImproveList');
const misinformationBox = document.getElementById('aiMisinformationBox');
const misinformationList = document.getElementById('aiMisinformationList');

function setPublishLockState(isUnlocked, message) {
    publishButton.disabled = !isUnlocked;
    publishButton.classList.toggle('is-locked', !isUnlocked);
    publishButton.setAttribute('aria-disabled', isUnlocked ? 'false' : 'true');
    publishStatus.textContent = message;
}

function invalidateVerification(message = 'Content changed. Run AI Fact Check again. Only Auto Publish results at 81% or above unlock publishing.') {
    trustScoreInput.value = '0';
    setPublishLockState(false, message);
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
        return `<li style="margin-bottom:8px;">${line}${reason}${source}</li>`;
    }).join('');
    misinformationBox.style.display = 'block';
}

function renderImprovementSuggestions(items) {
    if (!Array.isArray(items) || items.length === 0) {
        improveList.innerHTML = '';
        improveBox.style.display = 'none';
        return;
    }

    improveList.innerHTML = items.map((item) => {
        return `<li style="margin-bottom:8px;">${escapeHtml(item)}</li>`;
    }).join('');
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
        const bg = status === 'contradicted' ? '#3a1820' : (status === 'weak' ? '#3b2a12' : '#132f22');
        const fg = status === 'contradicted' ? '#fecdd3' : (status === 'weak' ? '#fde68a' : '#bbf7d0');
        const text = escapeHtml(claim && claim.text ? claim.text : '');
        const reason = claim && claim.reason ? `<p class="text-muted" style="font-size:12px; margin:0 0 6px; line-height:1.5;">${escapeHtml(claim.reason)}</p>` : '';
        const score = Number(claim && claim.match_score ? claim.match_score : 0);
        const source = claim && claim.source ? ` · Source: ${escapeHtml(claim.source)}` : '';

        return `<div style="border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:10px 12px; background:rgba(255,255,255,0.02);">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:6px;">
                <strong style="font-size:13px; line-height:1.5;">${text}</strong>
                <span style="font-size:11px; padding:4px 8px; border-radius:999px; background:${bg}; color:${fg}; white-space:nowrap;">${label}</span>
            </div>
            ${reason}
            <p class="text-muted" style="font-size:11px; margin:0;">Match Score: ${score.toFixed(2)}${source}</p>
        </div>`;
    }).join('');
    claimsBox.style.display = 'block';
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
    button.disabled = true;
    button.textContent = 'Running AI Fact Check...';

    try {
        const response = await fetch('/api/ai-verify.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
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
        const trustScore = Math.max(0, Math.min(100, Number(data.trust_score) || 0));
        const decision = String(
            data.publish_decision ||
            (trustScore >= autoPublishThreshold
                ? 'auto_publish'
                : (trustScore >= needsReviewThreshold ? 'needs_review' : 'unreliable'))
        );
        const verdictBox = document.getElementById('aiVerdictBox');
        const sourceLabel = document.getElementById('aiSourceLabel');

        document.getElementById('trustScoreInput').value = trustScore;
        document.getElementById('aiTrustScore').textContent = `${trustScore}%`;
        document.getElementById('aiSummary').textContent = data.summary || 'Verification completed.';

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

        verdictBox.textContent = data.verdict || 'Verification completed.';
        verdictBox.style.background = decision === 'auto_publish' ? '#22c55e' : (decision === 'needs_review' ? '#f59e0b' : '#ef4444');
        verdictBox.style.color = decision === 'unreliable' ? '#fff' : (decision === 'auto_publish' ? '#000' : '#111827');
        renderClaims(data.claims || []);
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
</script>

<?php page_foot(); ?>
