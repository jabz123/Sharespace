<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AITrainerController.php';

$auth = new AuthController();

$auth->requireAuth();
$user = $auth->currentUser();
$trainerCtrl = new AITrainerController();
$trainerCtrl->requireTrainer($user);

$breakdown = $trainerCtrl->getCredibilityBreakdown();
$categories = $trainerCtrl->getTrustByCategory();

page_head('AI Trainer Accuracy Metrics');
?>
<div class="dashboard-layout ai-trainer-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Accuracy Metrics', 'AI fact-checking performance analysis'); ?>
<?php flash_messages(); ?>

<div class="page-content ai-trainer-content">
    <section class="ai-metric-grid" aria-label="Credibility breakdown">
        <article class="ai-metric-card">
            <span>High Credibility (&gt;80)</span>
            <div><strong><?= (int)$breakdown['high']['count'] ?></strong><small>(<?= (int)$breakdown['high']['percent'] ?>%)</small></div>
            <div class="ai-progress-track"><span style="width: <?= (int)$breakdown['high']['percent'] ?>%"></span></div>
        </article>

        <article class="ai-metric-card">
            <span>Medium Credibility (60-79)</span>
            <div><strong><?= (int)$breakdown['medium']['count'] ?></strong><small>(<?= (int)$breakdown['medium']['percent'] ?>%)</small></div>
            <div class="ai-progress-track"><span style="width: <?= (int)$breakdown['medium']['percent'] ?>%"></span></div>
        </article>

        <article class="ai-metric-card">
            <span>Low Credibility (&lt;60)</span>
            <div><strong><?= (int)$breakdown['low']['count'] ?></strong><small>(<?= (int)$breakdown['low']['percent'] ?>%)</small></div>
            <div class="ai-progress-track"><span style="width: <?= (int)$breakdown['low']['percent'] ?>%"></span></div>
        </article>
    </section>

    <section class="ai-panel-card ai-category-score-card">
        <h2>Trust Score by Category</h2>

        <?php if (empty($categories)): ?>
            <p>No category metrics yet.</p>
        <?php else: ?>
            <div class="ai-category-bars">
                <?php foreach ($categories as $category): ?>
                    <?php
                    $score = max(0, min(100, (int)$category['average_trust_score']));
                    $count = (int)$category['article_count'];
                    ?>
                    <div class="ai-category-bar-row">
                        <span class="ai-category-name"><?= htmlspecialchars($category['category_name']) ?></span>
                        <div class="ai-category-track"><span style="width: <?= $score ?>%"></span></div>
                        <span class="ai-category-score"><?= $score ?>% (<?= $count ?>)</span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
</main>
</div>
<?php page_foot(); ?>
