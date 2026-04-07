<?php
// page that displays a full article and its comments
// retrieves article and comment data from the controllers
// checks user authentication and premium access before showing the article
// allows users to post new comments or delete their own comments
// uses layout helper functions to render the page ui

//fetches article and comment shit from controllers
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/controllers/CommentController.php';

//initialise controllers
$auth        = new AuthController();
$articleCtrl = new ArticleController();
$commentCtrl = new CommentController();

$auth->requireAuth();
$user = $auth->currentUser();

$id      = (int)($_GET['id'] ?? 0);
$article = $id ? $articleCtrl->getById($id) : null;

// 🔥 Fetch premium pricing from database
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

// load categories for the "wrong category" flag option (exclude current article category)
$flagCategories = DB::query(
    "SELECT id, name FROM categories WHERE id != ? ORDER BY name",
    [$article->category_id ?? 0]
);

// record article view
// insert ignore prevents duplicate views from same user
DB::execute(
    "INSERT IGNORE INTO article_views (user_id, article_id)
     VALUES (?, ?)",
    [$user->id, $article->id]
);

if (!$article) {

    if ($user->role === 'system_admin') {
        redirect('/pages/admin-dashboard.php', 'Article not found or not published.');
    }

    redirect('/dashboard.php', 'Article not found.');
}


// post and delete comment logic 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // preserve return URL
    $return = $_GET['return'] ?? '';

    // base URL
    $url = '/pages/article.php?id=' . $article->id;

    // append return if exists
    if ($return) {
        $url .= '&return=' . urlencode($return);
    }

    // always go back to comments section
    $url .= '#comments';

    if ($action === 'comment') {
        $commentCtrl->post($article->id, $user->id, $_POST['comment_body'] ?? '');
        header('Location: ' . $url);
        exit;
    }

    if ($action === 'delete_comment') {
        $commentCtrl->delete((int)($_POST['comment_id'] ?? 0), $user->id);
        header('Location: ' . $url);
        exit;
    }
}

//get comments for article, also checks if current user can delete each comment
$comments = $commentCtrl->getForArticle($article->id);
$isAuthor = $user->id === $article->authorId;

