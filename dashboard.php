<?php
// dashboard page

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';
require_once __DIR__ . '/includes/controllers/ArticleController.php';
require_once __DIR__ . '/includes/controllers/HomepageController.php';

// create controllers
$auth        = new AuthController();
$articleCtrl = new ArticleController();
$homeCtrl    = new HomepageController();

// ensure user is logged in
$auth->requireAuth();

// get current logged in user
$user = $auth->currentUser();



// homepage recommendation sections
// recommended articles based on user interests
$recommended = $homeCtrl->getRecommendedByInterest($user->id);

// articles people in same age group read
$ageGroupArticles = $homeCtrl->getPopularByAgeGroup($user->id);

// articles popular with same gender
$genderArticles = $homeCtrl->getPopularByGender($user->id);

// latest articles
$latestArticles = $homeCtrl->getLatest(6);


// dashboard stats (existing logic)
$avgTrust = count($latestArticles)
    ? round(array_sum(array_map(fn($a) => $a->trustScore, $latestArticles)) / count($latestArticles))
    : 0;

$trendingCount = count(array_unique(array_map(fn($a) => $a->categoryName, $latestArticles)));

page_head('Dashboard');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Welcome back, ' . htmlspecialchars($user->fullName), "Here's what's happening today"); ?>
<?php flash_messages(); ?>

<div class="page-content">
    <!-- write article button -->
    <div class="flex gap-2 mb-6">
        <a href="/pages/write.php" class="btn btn-primary">✏️ Write Article</a>
    </div>

    <!-- recommended for you -->
    <div class="mb-10">
        <h2>Recommended For You</h2>

        <?php if (empty($recommended)): ?>
            <p class="text-muted">No recommendations yet.</p>
        <?php else: ?>

        <div class="article-grid">
            <?php foreach ($recommended as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>


    <!-- people your age are reading -->
    <div class="mb-10">
        <h2>People Your Age Are Reading</h2>
        <?php if (empty($ageGroupArticles)): ?>
            <p class="text-muted">Not enough data yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($ageGroupArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>


    <!-- popular with same gender -->
    <div class="mb-10">
        <h2>Popular With <?php echo ucfirst($user->gender); ?> Readers</h2>
        <?php if (empty($genderArticles)): ?>
            <p class="text-muted">Not enough data yet.</p>
        <?php else: ?>
        <div class="article-grid">
            <?php foreach ($genderArticles as $article):
                article_card($article, $user);
            endforeach; ?>
        </div>
        <?php endif; ?>
    </div>


    <!-- latest articles -->
    <div class="mb-10">
        <h2 style="font-size:18px;font-weight:700;font-family:Georgia,serif;margin-bottom:16px">Latest Articles</h2>
        <?php if (empty($latestArticles)): ?>
            <p class="text-muted">No articles yet.</p>
        <?php else: ?>

        <div class="article-grid">
            <?php foreach ($latestArticles as $article):
                article_card($article, $user);
            endforeach; ?>
            <a href="/pages/browse.php" class="view-more-card">
            <div class="view-more-content"><span>View More</span><img src="/public/icons/viewmoreicon.png" alt="view more"></div>
            </a>
            </div>
        <?php endif; ?>
    </div>

</div>
</main>
</div>

<?php page_foot(); ?>