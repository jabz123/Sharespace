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
