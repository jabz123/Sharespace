<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/AdminController.php';
require_once __DIR__ . '/../includes/controllers/AITrainerController.php';

$auth = new AuthController();
$adminCtrl = new AdminController();

$auth->requireAuth();
$user = $auth->currentUser();
$adminCtrl->requireAdmin($user);

$aiCtrl = new AITrainerController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aiCtrl->saveCalibrationSettings($_POST, $user);
    $adminCtrl->logAction($user->id, 'update_model_settings', 'AI Calibration', null, 'Updated AI verification calibration settings.');
    redirect('/pages/admin-ai-calibration.php', null, 'Calibration settings saved.');
}

$settings = $aiCtrl->getCalibrationSettings();

page_head('AI Calibration');
?>
<div class="dashboard-layout admin-ai-shell">
<?php sidebar($user); ?>
<main>
<?php dash_header('Calibration', 'Configure backend AI publishing thresholds'); ?>
<?php flash_messages(); ?>

<div class="page-content admin-ai-content">
    <form method="POST" class="card admin-ai-card admin-ai-form">
        <section>
            <h2>Publishing Threshold</h2>
            <p>Minimum trust score required before an AI-checked article can be published.</p>
            <label for="publishing_threshold">
                Threshold:
                <output id="publishing_threshold_output"><?= (int)$settings['publishing_threshold'] ?>%</output>
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
        </section>

        <section>
            <h2>Dimension Weights</h2>
            <p>Optional backend weights sent to the n8n workflow for score calculation.</p>

            <div class="admin-ai-weight-row">
                <label for="factual_accuracy_weight">Factual Accuracy</label>
                <input id="factual_accuracy_weight" type="range" name="factual_accuracy_weight" min="0" max="100" value="<?= (int)$settings['factual_accuracy_weight'] ?>" data-output="factual_accuracy_weight_output">
                <output id="factual_accuracy_weight_output"><?= (int)$settings['factual_accuracy_weight'] ?>%</output>
            </div>

            <div class="admin-ai-weight-row">
                <label for="source_quality_weight">Source Quality</label>
                <input id="source_quality_weight" type="range" name="source_quality_weight" min="0" max="100" value="<?= (int)$settings['source_quality_weight'] ?>" data-output="source_quality_weight_output">
                <output id="source_quality_weight_output"><?= (int)$settings['source_quality_weight'] ?>%</output>
            </div>

            <div class="admin-ai-weight-row">
                <label for="bias_detection_weight">Bias Detection</label>
                <input id="bias_detection_weight" type="range" name="bias_detection_weight" min="0" max="100" value="<?= (int)$settings['bias_detection_weight'] ?>" data-output="bias_detection_weight_output">
                <output id="bias_detection_weight_output"><?= (int)$settings['bias_detection_weight'] ?>%</output>
            </div>
        </section>

        <section>
            <h2>Strict Mode</h2>
            <p>Block publishing when the AI workflow reports high-severity issues.</p>
            <label class="admin-ai-switch-row">
                <input type="checkbox" name="strict_mode" value="1" <?= (int)$settings['strict_mode'] === 1 ? 'checked' : '' ?>>
                <span class="admin-ai-switch"></span>
                <strong><?= (int)$settings['strict_mode'] === 1 ? 'Enabled' : 'Disabled' ?></strong>
            </label>
        </section>

        <div>
            <button type="submit" class="btn btn-primary">Save Calibration Settings</button>
        </div>
    </form>
</div>
</main>
</div>

<script>
document.querySelectorAll('.admin-ai-form input[type="range"]').forEach(function (input) {
    var output = document.getElementById(input.dataset.output);
    input.addEventListener('input', function () {
        if (output) {
            output.textContent = input.value + '%';
        }
    });
});
</script>
<?php page_foot(); ?>
