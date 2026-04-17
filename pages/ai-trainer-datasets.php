<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AITrainerController.php';

$auth = new AuthController();

$auth->requireAuth();
$user = $auth->currentUser();
$trainerCtrl = new AITrainerController();
$trainerCtrl->requireTrainer($user);

$articles = $trainerCtrl->getDatasetArticles(25);

function ai_trainer_score_label(int $score): string
{
    if ($score >= 80) {
        return 'High';
    }
    if ($score >= 60) {
        return 'Medium';
    }
    return 'Low';
}

function ai_trainer_score_class(int $score): string
{
    if ($score >= 80) {
        return 'high';
    }
    if ($score >= 60) {
        return 'medium';
    }
    return 'low';
}

page_head('AI Trainer Datasets');
?>
<div class="dashboard-layout ai-trainer-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Datasets', 'Articles used for AI training and evaluation'); ?>
<?php flash_messages(); ?>

<div class="page-content ai-trainer-content">
    <section class="ai-section-heading">
        <h2>Analyzed Articles</h2>
    </section>

    <section class="ai-table-card">
        <div class="ai-table-wrap">
            <table class="ai-trainer-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Trust Score</th>
                        <th>Analyzed</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($articles)): ?>
                        <tr>
                            <td colspan="4" class="ai-empty-cell">No analyzed articles yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($articles as $article): ?>
                            <?php $score = (int)$article['trust_score']; ?>
                            <tr>
                                <td><?= htmlspecialchars($article['title']) ?></td>
                                <td><span class="ai-category-pill"><?= htmlspecialchars($article['category_name']) ?></span></td>
                                <td>
                                    <span class="ai-score-pill <?= ai_trainer_score_class($score) ?>">
                                        <?= ai_trainer_score_label($score) ?> (<?= $score ?>)
                                    </span>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($article['analysed_at']))) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>
</main>
</div>
<?php page_foot(); ?>
