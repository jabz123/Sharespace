<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/article_flag_rules.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/controllers/CommentController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();
$commentCtrl = new CommentController();

$auth->requireAuth();
$user = $auth->currentUser();
$isSystemAdmin = $user->role === 'system_admin';

$id = (int)($_GET['id'] ?? 0);
$article = $id ? $articleCtrl->getById($id) : null;

if (!$article) {
    if ($user->role === 'system_admin') {
        redirect('/pages/admin-dashboard.php', 'Article not found or not published.');
    }

    redirect('/dashboard.php', 'Article not found.');
}

$premiumPlan = DB::first(
    "SELECT price, price_suffix
     FROM landing_pricing_plans
     WHERE name = 'Premium'
     LIMIT 1"
);

$isSaved = DB::first(
    "SELECT id FROM saved_articles WHERE user_id = ? AND article_id = ?",
    [$user->id, $article->id]
) ? true : false;

$alreadyFlagged = DB::first(
    "SELECT id FROM article_flags WHERE user_id = ? AND article_id = ?",
    [$user->id, $article->id]
) !== null;

DB::execute(
    "INSERT IGNORE INTO article_views (user_id, article_id)
     VALUES (?, ?)",
    [$user->id, $article->id]
);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $return = $_GET['return'] ?? '';
    $url = '/pages/article.php?id=' . $article->id;

    if ($return) {
        $url .= '&return=' . urlencode($return);
    }

    $url .= '#comments';

    if ($action === 'comment') {
        $result = $commentCtrl->post($article->id, $user->id, $_POST['comment_body'] ?? '');
        if (!empty($result['error'])) {
            flash_set('flash_error', $result['error']);
        }
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'delete_comment') {
        $commentCtrl->delete((int)($_POST['comment_id'] ?? 0), $user->id);
        header('Location: ' . $url);
        exit;
    }
}

$comments = $commentCtrl->getForArticle($article->id);
$isAuthor = $user->id === $article->authorId;
$flagReasonOptions = article_flag_reason_options();

$returnUrl = $_GET['return'] ?? '';
if ($returnUrl && !str_starts_with($returnUrl, '/')) {
    $returnUrl = '';
}

if (!$returnUrl) {
    $returnUrl = $user->role === 'system_admin'
        ? '/pages/admin-dashboard.php'
        : '/dashboard.php';
}

