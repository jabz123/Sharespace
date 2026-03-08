<?php

//data is all from entities
//shared layout functions for rendering page head, sidebar, flash messages, all that shit

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ArticleController.php';
require_once __DIR__ . '/controllers/CommentController.php';
require_once __DIR__ . '/textlimit.php';

function page_head(string $title): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) ?> – SharedSpace</title>
    <link rel="stylesheet" href="/public/css/app.css" />
</head>
<body>
<?php }

function page_foot(): void { ?>
    <script src="/public/js/app.js"></script>
</body>
</html>
<?php }


//receives user entity from authcontroller to render sidebar with user info and navigation links
//sidebar navigation
//will add more shit here as time passes
function sidebar(User $user): void {
    $path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $links = [
        ['href' => '/dashboard.php',         'icon' => '🏠', 'label' => 'Home'],
        ['href' => '/pages/browse.php',      'icon' => '👁', 'label' => 'Browse Articles'],
        ['href' => '/pages/my-articles.php', 'icon' => '📄', 'label' => 'My Articles'],
        ['href' => '/pages/write.php',       'icon' => '📝', 'label' => 'Write Article'],
        
    ];
    ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="/dashboard.php" class="logo-link">
            <div class="logo-icon">📰</div>
            <span class="logo-text">SharedSpace</span>
        </a>
    </div>

    <div class="sidebar-user">
        <div class="user-avatar"><?= htmlspecialchars($user->initial()) ?></div>
        <div class="user-info">
            <p class="user-name"><?= htmlspecialchars($user->fullName) ?></p>

             <?php if ($user->role === 'premium'): ?>
                <span class="role-badge premium">Premium</span>
            <?php else: ?>
            <span class="role-badge free">Free</span>
             <?php endif; ?>
          
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
        <?php foreach ($links as $link): ?>
            <li>
                <a href="<?= $link['href'] ?>"
                   class="nav-link <?= $path === $link['href'] ? 'active' : '' ?>">
                    <span><?= $link['icon'] ?></span>
                    <?= htmlspecialchars($link['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="/logout.php" class="nav-link logout">🚪 Sign Out</a>
    </div>
</aside>
<?php }

//reusable dashboard header with title and optional subtitle.
function dash_header(string $title, string $subtitle = ''): void { ?>
<header class="dash-header">
    <div>
        <h1 class="dash-title"><?= htmlspecialchars($title) ?></h1>
        <?php if ($subtitle): ?>
        <p class="dash-subtitle"><?= htmlspecialchars($subtitle) ?></p>
        <?php endif; ?>
    </div>
</header>
<?php }

//displays success or error messages set in the session and then clears them.
function flash_messages(): void {
    $err = flash('flash_error');
    $ok  = flash('flash_success');
    if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif;
    if ($ok):  ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif;
}

//can set the trust badge colour shit here
function trust_badge(int $score): string {
    $cls = $score >= 80 ? 'high' : ($score >= 60 ? 'mid' : 'low');
    return "<span class=\"trust-badge trust-{$cls}\">{$score}%</span>";
}

//makes the timestamp into like just now or how many hours or days ago
function relative_time(string $dateStr): string { 
    $diff  = time() - strtotime($dateStr);
    $hours = (int)($diff / 3600);
    if ($hours < 1)  return 'Just now';
    if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    $days = (int)($hours / 24);
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}


// article card logic
//receives entity from articlecontroller
function article_card(Article $article, User $user): void {

    $url = '/pages/article.php?id=' . $article->id;

    $isPremiumUser = $user->role === 'premium';

    $hasImage = !empty($article->imagePath);
    $isPremiumArticle = $hasImage;
    $commentCtrl = new CommentController();
    $commentCount = $commentCtrl->countByArticle($article->id);

?>
<a href="<?= $url ?>" class="article-card-link">
<div class="article-card">
    <div class="card-top">
        <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
        <?= trust_badge($article->trustScore) ?>
    </div>

    <?php if ($hasImage): ?>

    <div class="card-image">

        <img src="/<?= htmlspecialchars($article->imagePath) ?>">
       

        <?php if (!$isPremiumUser): ?>
            <span class="premium-badge">Premium</span>

            <div class="premium-overlay">
                <img src="/icons/premiumlockicon.png" class="premium-lock-icon">
                <p>Premium Content</p>
            </div>
        <?php endif; ?>

    </div>

    <?php endif; ?>

    

    <h3 class="card-title">
        <?= htmlspecialchars(limit_words($article->title, 8)) ?>
    </h3>

<?php if ($isPremiumArticle && !$isPremiumUser): ?>

    <p class="card-excerpt">Upgrade to Premium to access this article...</p>

<?php else: ?>

    <p class="card-excerpt">
    <?php
    $excerpt = $article->excerpt;

    if (mb_strlen($excerpt, 'UTF-8') > 120) {
        echo htmlspecialchars(mb_substr($excerpt, 0, 120, 'UTF-8')) . '...';
    } else {
        echo htmlspecialchars($excerpt);
    }
    ?>
    </p>

<?php endif; ?>

    <div class="card-footer">

    <div class="footer-left">

        <div class="author-avatar">
            <?= htmlspecialchars($article->authorInitial()) ?>
        </div>

        <div class="author-info">
            <div class="author-name">
                <?= htmlspecialchars($article->authorName) ?>
            </div>

            <div class="card-time">
                <?= relative_time($article->publishedAt) ?>
            </div>
        </div>

    </div>

    <div class="footer-actions">

        <div class="meta-item">
            <span class="meta-icon">💬</span>
            <span class="meta-count"><?= $commentCount ?></span>
        </div>

        <div class="meta-item">
            <span class="meta-icon">🚩</span>
            <span class="meta-count">0</span>
        </div>

    </div>

</div>

</div>
</a>
<?php
}