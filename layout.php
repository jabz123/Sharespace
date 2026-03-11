cat > /var/www/current/includes/layout.php << 'ENDOFFILE'
<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ArticleController.php';
require_once __DIR__ . '/controllers/CommentController.php';

function page_head(string $title): void { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($title) ?> – Shared Space</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/public/css/app.css" />
</head>
<body>
<?php }

function page_foot(): void { ?>
    <script src="<?= BASE_PATH ?>/public/js/app.js"></script>
</body>
</html>
<?php }

function sidebar(User $user): void {
    $path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $links = [
        ['href' => BASE_PATH . '/dashboard.php',         'icon' => BASE_PATH . '/icons/searchicon.png',      'label' => 'Home'],
        ['href' => BASE_PATH . '/pages/browse.php',      'icon' => BASE_PATH . '/icons/searchicon.png',      'label' => 'Browse Articles'],
        ['href' => BASE_PATH . '/pages/my-articles.php', 'icon' => BASE_PATH . '/icons/clearicon.png',       'label' => 'My Articles'],
        ['href' => BASE_PATH . '/pages/write.php',       'icon' => BASE_PATH . '/icons/premiumlockicon.png', 'label' => 'Write Article'],
    ];
    ?>
<aside class="sidebar">
    <div class="sidebar-logo">
        <a href="<?= BASE_PATH ?>/dashboard.php" class="logo-link">
            <div class="logo-icon">📰</div>
            <span class="logo-text">Shared Space</span>
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
                <a href="<?= $link['href'] ?>" class="nav-link <?= $path === $link['href'] ? 'active' : '' ?>">
                    <img src="<?= $link['icon'] ?>" alt="<?= htmlspecialchars($link['label']) ?>" style="width:18px;height:18px;vertical-align:middle;margin-right:8px;">
                    <?= htmlspecialchars($link['label']) ?>
                </a>
            </li>
        <?php endforeach; ?>
        </ul>
    </nav>
    <div class="sidebar-footer">
        <a href="<?= BASE_PATH ?>/logout.php" class="nav-link logout">🚪 Sign Out</a>
    </div>
</aside>
<?php }

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

function flash_messages(): void {
    $err = flash('flash_error');
    $ok  = flash('flash_success');
    if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif;
    if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif;
}

function trust_badge(int $score): string {
    $cls = $score >= 80 ? 'high' : ($score >= 60 ? 'mid' : 'low');
    return "<span class=\"trust-badge trust-{$cls}\">{$score}%</span>";
}

function relative_time(string $dateStr): string {
    $diff  = time() - strtotime($dateStr);
    $hours = (int)($diff / 3600);
    if ($hours < 1)  return 'Just now';
    if ($hours < 24) return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    $days = (int)($hours / 24);
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}

function article_card(Article $article): void {
    $url = BASE_PATH . '/pages/article.php?id=' . $article->id;
?>
<div class="article-card">

    <?php if ($article->imagePath): ?>
        <div class="card-image">
            <img src="<?= BASE_PATH . '/' . $article->imagePath ?>" alt="Article Image">
        </div>
    <?php endif; ?>

    <div class="card-top">
        <span class="category-tag"><?= htmlspecialchars($article->categoryName) ?></span>
        <?= trust_badge($article->trustScore) ?>
    </div>

    <h3 class="card-title">
        <a href="<?= $url ?>"><?= htmlspecialchars($article->title) ?></a>
    </h3>

    <p class="card-excerpt">
        <?= htmlspecialchars(mb_substr($article->excerpt, 0, 120)) ?>…
    </p>

</div>
<?php }
ENDOFFILE
