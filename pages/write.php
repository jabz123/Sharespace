<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

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
$articleCtrl = new ArticleController();
$minimumTrustScore = 60;

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
        redirect('/pages/my-articles.php', 'Article not found or permission denied.');
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
            redirect('/pages/my-articles.php', null, 'Draft saved!');
        }
    } else {
        $verification = $_SESSION['article_ai_verification'] ?? null;
        $fingerprint = buildArticleVerificationFingerprint($_POST, (int)$user->id);
        $isVerificationCurrent = is_array($verification)
            && ($verification['fingerprint'] ?? '') === $fingerprint;
        $verifiedTrustScore = $isVerificationCurrent ? (int)($verification['trust_score'] ?? 0) : 0;
        $hasPassingVerification = $isVerificationCurrent
            && !empty($verification['passed'])
            && $verifiedTrustScore >= $minimumTrustScore;

        if (!$hasPassingVerification) {
            $result = ['error' => 'Run AI Fact Check and get at least 60% before publishing this article.'];
            $_POST['trust_score'] = $verifiedTrustScore;
        } else {
            $_POST['status'] = 'published';
            $_POST['trust_score'] = $verifiedTrustScore;

            if ($isEdit) {
                $result = $articleCtrl->resubmitForExpertReview($editId, $user->id, $_POST);
                if (isset($result['ok'])) {
                    unset($_SESSION['article_ai_verification']);
                    redirect('/pages/my-articles.php?filter=pending', null, 'Article sent to category experts for final verification.');
                }
            } else {
                $result = $articleCtrl->submitForExpertReview($user->id, $_POST);
                if (isset($result['ok'])) {
                    unset($_SESSION['article_ai_verification']);
                    redirect('/pages/my-articles.php?filter=pending', null, 'Article sent to category experts for final verification.');
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
    && (int)($verification['trust_score'] ?? 0) >= $minimumTrustScore;
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
$initialSourceLabel = $hasCurrentVerification
    ? trim((string)($verification['source_label'] ?? ''))
    : '';

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
<style>
body.page-write-article,
body.page-edit-article {
    background:
        radial-gradient(circle at top left, rgba(245,166,35,0.08), transparent 24%),
        linear-gradient(180deg, #060b14 0%, #09111d 100%) !important;
    color: #f5f8ff !important;
}

body.page-write-article .dashboard-layout main,
body.page-edit-article .dashboard-layout main,
body.page-write-article .page-content,
body.page-edit-article .page-content {
    background: transparent !important;
}

body.page-write-article .dash-header,
body.page-edit-article .dash-header {
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 22%),
        linear-gradient(180deg, rgba(8,14,26,0.94) 0%, rgba(8,14,26,0.84) 100%) !important;
    border-bottom: 1px solid rgba(245,166,35,0.14) !important;
}

body.page-write-article .dash-title,
body.page-edit-article .dash-title,
body.page-write-article .dash-subtitle,
body.page-edit-article .dash-subtitle,
body.page-write-article .form-group label,
body.page-edit-article .form-group label,
body.page-write-article .ai-panel h3,
body.page-edit-article .ai-panel h3,
body.page-write-article .ai-panel p,
body.page-edit-article .ai-panel p,
body.page-write-article .text-muted,
body.page-edit-article .text-muted {
    color: #dbe6f6 !important;
}

.write-editor-shell {
    max-width: 1380px;
    padding: 34px 28px 48px !important;
    display: flex !important;
    gap: 26px !important;
    align-items: flex-start !important;
}

.write-editor-form {
    flex: 1 1 0;
    max-width: 920px;
    min-width: 0;
    padding: 30px !important;
    border-radius: 28px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 20%),
        linear-gradient(180deg, rgba(11,17,31,0.98) 0%, rgba(9,14,25,1) 100%) !important;
    box-shadow: 0 22px 48px rgba(0,0,0,0.22) !important;
}

.write-editor-form .form-group {
    margin-bottom: 24px !important;
}

body.page-write-article .write-editor-form input[type=text],
body.page-write-article .write-editor-form input[type=url],
body.page-write-article .write-editor-form select,
body.page-write-article .write-editor-form textarea,
body.page-edit-article .write-editor-form input[type=text],
body.page-edit-article .write-editor-form input[type=url],
body.page-edit-article .write-editor-form select,
body.page-edit-article .write-editor-form textarea {
    background: rgba(255,255,255,0.03) !important;
    border: 1px solid rgba(255,255,255,0.10) !important;
    color: #f5f8ff !important;
    border-radius: 14px !important;
    padding: 14px 16px !important;
    font-size: 15px !important;
}

body.page-write-article .write-editor-form input::placeholder,
body.page-write-article .write-editor-form textarea::placeholder,
body.page-edit-article .write-editor-form input::placeholder,
body.page-edit-article .write-editor-form textarea::placeholder {
    color: #7f93b2 !important;
}

body.page-write-article .write-editor-form select option,
body.page-edit-article .write-editor-form select option {
    color: #0b1628;
}

.write-content-input {
    min-height: 380px !important;
    line-height: 1.75 !important;
}

.write-editor-shell .image-preview {
    min-height: 220px;
    border-radius: 18px !important;
    border: 1px dashed rgba(255,255,255,0.16) !important;
    background: rgba(255,255,255,0.03) !important;
    color: #dbe6f6 !important;
}

.write-editor-shell .image-preview img {
    width: 100%;
    height: 220px;
    object-fit: cover;
    border-radius: 16px;
}

.write-editor-shell .ai-panel {
    flex: 0 0 360px !important;
    max-width: 360px !important;
}

.write-editor-shell .ai-verification-card {
    position: sticky;
    top: 24px;
    width: 100%;
    min-height: 720px;
    padding: 24px !important;
    border-radius: 26px !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.07), transparent 20%),
        linear-gradient(180deg, rgba(11,17,31,0.98) 0%, rgba(9,14,25,1) 100%) !important;
    box-shadow: 0 22px 48px rgba(0,0,0,0.22) !important;
}

.write-editor-shell .btn-ai,
.write-editor-shell .btn-draft,
.write-editor-shell .btn-publish {
    min-height: 50px;
    border-radius: 14px !important;
    font-weight: 700 !important;
    font-size: 15px !important;
}

.write-editor-shell .btn-ai {
    background: linear-gradient(135deg, #f4a321 0%, #ffca61 100%) !important;
    color: #08111f !important;
}

.write-editor-shell .btn-draft {
    background: rgba(255,255,255,0.05) !important;
    color: #dbe6f6 !important;
    border: 1px solid rgba(255,255,255,0.12) !important;
}

.write-editor-shell .btn-publish {
    background: linear-gradient(135deg, #203a67 0%, #2f4d83 100%) !important;
    color: #ffffff !important;
    border: 1px solid rgba(146,176,230,0.20) !important;
    transition: opacity 0.2s ease, transform 0.2s ease !important;
}

.write-editor-shell .btn-publish.is-locked,
.write-editor-shell .btn-publish:disabled {
    opacity: 0.38 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    box-shadow: none !important;
    filter: saturate(0.65);
}

.write-editor-shell .publish-status {
    margin: 12px 0 0 !important;
    font-size: 13px !important;
    line-height: 1.45 !important;
}

.write-editor-shell .progress-bar {
    background: rgba(255,255,255,0.08) !important;
}

.write-editor-shell .progress-bar > div {
    background: linear-gradient(135deg, #f4a321 0%, #ffca61 100%) !important;
}

.write-editor-shell .write-actions {
    margin-top: 24px !important;
    gap: 12px !important;
}

.write-editor-shell .write-actions-row {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}

@media (max-width: 1100px) {
    .write-editor-shell {
        flex-direction: column !important;
        padding: 22px 18px 32px !important;
    }

    .write-editor-form,
    .write-editor-shell .ai-panel {
        max-width: 100% !important;
        width: 100% !important;
    }

    .write-editor-shell .ai-verification-card {
        position: static;
        min-height: auto;
    }

    .write-editor-shell .write-actions-row {
        grid-template-columns: 1fr;
    }
}
</style>

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
            <form method="POST" id="write-form" enctype="multipart/form-data" class="write-editor-form">
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
                        <?= $verificationPassed
                            ? 'AI verification passed. Publishing will send this article to category experts for final approval.'
                            : 'Run AI Fact Check and score at least 60% to unlock submission for category expert review.' ?>
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
                            You can include a reference link such as a The Straits Times article URL for extra context.
                        </p>
                    </div>

                    <div id="ai-loading" style="display:none;">
                        <p class="text-muted" style="margin-top:10px;">
                            Running n8n verification workflow...
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

                        <p>Factual Accuracy</p>
                        <div class="progress-bar"><div id="metricFactualAccuracy" style="width:<?= max(0, min(100, (int)($initialMetrics['factual_accuracy'] ?? 0))) ?>%"></div></div>

                        <p>Source Quality</p>
                        <div class="progress-bar"><div id="metricSourceQuality" style="width:<?= max(0, min(100, (int)($initialMetrics['source_quality'] ?? 0))) ?>%"></div></div>

                        <p>Bias Detection</p>
                        <div class="progress-bar"><div id="metricBiasDetection" style="width:<?= max(0, min(100, (int)($initialMetrics['bias_detection'] ?? 0))) ?>%"></div></div>

                        <p>Logical Consistency</p>
                        <div class="progress-bar"><div id="metricLogicalConsistency" style="width:<?= max(0, min(100, (int)($initialMetrics['logical_consistency'] ?? 0))) ?>%"></div></div>

                        <p>Completeness</p>
                        <div class="progress-bar"><div id="metricCompleteness" style="width:<?= max(0, min(100, (int)($initialMetrics['completeness'] ?? 0))) ?>%"></div></div>

                        <div
                            class="ai-success-box"
                            id="aiVerdictBox"
                            style="<?= $hasCurrentVerification ? 'background:' . ($verificationPassed ? '#22c55e' : '#f59e0b') . ';color:' . ($verificationPassed ? '#000' : '#111827') . ';' : '' ?>"
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

const verificationThreshold = <?= (int)$minimumTrustScore ?>;
const publishButton = document.getElementById('publishButton');
const publishStatus = document.getElementById('publishStatus');
const trustScoreInput = document.getElementById('trustScoreInput');
const initialPublishUnlocked = <?= $verificationPassed ? 'true' : 'false' ?>;

function setPublishLockState(isUnlocked, message) {
    publishButton.disabled = !isUnlocked;
    publishButton.classList.toggle('is-locked', !isUnlocked);
    publishButton.setAttribute('aria-disabled', isUnlocked ? 'false' : 'true');
    publishStatus.textContent = message;
}

function invalidateVerification(message = 'Content changed. Run AI Fact Check again and score at least 60% to unlock publishing.') {
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
            throw new Error(data.error || 'AI verification failed.');
        }

        const metrics = data.metrics || {};
        const trustScore = Math.max(0, Math.min(100, Number(data.trust_score) || 0));
        const verdictBox = document.getElementById('aiVerdictBox');
        const sourceLabel = document.getElementById('aiSourceLabel');

        document.getElementById('trustScoreInput').value = trustScore;
        document.getElementById('aiTrustScore').textContent = `${trustScore}%`;
        document.getElementById('aiSummary').textContent = data.summary || 'Verification completed.';

        if (data.source_label) {
            sourceLabel.style.display = 'block';
            sourceLabel.textContent = data.source_label;
        } else if (sourceUrl) {
            sourceLabel.style.display = 'block';
            sourceLabel.innerHTML = `Reference used: <a href="${escapeHtml(sourceUrl)}" target="_blank" rel="noopener noreferrer">${escapeHtml(sourceUrl)}</a>`;
        } else {
            sourceLabel.style.display = 'none';
            sourceLabel.textContent = '';
        }

        setMetricBar('metricFactualAccuracy', metrics.factual_accuracy);
        setMetricBar('metricSourceQuality', metrics.source_quality);
        setMetricBar('metricBiasDetection', metrics.bias_detection);
        setMetricBar('metricLogicalConsistency', metrics.logical_consistency);
        setMetricBar('metricCompleteness', metrics.completeness);

        verdictBox.textContent = data.verdict || 'Verification completed.';
        verdictBox.style.background = trustScore >= verificationThreshold ? '#22c55e' : '#f59e0b';
        verdictBox.style.color = trustScore >= verificationThreshold ? '#000' : '#111827';

        if (trustScore >= verificationThreshold) {
            setPublishLockState(true, 'AI verification passed. Publishing will send this article to category experts for final approval.');
        } else {
            setPublishLockState(false, 'Trust score is below 60%. Submission stays locked until the result is green.');
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
    initialPublishUnlocked
        ? 'AI verification passed. Publishing will send this article to category experts for final approval.'
        : 'Run AI Fact Check and score at least 60% to unlock submission for category expert review.'
);
</script>

<?php page_foot(); ?>