page_head($article->title, $isSystemAdmin);
?>
<div class="dashboard-layout<?= $isSystemAdmin ? ' article-admin-shell' : ' user-dashboard-shell' ?>">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header(htmlspecialchars($article->categoryName), 'Article'); ?>

        <div class="page-content">
            <div class="article-detail">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn btn-ghost btn-sm">Back</a>

                        <?php if ($isAuthor): ?>
                            <a href="/pages/write.php?id=<?= $article->id ?>" class="btn btn-ghost btn-sm">Edit</a>
                        <?php endif; ?>
                    </div>

                    <?php if ($user->role !== 'system_admin'): ?>
                        <div class="flex items-center gap-4" style="position: relative;">
                            <button class="icon-btn <?= $isSaved ? 'active' : '' ?>" id="saveBtn" title="Save">
                                <img id="saveIcon" src="<?= $isSaved ? '/public/icons/bookmarkactive.png' : '/public/icons/Bookmark.png' ?>"
                                    data-default="/public/icons/Bookmark.png"
                                    data-active="/public/icons/bookmarkactive.png"
                                    alt="Save article">
                            </button>

                            <button class="icon-btn <?= $alreadyFlagged ? 'flagged' : '' ?>" id="flagBtn" data-article-id="<?= $article->id ?>"
                                <?= $alreadyFlagged ? 'disabled' : '' ?> title="<?= $alreadyFlagged ? 'Already flagged' : 'Flag' ?>">
                                <img src="/public/icons/flag.png" alt="Flag article">
                            </button>
                            <div style="position: relative;">
                            <button class="icon-btn" id="shareBtn" title="Share">
                                <img src="/public/icons/share.png" alt="Share article">
                            </button>
                            </div>  
                            <div id="shareMenu" class="share-menu hidden">
                            <button class="share-option" data-platform="whatsapp">WhatsApp</button>
                            <button class="share-option" data-platform="telegram">Telegram</button>
                            <button class="share-option" data-platform="twitter">Twitter</button>
                            <button class="share-option" data-platform="email">Email</button>
                            <button class="share-option" data-platform="print">Print</button>
                            <button class="share-option" data-platform="copy">Copy Link</button>
                            </div>
                                                </div>
                    <?php endif; ?>
                </div>

                <div class="flex justify-between items-center mb-3">
                    <span class="category-tag <?= category_theme_class($article->categoryName) ?>"><?= htmlspecialchars($article->categoryName) ?></span>
                    <?= trust_badge($article->trustScore) ?>
                </div>

                <h1 style="font-size:32px;font-weight:800;font-family:Georgia,serif;line-height:1.2;margin-bottom:16px">
                    <?= htmlspecialchars($article->title) ?>
                </h1>

                <div class="article-meta">
                    <div class="author-avatar" style="width:42px;height:42px;font-size:16px"><?= htmlspecialchars($article->authorInitial()) ?></div>
                    <div>
                        <p style="font-weight:600;font-size:14px"><?= htmlspecialchars($article->authorName) ?></p>
                        <p class="text-sm text-muted"><?= date('F j, Y g:i A', strtotime($article->publishedAt)) ?></p>
                    </div>
                </div>

                <?php if (!empty($article->imagePath)): ?>
                    <div class="article-banner">
                        <img src="/public/<?= htmlspecialchars($article->imagePath) ?>" alt="Article image">
                    </div>
                <?php endif; ?>

                <?php if (!empty($article->excerpt)): ?>
                    <div class="article-summary">
                        <h3 class="summary-title">Article Description</h3>
                        <p class="summary-text"><?= htmlspecialchars($article->excerpt) ?></p>
                    </div>

                    <div class="ai-overview-panel" id="aiOverviewPanel" style="margin-top:40px;">
                        <div class="ai-overview-header">
                            <div class="ai-overview-title">
                                <h3>AI Overview</h3>
                            </div>
                            <button id="aiOverviewBtn" class="btn btn-secondary btn-sm" data-article-id="<?= $article->id ?>">
                                Generate
                            </button>
                        </div>
                        <div id="aiOverviewContent"></div>
                    </div>
                <?php endif; ?>

                <h3 class="article-content-title">Article</h3>
                <div class="tts-bar">
                    <button class="tts-btn play" onclick="readArticle()">Read Aloud</button>
                    <button class="tts-btn stop" onclick="stopReading()">Stop Audio</button>
                </div>

                <div class="article-body">
                    <?php
                    $isPremiumUser = in_array($user->role, ['premium', 'system_admin', 'category_admin'], true);
                    $isPremiumArticle = !empty($article->imagePath);
                    ?>

                    <?php if ($isPremiumUser || !$isPremiumArticle): ?>
                        <?= $article->renderContent() ?>
                    <?php else: ?>
                        <?= $article->renderContentPreview(2) ?>

                        <div class="paywall-box">
                            <div class="paywall-title">
                                <img src="/public/icons/premiumlockicon2.png" alt="">
                                <span>Unlock the full story</span>
                            </div>
                            <p>
                                Get complete access to this article and all premium content.
                                Stay informed with deeper insights and trusted reporting.
                            </p>
                            <div class="paywall-price">
                                <span class="price-main"><?= $premiumPlan['price'] ?></span>
                                <span class="price-suffix"><?= $premiumPlan['price_suffix'] ?></span>
                            </div>
                            <a href="/subscribe.php" class="btn btn-primary">Upgrade to Premium</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div id="comments" style="margin-top:48px;padding-top:32px;border-top:2px solid var(--border)">
                <div class="comments-container">
                    <h2 style="font-size:20px;font-weight:700;font-family:Georgia,serif;margin-bottom:24px">
                        Comments <span style="font-size:14px;font-weight:400;color:var(--muted)">(<?= count($comments) ?>)</span>
                    </h2>

                    <div class="card" style="margin-bottom:28px">
                        <?php flash_messages(); ?>

                        <form method="POST" id="commentForm">
                            <input type="hidden" name="action" value="comment">
                            <div class="form-group" style="margin-bottom:12px">
                                <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">
                                    Leave a comment as <span style="color:var(--primary)"><?= htmlspecialchars($user->fullName) ?></span>
                                </label>
                                <textarea name="comment_body" id="commentBody" rows="3"
                                    placeholder="Share your thoughts on this article..."
                                    style="width:100%;resize:vertical"
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                        </form>
                    </div>

                    <?php if (empty($comments)): ?>
                        <p class="text-muted" style="text-align:center;padding:32px 0">
                            No comments yet. Be the first to share your thoughts.
                        </p>
                    <?php else: ?>
                        <div style="display:flex;flex-direction:column;gap:16px">
                            <?php foreach ($comments as $comment):
                                $canDelete = $comment->userId === $user->id;
                            ?>
                                <div class="card" style="padding:16px 20px">
                                    <div class="flex items-center gap-3" style="margin-bottom:10px">
                                        <div class="author-avatar" style="width:34px;height:34px;font-size:13px;flex-shrink:0"><?= htmlspecialchars($comment->initial()) ?></div>
                                        <div style="flex:1">
                                            <span style="font-weight:600;font-size:14px"><?= htmlspecialchars($comment->commenterName) ?></span>
                                            <span class="text-muted" style="font-size:12px;margin-left:8px"><?= relative_time($comment->createdAt) ?></span>
                                        </div>
                                        <?php if ($canDelete): ?>
                                            <form method="POST" style="margin:0">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <input type="hidden" name="comment_id" value="<?= $comment->id ?>">
                                                <button type="submit" class="btn btn-ghost btn-sm"
                                                    style="font-size:11px;padding:2px 8px;color:var(--muted)"
                                                    onclick="return confirm('Delete this comment?')">Remove</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                    <p style="font-size:14px;line-height:1.6;color:var(--fg);margin:0"><?= nl2br(htmlspecialchars($comment->content)) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<div id="flagModal" class="modal hidden">
    <div class="modal-overlay"></div>

    <div class="modal-content">
        <h2>Report Article</h2>
        <p class="modal-sub">Help us keep SharedSpace safe and reliable.</p>

        <form id="flagForm">
            <input type="hidden" name="article_id" value="<?= $article->id ?>">

            <div class="form-group">
                <label>Select a reason</label>
                <?php foreach ($flagReasonOptions as $index => $flagReason): ?>
                    <label>
                        <input
                            type="radio"
                            name="reason"
                            value="<?= htmlspecialchars($flagReason) ?>"
                            <?= $index === 0 ? 'required' : '' ?>>
                        <?= htmlspecialchars($flagReason) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label>Tell us more</label>
                <textarea
                    name="details"
                    id="flagDetails"
                    rows="3"
                    placeholder="Please explain clearly what is wrong with this article."
                    maxlength="400"
                    required></textarea>
                <small id="charCount">0/400</small>
                <small>Use 20 to 400 characters, more than 3 words, and avoid vague, heavily misspelled, or symbol-only text.</small>
            </div>

            <div class="modal-actions">
                <button type="button" id="closeModal" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-danger">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
    const saveBtn = document.getElementById('saveBtn');
    const saveIcon = document.getElementById('saveIcon');

    if (saveBtn && saveIcon) {
        saveBtn.addEventListener('click', function() {
            fetch('/actions/toggle-save.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: 'article_id=<?= $article->id ?>'
                })
                .then(res => res.json())
                .then(data => {
                    saveIcon.src = data.saved ? saveIcon.dataset.active : saveIcon.dataset.default;
                    saveBtn.classList.toggle('active', data.saved);
                });
        });
    }
