<?php
// page that displays the browse articles page
// retrieves articles from ArticleController based on category, sort, and search
// allows users to filter articles by category
// allows users to search for articles using keywords
// allows users to sort articles by recent or most trusted
// displays the articles in a grid using article_card layout component

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';
require_once __DIR__ . '/../includes/controllers/CategoryController.php';
require_once __DIR__ . '/../includes/controllers/CommentController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();
$categoryCtrl = new CategoryController();

$auth->requireAuth();
$user = $auth->currentUser();

$category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'recent';
$search = $_GET['search'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;
$totalArticles = $articleCtrl->countByCategory($category, $search);
$totalPages = ceil($totalArticles / $perPage);
$articles = $articleCtrl->getByCategory($category, $sort, $search, $perPage, $offset);
$allCategories = $categoryCtrl->getAll();
$featuredArticle = !empty($articles) ? $articles[0] : null;
$feedArticles = !empty($articles) ? array_slice($articles, 1) : [];

page_head('Browse Articles');
?>

<div class="dashboard-layout browse-page-shell">

    <?php sidebar($user); ?>

    <main>

        <?php dash_header('Browse Articles', 'Explore all articles'); ?>

        <div class="page-content browse-page-content">
            <section class="browse-hero-panel">
                <div class="browse-hero-copy">
                    <span class="browse-kicker">Verified newsroom</span>
                    <h2 class="browse-hero-title">A high-signal view of the stories that matter most.</h2>
                    <p class="browse-hero-text">Track trusted reporting, surface emerging narratives, and scan each article through SharedSpace credibility signals.</p>
                </div>

                <form method="GET" class="search-bar browse-hero-search">
                    <div class="search-input-wrapper">
                        <input
                            type="text"
                            id="searchInput"
                            name="search"
                            placeholder="Search verified reporting"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                        <button type="button" id="clearSearch" class="clear-btn"><img src="/public/icons/clearicon.png" alt="Clear"></button>
                    </div>

                    <input type="hidden" name="category" value="<?= $category ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">

                    <button type="submit" class="search-btn"><img src="/public/icons/searchicon.png" alt="Search"></button>
                </form>
            </section>

            <div class="filter-row browse-filter-row">

                <div class="category-filters">
                    <a href="browse.php?sort=<?= $sort ?>&search=<?= $search ?>"
                        class="<?= $category == null ? 'active-filter' : '' ?>">All</a>

                    <?php foreach ($allCategories as $cat): ?>
                        <?php $catSlug = strtolower($cat['name']); ?>
                        <a href="?category=<?= urlencode($catSlug) ?>&sort=<?= $sort ?>&search=<?= $search ?>"
                            class="<?= $category == $catSlug ? 'active-filter' : '' ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>

            </div>

            <div class="sort-filters">
                <span>Sort By:</span>

                <a href="?category=<?= $category ?>&sort=recent&search=<?= $search ?>"
                    class="sort-btn <?= $sort == 'recent' ? 'active-filter' : '' ?>">Recent
                </a>

                <a href="?category=<?= $category ?>&sort=trusted&search=<?= $search ?>"
                    class="sort-btn <?= $sort == 'trusted' ? 'active-filter' : '' ?>">Most Trusted
                </a>
            </div>

            <?php if ($featuredArticle): ?>
                <?php $featuredComments = (new CommentController())->countByArticle($featuredArticle->id); ?>
                <article class="browse-featured-card">
                    <div class="browse-featured-media">
                        <?php if (!empty($featuredArticle->imagePath)): ?>
                            <img src="/public/<?= htmlspecialchars($featuredArticle->imagePath) ?>" alt="">
                        <?php endif; ?>
                        <div class="browse-featured-overlay"></div>
                        <div class="browse-featured-topline">
                            <span class="browse-featured-tag">Top story</span>
                            <span class="browse-featured-score"><?= (int)$featuredArticle->trustScore ?>%</span>
                        </div>
                    </div>

                    <div class="browse-featured-body">
                        <div class="browse-featured-meta">
                            <span class="category-tag <?= category_theme_class($featuredArticle->categoryName) ?>"><?= htmlspecialchars($featuredArticle->categoryName) ?></span>
                            <span class="browse-verified-pill">Verified</span>
                        </div>

                        <h3 class="browse-featured-title">
                            <a href="/pages/article.php?id=<?= $featuredArticle->id ?>&return=<?= urlencode($_SERVER['REQUEST_URI']) ?>">
                                <?= htmlspecialchars($featuredArticle->title) ?>
                            </a>
                        </h3>

                        <p class="browse-featured-excerpt">
                            <?= htmlspecialchars(limit_words($featuredArticle->excerpt, 26)) ?>
                        </p>

                        <div class="browse-featured-credibility">
                            <div class="browse-featured-cred-head">
                                <span>Credibility score</span>
                                <span><?= (int)$featuredArticle->trustScore ?>%</span>
                            </div>
                            <div class="browse-featured-track">
                                <span style="width: <?= max(12, min(100, (int)$featuredArticle->trustScore)) ?>%"></span>
                            </div>
                        </div>

                        <div class="browse-featured-footer">
                            <div class="browse-featured-author">
                                <span class="author-avatar"><?= htmlspecialchars($featuredArticle->authorInitial()) ?></span>
                                <div>
                                    <div class="author-name"><?= htmlspecialchars($featuredArticle->authorName) ?></div>
                                    <div class="card-time"><?= relative_time($featuredArticle->publishedAt) ?></div>
                                </div>
                            </div>

                            <div class="browse-featured-stats">
                                <span><?= (int)$featuredArticle->viewCount ?> views</span>
                                <span><?= (int)$featuredComments ?> comments</span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endif; ?>

            <div class="article-grid browse-article-grid">
                <?php if (empty($articles)): ?>
                    <p>No articles found.</p>
                <?php else: ?>
                    <?php foreach ($feedArticles as $article): ?>
                        <?php article_card($article, $user); ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </div>

        <div class="pagination">

            <a href="<?= $page > 1
                ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=1'
                : '#' ?>"
            class="page-btn <?= $page == 1 ? 'disabled' : '' ?>">
            First
            </a>

            <a href="<?= $page > 1
                ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=' . ($page - 1)
                : '#' ?>"
            class="page-btn <?= $page == 1 ? 'disabled' : '' ?>">
            Previous
            </a>

            <form method="GET" class="page-form">
                <input
                    type="number"
                    id="pageInput"
                    name="page"
                    value="<?= $page ?>"
                    min="1"
                    max="<?= $totalPages ?>"
                    class="page-input">

                <span>of <?= $totalPages ?></span>
                <input type="hidden" name="category" value="<?= $category ?>">
                <input type="hidden" name="sort" value="<?= $sort ?>">
                <input type="hidden" name="search" value="<?= $search ?>">
            </form>

            <a href="<?= $page < $totalPages
                ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=' . ($page + 1)
                : '#' ?>"
            class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>">
            Next
            </a>

            <a href="<?= $page < $totalPages
                ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=' . $totalPages
                : '#' ?>"
            class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>">
            Last
            </a>

        </div>

    </main>

</div>

<?php page_foot(); ?>
