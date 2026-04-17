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
$commentCtrl = new CommentController();

$auth->requireAuth();
$user = $auth->currentUser();
$isSystemAdmin = $user->role === 'system_admin';
$isTrainer = $user->role === 'ai_trainer';

$category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'recent';
$search = $_GET['search'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;
$totalArticles = $articleCtrl->countByCategory($category, $search);
$totalPages = (int) ceil($totalArticles / $perPage);
$adminTotalPages = max(1, $totalPages);
$articles = $articleCtrl->getByCategory($category, $sort, $search, $perPage, $offset);
$allCategories = $categoryCtrl->getAll();
$featuredArticle = !empty($articles) ? $articles[0] : null;
$feedArticles = !empty($articles) ? array_slice($articles, 1) : [];
$activeSortLabel = $sort === 'trusted' ? 'Most trusted first' : 'Newest first';

page_head('Browse Articles');
?>

<?php if ($isSystemAdmin || $isTrainer): ?>
<div class="dashboard-layout browse-admin-shell">

    <?php sidebar($user); ?>

    <main>

        <?php dash_header('Browse Articles', 'Explore all articles'); ?>
        <?php flash_messages(); ?>

        <div class="page-content browse-page-content">
            <section class="card browse-overview-card">
                <div class="browse-overview-main">
                    <span class="browse-overview-kicker">Verified newsroom</span>
                    <h2 class="browse-overview-title">Browse trusted coverage across every section.</h2>
                    <p class="browse-overview-text">
                        Explore the full SharedSpace article feed, refine the newsroom by category,
                        and sort stories by freshness or credibility.
                    </p>
                </div>

                <div class="browse-overview-side">
                    <form method="GET" class="browse-search-form">
                        <div class="search-input-wrapper">
                            <input
                                type="text"
                                id="searchInput"
                                name="search"
                                placeholder="Search verified reporting"
                                value="<?= htmlspecialchars($search ?? '') ?>">

                            <button type="button" id="clearSearch" class="clear-btn">
                                <img src="/public/icons/clearicon.png" alt="Clear">
                            </button>
                        </div>

                        <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? '') ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">

                        <button type="submit" class="search-btn">Search</button>
                    </form>

                    <div class="browse-overview-stats">
                        <div class="browse-overview-stat">
                            <strong><?= (int) $totalArticles ?></strong>
                            <span>Verified stories</span>
                        </div>
                        <div class="browse-overview-stat">
                            <strong><?= htmlspecialchars($activeSortLabel) ?></strong>
                            <span>Current ranking</span>
                        </div>
                        <div class="browse-overview-stat">
                            <strong><?= (int) $page ?></strong>
                            <span>Page of <?= (int) $adminTotalPages ?></span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card browse-controls-card">
                <div class="browse-controls-head">
                    <div>
                        <h3>Filter and sort</h3>
                        <p>Use the category chips and ranking options to narrow the article feed.</p>
                    </div>
                </div>

                <div class="filter-row browse-filter-row">
                    <div class="category-filters">
                        <a
                            href="browse.php?sort=<?= urlencode($sort) ?>&search=<?= urlencode((string) $search) ?>"
                            class="<?= $category == null ? 'active-filter' : '' ?>">
                            All
                        </a>

                        <?php foreach ($allCategories as $cat): ?>
                            <?php $catSlug = strtolower($cat['name']); ?>
                            <a
                                href="?category=<?= urlencode($catSlug) ?>&sort=<?= urlencode($sort) ?>&search=<?= urlencode((string) $search) ?>"
                                class="<?= $category == $catSlug ? 'active-filter' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="sort-filters browse-sort-row">
                    <span>Sort By:</span>

                    <a
                        href="?category=<?= urlencode((string) $category) ?>&sort=recent&search=<?= urlencode((string) $search) ?>"
                        class="sort-btn <?= $sort == 'recent' ? 'active-filter' : '' ?>">
                        Recent
                    </a>

                    <a
                        href="?category=<?= urlencode((string) $category) ?>&sort=trusted&search=<?= urlencode((string) $search) ?>"
                        class="sort-btn <?= $sort == 'trusted' ? 'active-filter' : '' ?>">
                        Most Trusted
                    </a>
                </div>
            </section>

            <?php if ($featuredArticle): ?>
                <?php $featuredComments = $commentCtrl->countByArticle($featuredArticle->id); ?>
                <section class="card browse-featured-card">
                    <div class="browse-featured-head">
                        <div>
                            <span class="browse-section-kicker">Featured article</span>
                            <h3>Top story in the current feed</h3>
                        </div>
                        <p>
                            Highlighting the strongest article from the selected category, search,
                            and sort combination.
                        </p>
                    </div>

                    <article class="browse-featured-story">
                        <div class="browse-featured-media">
                            <?php if (!empty($featuredArticle->imagePath)): ?>
                                <img src="/public/<?= htmlspecialchars($featuredArticle->imagePath) ?>" alt="">
                            <?php endif; ?>
                            <div class="browse-featured-overlay"></div>
                            <div class="browse-featured-topline">
                                <span class="browse-featured-tag">Top story</span>
                                <span class="browse-featured-score"><?= (int) $featuredArticle->trustScore ?>%</span>
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
                                    <span><?= (int) $featuredArticle->trustScore ?>%</span>
                                </div>
                                <div class="browse-featured-track">
                                    <span style="width: <?= max(12, min(100, (int) $featuredArticle->trustScore)) ?>%"></span>
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
                                    <span><?= (int) $featuredArticle->viewCount ?> views</span>
                                    <span><?= (int) $featuredComments ?> comments</span>
                                </div>
                            </div>
                        </div>
                    </article>
                </section>
            <?php else: ?>
                <section class="card browse-empty-state">
                    <span class="browse-section-kicker">No matching coverage</span>
                    <h3>No articles matched your current filters.</h3>
                    <p>Try clearing the search term or switching to another category to expand the feed.</p>
                </section>
            <?php endif; ?>

            <section class="browse-results-section">
                <div class="browse-results-head">
                    <div>
                        <span class="browse-section-kicker">Newsroom feed</span>
                        <h3><?= !empty($feedArticles) ? 'More articles to review' : 'Article results' ?></h3>
                    </div>
                    <p>
                        <?= $totalArticles > 0
                            ? 'Showing the latest set of verified stories for your current filters.'
                            : 'Update the filters above to load matching stories.' ?>
                    </p>
                </div>

                <div class="article-grid browse-article-grid">
                    <?php if (empty($articles)): ?>
                        <div class="card browse-empty-state browse-empty-inline">
                            <h3>No articles found.</h3>
                            <p>There are no published articles for this filter combination yet.</p>
                        </div>
                    <?php elseif (empty($feedArticles)): ?>
                        <div class="card browse-empty-state browse-empty-inline">
                            <h3>No additional articles on this page.</h3>
                            <p>The featured story is currently the only result returned for these filters.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($feedArticles as $article): ?>
                            <?php article_card($article, $user); ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <div class="pagination browse-pagination">

            <a href="<?= $page > 1
                ? '?category=' . urlencode((string) $category) . '&sort=' . urlencode($sort) . '&search=' . urlencode((string) $search) . '&page=1'
                : '#' ?>"
            class="page-btn <?= $page == 1 ? 'disabled' : '' ?>">
            First
            </a>

            <a href="<?= $page > 1
                ? '?category=' . urlencode((string) $category) . '&sort=' . urlencode($sort) . '&search=' . urlencode((string) $search) . '&page=' . ($page - 1)
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
                    max="<?= $adminTotalPages ?>"
                    class="page-input">

                <span>of <?= $adminTotalPages ?></span>
                <input type="hidden" name="category" value="<?= htmlspecialchars($category ?? '') ?>">
                <input type="hidden" name="sort" value="<?= htmlspecialchars($sort) ?>">
                <input type="hidden" name="search" value="<?= htmlspecialchars($search ?? '') ?>">
            </form>

            <a href="<?= $page < $adminTotalPages
                ? '?category=' . urlencode((string) $category) . '&sort=' . urlencode($sort) . '&search=' . urlencode((string) $search) . '&page=' . ($page + 1)
                : '#' ?>"
            class="page-btn <?= $page == $adminTotalPages ? 'disabled' : '' ?>">
            Next
            </a>

            <a href="<?= $page < $adminTotalPages
                ? '?category=' . urlencode((string) $category) . '&sort=' . urlencode($sort) . '&search=' . urlencode((string) $search) . '&page=' . $adminTotalPages
                : '#' ?>"
            class="page-btn <?= $page == $adminTotalPages ? 'disabled' : '' ?>">
            Last
            </a>

        </div>

    </main>

</div>
<?php else: ?>
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

                <div class="browse-hero-tools">
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

                    <div class="browse-hero-stats">
                        <div class="browse-hero-stat">
                            <strong><?= (int)$totalArticles ?></strong>
                            <span>Verified stories</span>
                        </div>
                        <div class="browse-hero-stat">
                            <strong><?= htmlspecialchars(ucfirst($sort)) ?></strong>
                            <span>Current ranking</span>
                        </div>
                    </div>
                </div>
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

            <section class="browse-story-stage">
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

                <aside class="browse-side-panel">
                    <div class="browse-side-card">
                        <span class="browse-side-kicker">Today’s signal</span>
                        <h3>What the credibility layer is surfacing right now.</h3>
                        <ul class="browse-side-list">
                            <li>Stories with stronger sourcing are ranked higher.</li>
                            <li>Verification badges make trustworthy reporting easier to scan.</li>
                            <li>Featured coverage prioritizes signal over volume.</li>
                        </ul>
                    </div>
                </aside>
            </section>

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

<?php endif; ?>

<?php page_foot(); ?>
