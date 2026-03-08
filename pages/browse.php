<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$articleCtrl = new ArticleController();

$auth->requireAuth();
$user = $auth->currentUser();

$category = $_GET['category'] ?? null;
$sort = $_GET['sort'] ?? 'recent';
$search = $_GET['search'] ?? null;
$articles = $articleCtrl->getByCategory($category, $sort, $search);

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

        <a href="?category=technology&sort=<?= $sort ?>&search=<?= $search ?>"
        class="<?= $category == 'technology' ? 'active-filter' : '' ?>">Technology</a>

        <a href="?category=science&sort=<?= $sort ?>&search=<?= $search ?>"
        class="<?= $category == 'science' ? 'active-filter' : '' ?>">Science</a>

        <a href="?category=politics&sort=<?= $sort ?>&search=<?= $search ?>"
        class="<?= $category == 'politics' ? 'active-filter' : '' ?>">Politics</a>

        <a href="?category=economy&sort=<?= $sort ?>&search=<?= $search ?>"
        class="<?= $category == 'economy' ? 'active-filter' : '' ?>">Economy</a>

        <a href="?category=sports&sort=<?= $sort ?>&search=<?= $search ?>"
        class="<?= $category == 'sports' ? 'active-filter' : '' ?>">Sports</a>

        <a href="?category=health&sort=<?= $sort ?>&search=<?= $search ?>"
        class="<?= $category == 'health' ? 'active-filter' : '' ?>">Health</a>
    </div>

    <form method="GET" class="search-bar">

        <div class="search-input-wrapper">

            <input 
                type="text" 
                id="searchInput"
                name="search" 
                placeholder="Search articles"
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
            >

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

<?php if(empty($articles)): ?>

<p>No articles found.</p>

<?php else: ?>

<?php foreach($articles as $article): ?>

<?php article_card($article, $user); ?>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</main>

</div>

<?php page_foot(); ?>