page_head($article->title);
?>
<div class="dashboard-layout">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header(htmlspecialchars($article->categoryName), 'Article'); ?>
        <div class="page-content">
            <div class="article-detail">

                <div class="flex items-center justify-between mb-6">

                    <?php
                    $returnUrl = $_GET['return'] ?? '';

                    // security check (optional but recommended)
                    if ($returnUrl && !str_starts_with($returnUrl, '/')) {
                        $returnUrl = '';
                    }

                    // fallback based on role
                    if (!$returnUrl) {
                        if ($user->role === 'system_admin') {
                            $returnUrl = '/pages/admin-dashboard.php';
                        } else {
                            $returnUrl = '/dashboard.php';
                        }
                    }
                    ?>

                    <!-- LEFT SIDE -->
                    <div class="flex items-center gap-2">
                        <a href="<?= htmlspecialchars($returnUrl) ?>" class="btn btn-ghost btn-sm">← Back</a>

                        <?php if ($isAuthor): ?>
                            <a href="/pages/write.php?id=<?= $article->id ?>" class="btn btn-ghost btn-sm">✏️ Edit</a>
                        <?php endif; ?>
                    </div>

                    <!-- RIGHT SIDE -->
                    <?php if ($user->role !== 'system_admin'): ?>
                        <div class="flex items-center gap-4">

                            <button class="icon-btn" id="saveBtn" title="Save">
                                <img id="saveIcon" src="<?= $isSaved ? '/public/icons/bookmarkactive.png' : '/public/icons/Bookmark.png' ?>"
                                    data-default="/public/icons/Bookmark.png"
                                    data-active="/public/icons/bookmarkactive.png">
                            </button>

                            <button class="icon-btn <?= $alreadyFlagged ? 'flagged' : '' ?>" id="flagBtn" data-article-id="<?= $article->id ?>"
                                <?= $alreadyFlagged ? 'disabled' : '' ?> title="<?= $alreadyFlagged ? 'Already flagged' : 'Flag' ?>">
                                <img src="/public/icons/flag.png" alt="Flag">
                            </button>

                            <button class="icon-btn" id="shareBtn" title="Share">
                                <img src="/public/icons/share.png" alt="Share">
                            </button>

                        </div>
                    <?php endif; ?>

                </div>

                <div class="flex justify-between items-center mb-3">
                    <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
                    <?= trust_badge($article->trustScore) ?>
                </div>

                <h1 style="font-size:32px;font-weight:800;font-family:Georgia,serif;line-height:1.2;margin-bottom:16px">
                    <?= htmlspecialchars($article->title) ?>
                </h1>
                <div class="article-meta">
                    <div class="author-avatar" style="width:42px;height:42px;font-size:16px"><?= htmlspecialchars($article->authorInitial()) ?></div>
                    <div>
                        <p style="font-weight:600;font-size:14px"><?= htmlspecialchars($article->authorName) ?></p>
                        <p class="text-sm text-muted">🕐 <?= date('F j, Y g:i A', strtotime($article->publishedAt)) ?></p>
                    </div>
                </div>
                <?php if (!empty($article->imagePath)): ?>

                    <div class="article-banner">
                        <img src="/public/<?= htmlspecialchars($article->imagePath) ?>" alt="Article Image">
                    </div>

                <?php endif; ?>
                <?php if (!empty($article->excerpt)): ?>

                    <div class="article-summary">
                        <h3 class="summary-title">Brief Summary</h3>
                        <p class="summary-text">
                            <?= htmlspecialchars($article->excerpt) ?>
                        </p>
                    </div>
                    <!-- AI Overview shit, new. -->
                    <div class="ai-overview-panel" id="aiOverviewPanel" style="margin-top:40px;">
                        <div class="ai-overview-header">
                            <div class="ai-overview-title">
                                <span class="ai-icon">&#10022;</span>
                                <h3>AI Overview</h3>
                            </div>
                            <button id="aiOverviewBtn" class="btn btn-secondary btn-sm"
                                data-article-id="<?= $article->id ?>">
                                Generate
                            </button>
                        </div>
                        <div id="aiOverviewContent"></div>
                    </div>

                <?php endif; ?>

                <h3 class="article-content-title">Article</h3>
               <div class="tts-bar">
                <button class="tts-btn play" onclick="readArticle()">▶ Listen</button>
                <button class="tts-btn stop" onclick="stopReading()">⏹ Stop</button>
                </div>

                <div class="article-body">

                    <?php
                    //  Show full content if:
                    //  user is premium OR article is NOT premium (no image)
                    $isPremiumUser = $user->role === 'premium' || $user->role === 'system_admin' || $user->role === 'category_admin';
                    $isPremiumArticle = !empty($article->imagePath);
                    ?>

                    <?php if ($isPremiumUser || !$isPremiumArticle): ?>

                        <!-- Premium users see FULL content -->
                        <?= $article->renderContent() ?>

                    <?php else: ?>

                        <!--  Free users see PREVIEW only -->
                        <?= $article->renderContentPreview(2) ?>

                        <!--  PAYWALL SECTION -->
                        <div class="paywall-box">

                            <div class="paywall-title">
                                <img src="/public/icons/premiumlockicon2.png" alt="">
                                <span>Unlock the full story</span>
                            </div>
                            <p2>
                                Get complete access to this article and all premium content.
                                Stay informed with deeper insights and trusted reporting.
                            </p2>
                            <div class="paywall-price">
                                <span class="price-main">
                                    <?= ($premiumPlan['price']) ?>
                                </span>
                                <span class="price-suffix">
                                    <?= $premiumPlan['price_suffix'] ?>
                                </span>
                            </div>
                            <a href="/subscribe.php" class="btn btn-primary">
                                Upgrade to Premium
                            </a>
                        </div>

                    <?php endif; ?>

                </div>

            </div>







            <div id="comments" style="margin-top:48px;padding-top:32px;border-top:2px solid var(--border)">
                <div class="comments-container">

                    <h2 style="font-size:20px;font-weight:700;font-family:Georgia,serif;margin-bottom:24px">
                        💬 Comments <span style="font-size:14px;font-weight:400;color:var(--muted)">(<?= count($comments) ?>)</span>
                    </h2>


                    <div class="card" style="margin-bottom:28px">
                        <form method="POST">
                            <input type="hidden" name="action" value="comment">
                            <div class="form-group" style="margin-bottom:12px">
                                <label style="font-size:13px;font-weight:600;margin-bottom:6px;display:block">
                                    Leave a comment as <span style="color:var(--primary)"><?= htmlspecialchars($user->fullName) ?></span>
                                </label>
                                <textarea name="comment_body" rows="3"
                                    placeholder="Share your thoughts on this article…"
                                    style="width:100%;resize:vertical"
                                    required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Post Comment</button>
                        </form>
                    </div>

                    <!--comment list-->
                    <?php if (empty($comments)): ?>
                        <p class="text-muted" style="text-align:center;padding:32px 0">
                            No comments yet. Be the first to share your thoughts!
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
                                                    onclick="return confirm('Delete this comment?')">✕</button>
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

