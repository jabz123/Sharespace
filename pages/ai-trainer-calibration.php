<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AITrainerController.php';

$auth = new AuthController();

$auth->requireAuth();
$user = $auth->currentUser();
$trainerCtrl = new AITrainerController();
$trainerCtrl->requireTrainer($user);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainerCtrl->saveCalibrationSettings($_POST, $user);
    redirect('/pages/ai-trainer-calibration.php', null, 'Calibration settings saved.');
}

$settings = $trainerCtrl->getCalibrationSettings();

page_head('AI Trainer Calibration');
?>
<div class="dashboard-layout ai-trainer-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Calibration', 'Fine-tune AI fact-checking parameters'); ?>
<?php flash_messages(); ?>

<div class="page-content ai-trainer-content">
    <form method="POST" class="ai-calibration-form">
        <section class="ai-panel-card ai-calibration-card">
            <h2>Publishing Threshold</h2>
            <p>Minimum trust score required to publish an article</p>
            <label class="ai-slider-label" for="publishing_threshold">
                Threshold: <output id="publishing_threshold_output"><?= (int)$settings['publishing_threshold'] ?>%</output>
            </label>
            <input
                id="publishing_threshold"
                type="range"
                name="publishing_threshold"
                min="0"
                max="100"
                value="<?= (int)$settings['publishing_threshold'] ?>"
                data-output="publishing_threshold_output"
            >
            <p class="ai-help-text">Articles below this score will be blocked from publishing.</p>
        </section>

        <section class="ai-panel-card ai-calibration-card">
            <h2>Dimension Weights</h2>
            <p>Adjust how much each dimension contributes to the overall trust score</p>

            <div class="ai-weight-row">
                <label for="factual_accuracy_weight">Factual Accuracy</label>
                <input id="factual_accuracy_weight" type="range" name="factual_accuracy_weight" min="0" max="100" value="<?= (int)$settings['factual_accuracy_weight'] ?>" data-output="factual_accuracy_weight_output">
                <output id="factual_accuracy_weight_output"><?= (int)$settings['factual_accuracy_weight'] ?>%</output>
            </div>

            <div class="ai-weight-row">
                <label for="source_quality_weight">Source Quality</label>
                <input id="source_quality_weight" type="range" name="source_quality_weight" min="0" max="100" value="<?= (int)$settings['source_quality_weight'] ?>" data-output="source_quality_weight_output">
                <output id="source_quality_weight_output"><?= (int)$settings['source_quality_weight'] ?>%</output>
            </div>

            <div class="ai-weight-row">
                <label for="bias_detection_weight">Bias Detection</label>
                <input id="bias_detection_weight" type="range" name="bias_detection_weight" min="0" max="100" value="<?= (int)$settings['bias_detection_weight'] ?>" data-output="bias_detection_weight_output">
                <output id="bias_detection_weight_output"><?= (int)$settings['bias_detection_weight'] ?>%</output>
            </div>
        </section>

        <section class="ai-panel-card ai-calibration-card">
            <h2>Strict Mode</h2>
            <p>When enabled, articles with any flagged claim of high severity are automatically blocked</p>
            <label class="ai-switch-row">
                <input type="checkbox" name="strict_mode" value="1" <?= (int)$settings['strict_mode'] === 1 ? 'checked' : '' ?>>
                <span class="ai-switch"></span>
                <strong><?= (int)$settings['strict_mode'] === 1 ? 'Enabled' : 'Disabled' ?></strong>
            </label>
        </section>

        <div class="ai-form-actions">
            <button type="submit" class="btn btn-primary">Save Calibration Settings</button>
        </div>
    </form>
</div>
</main>
</div>

<script>
document.querySelectorAll('.ai-calibration-form input[type="range"]').forEach(function (input) {
    var output = document.getElementById(input.dataset.output);
    input.addEventListener('input', function () {
        if (output) {
            output.textContent = input.value + '%';
        }
    });
});
</script>
<?php page_foot(); ?>
