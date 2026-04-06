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

// $articles = $articleCtrl->getByCategory($category, $sort, $search);
$allCategories = $categoryCtrl->getAll();

page_head('Browse Articles');
?>

<div class="dashboard-layout">

    <?php sidebar($user); ?>

    <main>


        <!-- page header -->
        <?php dash_header('Browse Articles', 'Explore all articles'); ?>

        <div class="page-content">
            <div class="filter-row">

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

                <form method="GET" class="search-bar">

                    <div class="search-input-wrapper">

                        <input
                            type="text"
                            id="searchInput"
                            name="search"
                            placeholder="Search articles"
                            value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">

                        <button type="button" id="clearSearch" class="clear-btn"><img src="/public/icons/clearicon.png" alt="Clear"></button>

                    </div>

                    <input type="hidden" name="category" value="<?= $category ?>">
                    <input type="hidden" name="sort" value="<?= $sort ?>">

                    <button type="submit" class="search-btn"> <img src="/public/icons/searchicon.png" alt="Search"></button>

                </form>

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



            <div class="article-grid">

                <?php if (empty($articles)): ?>

                    <p>No articles found.</p>

                <?php else: ?>

                    <?php foreach ($articles as $article): ?>

                        <?php article_card($article, $user); ?>

                    <?php endforeach; ?>

                <?php endif; ?>

            </div>

        </div>
            <div class="pagination">

               <!-- ⏮ FIRST PAGE -->
                <a href="<?= $page > 1 
                    ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=1'
                    : '#' ?>"
                class="page-btn <?= $page == 1 ? 'disabled' : '' ?>">
                ⏮
                </a>

                <!-- ◀ PREVIOUS -->
                <a href="<?= $page > 1 
                    ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=' . ($page - 1)
                    : '#' ?>"
                class="page-btn <?= $page == 1 ? 'disabled' : '' ?>">
                ◀
                </a>
                <form method="GET" class="page-form">
                <!-- PAGE INPUT -->
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
               <!-- ▶ NEXT -->
            <a href="<?= $page < $totalPages 
                ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=' . ($page + 1)
                : '#' ?>"
            class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>">
            ▶
            </a>

            <!-- ⏭ LAST -->
            <a href="<?= $page < $totalPages 
                ? '?category=' . urlencode($category) . '&sort=' . urlencode($sort) . '&search=' . urlencode($search) . '&page=' . $totalPages
                : '#' ?>"
            class="page-btn <?= $page == $totalPages ? 'disabled' : '' ?>">
            ⏭
            </a>

            </div>

    </main>

</div>

<?php page_foot(); ?>