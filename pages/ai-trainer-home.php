<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AITrainerController.php';

$auth = new AuthController();

$auth->requireAuth();
$user = $auth->currentUser();
$trainerCtrl = new AITrainerController();
$trainerCtrl->requireTrainer($user);

$recentArticles = $trainerCtrl->getRecentArticles(3);

page_head('AI Trainer Home');
?>
<div class="dashboard-layout ai-trainer-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Welcome Back, AI Trainer ' . $user->fullName, "Here's what is happening on SharedSpace today."); ?>
<?php flash_messages(); ?>

<div class="page-content ai-trainer-content">
    <section class="ai-home-toolbar">
        <h2>Recent Articles</h2>
        <form action="/pages/browse.php" method="GET" class="ai-search-form">
            <input type="text" name="search" placeholder="Search articles" aria-label="Search articles">
        </form>
    </section>

    <section class="ai-home-grid">
        <?php if (empty($recentArticles)): ?>
            <div class="ai-panel-card">
                <h2>No articles yet</h2>
                <p>Recent articles will appear here after they are published.</p>
            </div>
        <?php else: ?>
            <?php foreach ($recentArticles as $article): ?>
                <?php
                $score = (int)($article['trust_score'] ?? 0);
                $imagePath = trim((string)($article['image_path'] ?? ''));
                ?>
                <article class="ai-home-article">
                    <a href="/pages/article.php?id=<?= (int)$article['id'] ?>" class="ai-home-article-link">
                        <div class="ai-home-media">
                            <?php if ($imagePath !== ''): ?>
                                <img src="/public/<?= htmlspecialchars($imagePath) ?>" alt="">
                            <?php endif; ?>
                            <span class="ai-premium-tag">Premium</span>
                        </div>
                        <div class="ai-home-body">
                            <div class="ai-home-meta">
                                <span><?= htmlspecialchars($article['category_name']) ?></span>
                                <span class="ai-score-pill <?= $score >= 80 ? 'high' : ($score >= 60 ? 'medium' : 'low') ?>"><?= $score ?>% Verified</span>
                            </div>
                            <h3><?= htmlspecialchars($article['title']) ?></h3>
                            <p><?= htmlspecialchars(limit_words($article['excerpt'], 22)) ?></p>
                            <div class="ai-home-footer">
                                <span><?= htmlspecialchars($article['author_name']) ?></span>
                                <span><?= htmlspecialchars(relative_time($article['published_at'])) ?></span>
                            </div>
                        </div>
                    </a>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</div>
</main>
</div>
<?php page_foot(); ?>
