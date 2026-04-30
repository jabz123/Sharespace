<?php

//this is a shared rendering layer for all/ most pages.

//css styles are all loaded here in the layout file
//this file also has common functions for rendering the layout and other shared utilities
//css will be called through page_head function. it is at the top of every page.
//to make sure the cache is refreshed when changes are made.

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ArticleController.php';
require_once __DIR__ . '/controllers/CommentController.php';
require_once __DIR__ . '/textlimit.php';

function assigned_category_for_expert(int $userId): ?array
{
    static $cache = [];

    if (array_key_exists($userId, $cache)) {
        return $cache[$userId];
    }

    DB::ensureCategoryExpertsTable();

    $cache[$userId] = DB::first(
        'SELECT c.id, c.name
         FROM category_experts ce
         JOIN categories c ON c.id = ce.category_id
         WHERE ce.user_id = ?
         ORDER BY c.name
         LIMIT 1',
        [$userId]
    );

    return $cache[$userId];
}

// this is called at the head section of ever page to load common css and title shit
//sets page title and adds class to body

//page head just sets page title, and generates body class slug. So,
//"Discover Users" will become page-discover-users, shown at $slug below.

//check if user is admin. if admin, load admin css.
function page_head(string $title, bool $useSystemAdminCss = false): void
{
    $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $title), '-'));
    //this slug is used in the body class to allow page-specific css targeting without
    //needing to create separate css files for every page.

    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title><?= htmlspecialchars($title) ?> - SharedSpace</title>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="/public/css/app.css?v=<?= filemtime(__DIR__ . '/../public/css/app.css') ?>" />

        <!-- check user type then load admin css, or user dark theme css -->
        <?php if ($useSystemAdminCss): ?>
            <link rel="stylesheet" href="/public/css/pages.css?v=<?= filemtime(__DIR__ . '/../public/css/pages.css') ?>" />
            <link rel="stylesheet" href="/public/css/system-admin.css?v=<?= filemtime(__DIR__ . '/../public/css/system-admin.css') ?>" />
        <?php else: ?>
            <link rel="stylesheet" href="/public/css/dashboard.css?v=<?= filemtime(__DIR__ . '/../public/css/dashboard.css') ?>" />
            <link rel="stylesheet" href="/public/css/pages.css?v=<?= filemtime(__DIR__ . '/../public/css/pages.css') ?>" />
        <?php endif; ?>
        <!-- removed ai trainer in line style here -->
    </head>

    <body class="page-<?= htmlspecialchars($slug) ?>">
    <?php }