<!-- FLAG MODAL FORM -->
<div id="flagModal" class="modal hidden">
    <div class="modal-overlay"></div>

    <div class="modal-content">
        <h2>🚩 Report Article</h2>
        <p class="modal-sub">Help us keep SharedSpace safe and reliable.</p>

        <form id="flagForm">
            <input type="hidden" name="article_id" value="<?= $article->id ?>">

            <!-- Reasons -->
            <div class="form-group">
                <label>Select a reason</label>

                <label><input type="radio" name="reason" value="INAPPROPRIATE_LANGUAGE" required> Inappropriate language</label>
                <label><input type="radio" name="reason" value="MISINFORMATION"> Misinformation</label>
                <label><input type="radio" name="reason" value="HATE_SPEECH"> Hate speech</label>
                <label><input type="radio" name="reason" value="VIOLENCE"> Violence</label>
                <label><input type="radio" name="reason" value="ADVERTISING"> Advertising</label>
                <label><input type="radio" name="reason" value="WRONG_CATEGORY"> Wrong category</label>
            </div>

            <!-- Category selector — shown only for WRONG_CATEGORY -->
            <div class="form-group" id="categoryGroup" style="display:none">
                <label for="suggestedCategory">Suggest the correct category</label>
                <select name="suggested_category_id" id="suggestedCategory">
                    <option value="">— Select a category —</option>
                    <?php foreach ($flagCategories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Additional info -->
            <div class="form-group" id="detailsGroup">
                <label>Tell us more</label>
                <textarea
                    name="details"
                    id="flagDetails"
                    rows="3"
                    placeholder="Provide more details..."
                    maxlength="100"
                    required></textarea>
                <small id="charCount">0/100</small>
            </div>

            <!-- Actions -->
            <div class="modal-actions">
                <button type="button" id="closeModal" class="btn btn-ghost">Cancel</button>
                <button type="submit" class="btn btn-danger">Submit Report</button>
            </div>
        </form>
    </div>
</div>

<script>
    const saveBtn = document.getElementById("saveBtn");
    const saveIcon = document.getElementById("saveIcon");

    saveBtn.addEventListener("click", function() {

        fetch('/actions/toggle-save.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'article_id=<?= $article->id ?>'
            })
            .then(res => res.json())
            .then(data => {
                if (data.saved) {
                    saveIcon.src = saveIcon.dataset.active;
                } else {
                    saveIcon.src = saveIcon.dataset.default;
                }
            });

    });
</script>

<!--script for ai overview shit -->
<script>
    (function() {
        const btn = document.getElementById('aiOverviewBtn');
        const content = document.getElementById('aiOverviewContent');
        if (!btn) return;

        btn.addEventListener('click', async function() {
            const articleId = btn.dataset.articleId;

            btn.disabled = true;
            btn.textContent = 'Generating…';
            content.innerHTML = '<p class="ai-loading">Analysing article…</p>';

            try {
                const res = await fetch('/api/ai-overview.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        article_id: parseInt(articleId)
                    })
                });
                const data = await res.json();

                if (data.error) {
                    content.innerHTML = `<p class="alert alert-error">${data.error}</p>`;
                    return;
                }

                const points = (data.key_points || [])
                    .map(p => `<li>${p}</li>`)
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
            } catch (e) {
                content.innerHTML = `<p class="alert alert-error">Network error: ${e.message}</p>`;
            } finally {
                btn.disabled = false;
                btn.textContent = 'Regenerate';
            }
        });
    })();
</script>

<?php page_foot(); ?>