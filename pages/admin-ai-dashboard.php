<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';
require_once __DIR__ . '/../includes/controllers/AIVerificationController.php';

$auth = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();
$adminCtrl->requireAdmin($user);

$aiCtrl = new AIVerificationController();
$stats = $aiCtrl->getDashboardStats();
$recentAnalyses = $aiCtrl->getRecentAnalyses(8);

page_head('AI Dashboard', true);
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('AI Dashboard', 'Monitor AI verification outcomes'); ?>
<?php flash_messages(); ?>

<div class="page-content admin-ai-content">
    <section class="admin-ai-stat-grid">
        <article class="admin-ai-stat-card">
            <span>Total Articles Analyzed</span>
            <strong><?= (int)$stats['totalArticles'] ?></strong>
        </article>
        <article class="admin-ai-stat-card">
            <span>Average Trust Score</span>
            <strong><?= (int)$stats['averageTrustScore'] ?>%</strong>
        </article>
        <article class="admin-ai-stat-card">
            <span>High Credibility</span>
            <strong><?= (int)$stats['highCredibility'] ?></strong>
        </article>
        <article class="admin-ai-stat-card">
            <span>Low Credibility</span>
            <strong><?= (int)$stats['lowCredibility'] ?></strong>
        </article>
    </section>

    <section class="card admin-ai-card">
        <div class="admin-ai-card-head">
            <div>
                <h2>Recent AI Analysis Activity</h2>
                <p>Latest articles reviewed by the AI verification pipeline.</p>
            </div>
        </div>

        <?php if (empty($recentAnalyses)): ?>
            <p class="text-muted">No analyzed articles yet.</p>
        <?php else: ?>
            <div class="admin-ai-list">
                <?php foreach ($recentAnalyses as $analysis): ?>
                    <?php $score = (int)$analysis['trust_score']; ?>
                    <div class="admin-ai-list-row">
                        <div>
                            <strong><?= htmlspecialchars($analysis['title']) ?></strong>
                            <span><?= htmlspecialchars($analysis['category_name']) ?> category</span>
                        </div>
                        <span class="admin-ai-score <?= $score >= 80 ? 'high' : ($score >= 60 ? 'medium' : 'low') ?>">
                            <?= $score ?>%
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