function page_foot(): void
{ ?>
        <div id="toast" class="toast hidden">
            <span id="toastMessage"></span>
            <button id="toastClose">x</button>
        </div>

        <script src="/public/js/app.js"></script>
    </body>

    </html>
<?php }
//for logo component
function sharedspace_brand(string $href = '/', string $variant = 'dark', string $class = ''): void
{ ?>
    <a href="<?= htmlspecialchars($href) ?>" class="sharedspace-brand <?= htmlspecialchars(trim($class)) ?>">
        <img
            src="<?= htmlspecialchars($variant === 'light' ? '/public/icons/sharedspace-logo-light.svg' : '/public/icons/sharedspace-logo-dark.svg') ?>"
            alt="SharedSpace"
            class="sharedspace-brand-image">
    </a>
<?php }
//sidebar component. shows diff info based on user type
function sidebar(User $user): void
{
    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    if ($user->role === 'system_admin') {
        $links = [
            ['href' => '/pages/admin-dashboard.php', 'key' => 'home',    'label' => 'Admin Dashboard'],
            ['href' => '/pages/browse.php',           'key' => 'browse',  'label' => 'Browse Articles'],
            ['href' => '/pages/admin-landing.php',    'key' => 'landing', 'label' => 'Manage Landing Page'],
            ['href' => '/pages/admin-audit-log.php',  'key' => 'audit',   'label' => 'Audit Log'],
            ['href' => '/pages/admin-ai-dashboard.php', 'key' => 'ai', 'label' => 'AI Dashboard'],
            ['href' => '/pages/profile.php',          'key' => 'profile', 'label' => 'Profile'],

        ];
    } elseif ($user->role === 'category_admin') {
        $links = [
            ['href' => '/pages/category-admin-dashboard.php', 'key' => 'home', 'label' => 'Home'],
            ['href' => '/pages/browse.php', 'key' => 'browse', 'label' => 'Browse Articles'],
            ['href' => '/pages/unverified-articles.php', 'key' => 'alerts', 'label' => 'Unverified Articles'],
            ['href' => '/pages/category-articles.php', 'key' => 'library', 'label' => 'Category Articles'],
            ['href' => '/pages/category-writers.php', 'key' => 'writers', 'label' => 'Category Writers'],
            ['href' => '/pages/flagged-articles.php', 'key' => 'flags', 'label' => 'Flagged Articles'],
            ['href' => '/pages/my-articles.php', 'key' => 'articles', 'label' => 'My Articles'],
            ['href' => '/pages/write.php', 'key' => 'compose', 'label' => 'Write Article'],
            ['href' => '/pages/profile.php', 'key' => 'profile', 'label' => 'Profile'],
            ['href' => '/pages/savedarticles.php', 'key' => 'saved', 'label' => 'Saved Articles'],
            ['href' => '/pages/users.php', 'key' => 'users', 'label' => 'Discover Users'],
            
        ];
    } else {
        $links = [
            ['href' => '/dashboard.php', 'key' => 'home', 'label' => 'Home'],
            ['href' => '/pages/browse.php', 'key' => 'browse', 'label' => 'Browse Articles'],
            ['href' => '/pages/my-articles.php', 'key' => 'articles', 'label' => 'My Articles'],
            ['href' => '/pages/write.php', 'key' => 'compose', 'label' => 'Write Article'],
            ['href' => '/pages/profile.php', 'key' => 'profile', 'label' => 'Profile'],
            ['href' => '/pages/savedarticles.php', 'key' => 'saved', 'label' => 'Saved Articles'],
            ['href' => '/pages/subscription.php', 'key' => 'membership', 'label' => $user->role === 'premium' ? 'Subscription' : 'Upgrade'],
            ['href' => '/pages/users.php', 'key' => 'users', 'label' => 'Discover Users'],
        ];
    }
    ?>
    <!-- sidebar overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <?php sharedspace_brand(
                $user->role === 'system_admin' ? '/pages/admin-dashboard.php' : ($user->role === 'ai_trainer' ? '/pages/ai-trainer-dashboard.php' : ($user->role === 'category_admin' ? '/pages/category-admin-dashboard.php' : '/dashboard.php')),
                'light',
                'sidebar-brand'
            ); ?>
        </div>

        <a class="sidebar-user sidebar-user-link" href="/pages/user-profile.php?id=<?= (int) $user->id ?>">
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
                <?php elseif ($user->role === 'ai_trainer'): ?>
                    <span class="role-badge ai-trainer">AI Trainer</span>
                <?php elseif ($user->role === 'category_admin'): ?>
                    <span class="role-badge category-admin">Category Expert</span>
                    <?php
                    $assignedCategory = assigned_category_for_expert((int) $user->id);
                    if ($assignedCategory): ?>
                        <span class="role-badge category-name"><?= htmlspecialchars($assignedCategory['name']) ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="role-badge free">Free</span>
                <?php endif; ?>
            </div>
        </a>

        <nav class="sidebar-nav">
            <ul>
                <?php foreach ($links as $link): ?>
                    <li>
                        <a href="<?= $link['href'] ?>" class="nav-link <?= $path === $link['href'] ? 'active' : '' ?>">
                            <span class="nav-icon nav-icon-<?= htmlspecialchars($link['key']) ?>" aria-hidden="true"></span>
                            <?= htmlspecialchars($link['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <a href="/logout.php" class="nav-link logout">
                <span class="nav-icon nav-icon-logout" aria-hidden="true"></span>
                Sign Out
            </a>
        </div>
    </aside>
<?php }

//header component for dashboard and admin pages
function dash_header(string $title, string $subtitle = ''): void
{ ?>
    <header class="dash-header">  <!-- hamburger btn for mobile -->
        <button class="hamburger-btn" id="hamburgerBtn" aria-label="Toggle navigation" aria-expanded="false" aria-controls="sidebar">
            <span></span><span></span><span></span>
        </button>
        <div>
            <h1 class="dash-title"><?= htmlspecialchars($title) ?></h1>
            <?php if ($subtitle): ?>
                <p class="dash-subtitle"><?= htmlspecialchars($subtitle) ?></p>
            <?php endif; ?>
        </div>
    </header>
    <?php }

function flash_messages(): void
{
    $err = flash('flash_error');
    $ok = flash('flash_success');
    if ($err): ?><div class="alert alert-error"><?= htmlspecialchars($err) ?></div><?php endif;
    if ($ok): ?><div class="alert alert-success"><?= htmlspecialchars($ok) ?></div><?php endif;
}
//maps category names to css classes so can apply category specific stylinh
function category_theme_class(string $name): string
{
    return match (strtolower(trim($name))) {
        'politics' => 'category-politics',
        'health' => 'category-health',
        'economy', 'business', 'finance' => 'category-economy',
        'sports' => 'category-sports',
        'games', 'gaming' => 'category-games',
        'technology', 'tech' => 'category-technology',
        'education' => 'category-education',
        'science' => 'category-science',
        default => 'category-default',
    };
}

function trust_badge(int $score): string
{
    $cls = $score >= 80 ? 'high' : ($score >= 60 ? 'mid' : 'low');
    return "<span class=\"trust-badge trust-{$cls}\">{$score}%</span>";
}

function relative_time(string $dateStr): string
{
    $diff = time() - strtotime($dateStr);
    $hours = (int) ($diff / 3600);
    if ($hours < 1) {
        return 'Just now';
    }
    if ($hours < 24) {
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    }
    $days = (int) ($hours / 24);
    return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
}

function article_card(Article $article, User $user): void
{
    $currentUrl = $_SERVER['REQUEST_URI'];
    $url = '/pages/article.php?id=' . $article->id . '&return=' . urlencode($currentUrl);

    $isPremiumUser = $user->role === 'premium' || $user->role === 'system_admin' || $user->role === 'category_admin';
    $hasImage = !empty($article->imagePath);
    $commentCtrl = new CommentController();
    $commentCount = $commentCtrl->countByArticle($article->id);
    ?>
    <a href="<?= $url ?>" class="article-card-link">
        <div class="article-card">
            <div class="card-top">
                <span class="category-tag <?= category_theme_class($article->categoryName) ?>"><?= htmlspecialchars($article->categoryName) ?></span>
                <?= trust_badge($article->trustScore) ?>
            </div>

            <?php if ($article->status === 'suspended'): ?>
                <span class="suspended-badge">Suspended Article</span>
            <?php endif; ?>

            <?php if ($hasImage): ?>
                <div class="card-image">
                    <img src="/public/<?= htmlspecialchars($article->imagePath) ?>" alt="">
                    <?php if (!$isPremiumUser): ?>
                        <span class="premium-badge">Premium</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <h3 class="card-title">
                <?= htmlspecialchars(limit_words($article->title, 8)) ?>
            </h3>

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

            <div class="card-credibility">
                <div class="card-credibility-row">
                    <span class="card-verified-pill">Verified</span>
                    <span class="card-score-copy"><?= (int) $article->trustScore ?>% credibility</span>
                </div>
                <?php
                                                                                                                                                $trackClass = ((int) $article->trustScore >= 80) ? 'track-high' : (((int) $article->trustScore >= 60) ? 'track-mid' : 'track-low');
    ?>
                <div class="card-score-track">
                    <span class="<?= $trackClass ?>" style="width:<?= max(12, min(100, (int) $article->trustScore)) ?>%"></span>
                </div>

            </div>

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
                        <span class="meta-label">Comments</span>
                        <span class="meta-count"><?= $commentCount ?></span>
                    </div>

                    <div class="meta-item">
                        <span class="meta-label">Views</span>
                        <span class="meta-count"><?= $article->viewCount ?></span>
                    </div>
                </div>
            </div>
        </div>
    </a>
<?php
}
