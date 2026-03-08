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
$articles = $articleCtrl->getByCategory($category, $sort);

page_head('Browse Articles');
?>

<div class="dashboard-layout">

<?php sidebar($user); ?>

<main>

<?php dash_header('Browse Articles', 'Explore all articles'); ?>

<div class="page-content">

<div class="category-filters">

<!-- <a href="browse.php">All</a>
<a href="?category=technology">Technology</a>
<a href="?category=science">Science</a>
<a href="?category=politics">Politics</a>
<a href="?category=economy">Economy</a>
<a href="?category=sports">Sports</a>
<a href="?category=health">Health</a> -->
<a href="browse.php?sort=<?= $sort ?>">All</a>

<a href="?category=technology&sort=<?= $sort ?>">Technology</a>
<a href="?category=science&sort=<?= $sort ?>">Science</a>
<a href="?category=politics&sort=<?= $sort ?>">Politics</a>
<a href="?category=economy&sort=<?= $sort ?>">Economy</a>
<a href="?category=sports&sort=<?= $sort ?>">Sports</a>
<a href="?category=health&sort=<?= $sort ?>">Health</a>

</div>

<div class="sort-filters">

<span>Sort By:</span>

<!-- <a href="?sort=recent" class="sort-btn">Recent</a>
<a href="?sort=trusted" class="sort-btn">Most Trusted</a> -->
<a href="?category=<?= $category ?>&sort=recent" class="sort-btn">Recent</a>
<a href="?category=<?= $category ?>&sort=trusted" class="sort-btn">Most Trusted</a>

</div>

<div class="article-grid">

<?php if(empty($articles)): ?>

<p>No articles found.</p>

<?php else: ?>

<?php foreach($articles as $article): ?>

<?php article_card($article); ?>

<?php endforeach; ?>

<?php endif; ?>

</div>

</div>

</main>

</div>

<?php page_foot(); ?>