</script>

<script>
    (function() {
        const btn = document.getElementById('aiOverviewBtn');
        const content = document.getElementById('aiOverviewContent');
        if (!btn) return;

        btn.addEventListener('click', async function() {
            const articleId = btn.dataset.articleId;

            btn.disabled = true;
            btn.textContent = 'Generating...';
            content.innerHTML = '<p class="ai-loading">Analysing article...</p>';

            try {
                const res = await fetch('/api/ai-overview.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        article_id: parseInt(articleId, 10)
                    })
                });
                const data = await res.json();

                if (data.error) {
                    content.innerHTML = `<p class="alert alert-error">${data.error}</p>`;
                    return;
                }

                const points = (data.key_points || [])
                    .map(point => `<li>${point}</li>`)
                    .join('');

                content.innerHTML = `
                <div class="ai-overview-body">
                    <div class="ai-meta">
                        <span class="ai-badge">${data.tone}</span>
                        <span class="ai-badge ai-badge-muted">${data.read_time}</span>
                    </div>
                    <div class="ai-section">
                        <h4>Summary</h4>
                        <p>${data.summary}</p>
                    </div>
                    <div class="ai-section">
                        <h4>Key Points</h4>
                        <ul>${points}</ul>
                    </div>
                </div>
            `;
            } catch (error) {
                content.innerHTML = `<p class="alert alert-error">Network error: ${error.message}</p>`;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Regenerate';
            }
        });
    })();
</script>

<?php page_foot(); ?>
