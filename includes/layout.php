<?php

// shared layout helper functions used by many pages
// handles common ui components like page head, footer, sidebar and dashboard header
// also contains helper functions for displaying things like flash messages, article cards, trust badges and relative time
// pages call these functions to render the interface instead of repeating the same html everywhere

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
//sidebar navigation with role-based logic for system_admin
function sidebar(User $user): void {
    $path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    
    // Role-based navigation
    if ($user->role === 'system_admin') {
        $links = [
            ['href' => '/pages/admin-dashboard.php', 'icon' => '🏠', 'label' => 'Admin Dashboard'],
            ['href' => '/pages/browse.php',          'icon' => '👁', 'label' => 'Browse Articles'],
            ['href' => '/pages/admin-landing.php',   'icon' => '🖼️', 'label' => 'Manage Landing Page'],
            ['href' => '/pages/profile.php',         'icon' => '👤', 'label' => 'Profile'],
        ];
    } elseif ($user->role === 'category_admin') {
        $links = [
            ['href' => '/dashboard.php',                  'icon' => '🏠', 'label' => 'Home'],
            ['href' => '/pages/browse.php',               'icon' => '👁', 'label' => 'Browse Articles'],
            ['href' => '/pages/manage-category.php',      'icon' => '📋', 'label' => 'Manage My Category'],
            ['href' => '/pages/my-articles.php',          'icon' => '📄', 'label' => 'My Articles'],
            ['href' => '/pages/write.php',                'icon' => '📝', 'label' => 'Write Article'],
            ['href' => '/pages/profile.php',              'icon' => '👤', 'label' => 'Profile'],
        ];
    } else {
        $links = [
            ['href' => '/dashboard.php',         'icon' => '🏠', 'label' => 'Home'],
            ['href' => '/pages/browse.php',      'icon' => '👁', 'label' => 'Browse Articles'],
            ['href' => '/pages/my-articles.php', 'icon' => '📄', 'label' => 'My Articles'],
            ['href' => '/pages/write.php',       'icon' => '📝', 'label' => 'Write Article'],
            ['href' => '/pages/profile.php',     'icon' => '👤', 'label' => 'Profile'],
        ];
    }
    ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="<?= $user->role === 'system_admin' ? '/pages/admin-dashboard.php' : '/dashboard.php' ?>" class="logo-link">
            <div class="logo-icon">📰</div>
            <span class="logo-text">SharedSpace</span>
        </a>
    </div>

    <div class="sidebar-user">
        <?php if (!empty($user->avatarUrl)): ?>
            <div class="user-avatar" style="background:none;padding:0;overflow:hidden">
                <img src="/public/<?= htmlspecialchars($user->avatarUrl) ?>"
                     alt="<?= htmlspecialchars($user->fullName) ?>"
                     style="width:100%;height:100%;object-fit:cover;border-radius:50%">
            </div>
        <?php else: ?>
            <div class="user-avatar"><?= htmlspecialchars($user->initial()) ?></div>
        <?php endif; ?>
        <div class="user-info">
            <p class="user-name"><?= htmlspecialchars($user->fullName) ?></p>

             <?php if ($user->role === 'premium'): ?>
                <span class="role-badge premium">Premium</span>
            <?php elseif ($user->role === 'system_admin'): ?>
                <span class="role-badge system-admin">System Admin</span>
            <?php elseif ($user->role === 'category_admin'): ?>
                <span class="role-badge" style="background:var(--primary);color:#fff">Category Admin</span>
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

    $isPremiumUser = $user->role === 'premium' || $user->role === 'system_admin';

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

        <img src="/public/<?= htmlspecialchars($article->imagePath) ?>">
       

        <?php if (!$isPremiumUser): ?>
            <span class="premium-badge">Premium</span>
        <?php endif; ?>

    </div>

    <?php endif; ?>

    

    <h3 class="card-title">
        <?= htmlspecialchars(limit_words($article->title, 8)) ?>
    </h3>

        <!-- 🔥 Always show excerpt now (soft paywall implemented) -->
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
            <span class="meta-icon">👁</span>
            <span class="meta-count"><?= $article->viewCount ?></span>
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