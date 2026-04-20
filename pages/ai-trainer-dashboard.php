<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AITrainerController.php';

$auth = new AuthController();

$auth->requireAuth();
$user = $auth->currentUser();
$trainerCtrl = new AITrainerController();
$trainerCtrl->requireTrainer($user);

$stats = $trainerCtrl->getDashboardStats();
$recentAnalyses = $trainerCtrl->getRecentAnalyses(5);

page_head('AI Trainer Dashboard');
?>
<div class="dashboard-layout ai-trainer-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('AI Trainer Dashboard', 'Monitor and calibrate the AI fact checking system'); ?>
<?php flash_messages(); ?>

<div class="page-content ai-trainer-content">
    <section class="ai-stat-grid" aria-label="AI trainer overview">
        <article class="ai-stat-card">
            <span class="ai-stat-label">Total Articles Analyzed</span>
            <div class="ai-stat-main">
                <strong><?= (int)$stats['totalArticles'] ?></strong>
                <span class="ai-stat-icon document"></span>
            </div>
        </article>

        <article class="ai-stat-card">
            <span class="ai-stat-label">Average Trust Score</span>
            <div class="ai-stat-main">
                <strong><?= (int)$stats['averageTrustScore'] ?>%</strong>
                <span class="ai-stat-icon check"></span>
            </div>
        </article>

        <article class="ai-stat-card">
            <span class="ai-stat-label">High Credibility (&gt;80)</span>
            <div class="ai-stat-main">
                <strong><?= (int)$stats['highCredibility'] ?></strong>
                <span class="ai-stat-icon trend-up"></span>
            </div>
        </article>

        <article class="ai-stat-card">
            <span class="ai-stat-label">Low Credibility (&lt;60)</span>
            <div class="ai-stat-main">
                <strong><?= (int)$stats['lowCredibility'] ?></strong>
                <span class="ai-stat-icon trend-down"></span>
            </div>
        </article>
    </section>

    <section class="ai-panel-card">
        <div class="ai-panel-head">
            <h2>Recent AI Analysis Activity</h2>
            <a href="/pages/ai-trainer-datasets.php" class="ai-text-link">View datasets</a>
        </div>
        <p>The AI fact-checking model is actively analyzing articles. Use the sub-pages to review datasets, accuracy metrics, and calibration settings.</p>

        <?php if (!empty($recentAnalyses)): ?>
            <div class="ai-activity-list">
                <?php foreach ($recentAnalyses as $analysis): ?>
                    <div class="ai-activity-row">
                        <div>
                            <strong><?= htmlspecialchars($analysis['title']) ?></strong>
                            <span><?= htmlspecialchars($analysis['category_name']) ?> category</span>
                        </div>
                        <span class="ai-score-pill <?= (int)$analysis['trust_score'] >= 80 ? 'high' : ((int)$analysis['trust_score'] >= 60 ? 'medium' : 'low') ?>">
                            <?= (int)$analysis['trust_score'] ?>%
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</div>
</main>
</div>
<?php page_foot(); ?>
