<?php

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/LandingPageController.php';

$auth = new AuthController();
$user = $auth->currentUser();

if (!$user) {
    header('Location: /');
    exit;
}

if ($user->role !== 'system_admin') {
    header('Location: /dashboard.php');
    exit;
}

$landingCtrl = new LandingPageController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // HERO SECTION
    if (isset($_POST['update_hero'])) {
        $landingCtrl->updateHero($_POST);
        header('Location: /pages/admin-landing.php?hero_updated=1#hero-section');
        exit;
    }

    // DEMO VIDEO
    if (isset($_POST['update_demo_video'])) {
        $landingCtrl->updateDemoVideo($_POST);
        header('Location: /pages/admin-landing.php?video_updated=1#video-section');
        exit;
    }

    // FEATURE CARDS
    if (isset($_POST['add_feature'])) {
        $landingCtrl->addFeature($_POST);
        header('Location: /pages/admin-landing.php?feature_added=1#features-section');
        exit;
    }

    if (isset($_POST['update_feature'])) {
        $landingCtrl->updateFeature((int) $_POST['feature_id'], $_POST);
        header('Location: /pages/admin-landing.php?feature_updated=1#features-section');
        exit;
    }

    if (isset($_POST['delete_feature'])) {
        $landingCtrl->deleteFeature((int) $_POST['feature_id']);
        header('Location: /pages/admin-landing.php?feature_deleted=1#features-section');
        exit;
    }

    // HOW IT WORKS STEPS
    if (isset($_POST['add_step'])) {
        $landingCtrl->addStep($_POST);
        header('Location: /pages/admin-landing.php?step_added=1#steps-section');
        exit;
    }

    if (isset($_POST['update_step'])) {
        $landingCtrl->updateStep((int) $_POST['step_id'], $_POST);
        header('Location: /pages/admin-landing.php?step_updated=1#steps-section');
        exit;
    }

    if (isset($_POST['delete_step'])) {
        $landingCtrl->deleteStep((int) $_POST['step_id']);
        header('Location: /pages/admin-landing.php?step_deleted=1#steps-section');
        exit;
    }
}

$hero = $landingCtrl->getHero();
$demoVideo = $landingCtrl->getDemoVideo();
$features = $landingCtrl->getFeatures();
$steps = $landingCtrl->getSteps();

$editFeature = null;
$editStep = null;

if (isset($_GET['edit_feature'])) {
    $editFeature = $landingCtrl->getFeatureById((int) $_GET['edit_feature']);
}

if (isset($_GET['edit_step'])) {
    $editStep = $landingCtrl->getStepById((int) $_GET['edit_step']);
}

page_head('Manage Landing Page');
?>

<div class="dashboard-layout">
    <?php sidebar($user); ?>

    <main>
        <?php dash_header('Manage Landing Page', 'Edit homepage content sections'); ?>

        <div class="page-content">

            <!-- HERO SECTION -->
            <div id="hero-section" class="card" style="padding: 24px; margin-bottom: 24px;">
                <h2 style="margin-bottom: 8px;">Hero Section Editor</h2>
                <p style="margin-bottom: 24px; color: #6b7280;">
                    Edit the main banner content shown at the top of the landing page.
                </p>

                <form method="POST">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="badge">Hero Badge</label>
                        <input
                            id="badge"
                            type="text"
                            name="badge"
                            value="<?= htmlspecialchars($hero['badge'] ?? '') ?>"
                            required
                            style="width: 100%;"
                        >
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="hero_title">Hero Title</label>
                        <input
                            id="hero_title"
                            type="text"
                            name="title"
                            value="<?= htmlspecialchars($hero['title'] ?? '') ?>"
                            required
                            style="width: 100%;"
                        >
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="title_highlight">Hero Highlighted Title</label>
                        <input
                            id="title_highlight"
                            type="text"
                            name="title_highlight"
                            value="<?= htmlspecialchars($hero['title_highlight'] ?? '') ?>"
                            required
                            style="width: 100%;"
                        >
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="subtitle">Hero Subtitle</label>
                        <textarea
                            id="subtitle"
                            name="subtitle"
                            rows="4"
                            required
                            style="width: 100%;"
                        ><?= htmlspecialchars($hero['subtitle'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" name="update_hero" class="btn btn-primary">
                        Save Hero Section
                    </button>

                    <?php if (isset($_GET['hero_updated']) && $_GET['hero_updated'] == '1'): ?>
                        <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                            Hero section updated successfully.
                        </div>
                    <?php endif; ?>
                </form>

                <hr style="margin: 24px 0;">

                <h3 style="margin-bottom: 16px;">Current Hero Content</h3>

                <div style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="margin-bottom: 8px;"><strong>Badge:</strong> <?= htmlspecialchars($hero['badge'] ?? '') ?></div>
                    <div style="margin-bottom: 8px;"><strong>Title:</strong> <?= htmlspecialchars($hero['title'] ?? '') ?></div>
                    <div style="margin-bottom: 8px;"><strong>Highlighted Title:</strong> <?= htmlspecialchars($hero['title_highlight'] ?? '') ?></div>
                    <div><strong>Subtitle:</strong> <?= htmlspecialchars($hero['subtitle'] ?? '') ?></div>
                </div>
            </div>

            <!-- DEMO VIDEO SECTION -->
            <div id="video-section" class="card" style="padding: 24px; margin-bottom: 24px;">
                <h2 style="margin-bottom: 8px;">Demo Video Editor</h2>
                <p style="margin-bottom: 24px; color: #6b7280;">
                    Edit the title, subtitle, and YouTube link for the demo video section.
                </p>

                <form method="POST">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="video_title">Section Title</label>
                        <input
                            id="video_title"
                            type="text"
                            name="title"
                            value="<?= htmlspecialchars($demoVideo['title'] ?? '') ?>"
                            required
                            style="width: 100%;"
                        >
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="video_subtitle">Section Subtitle</label>
                        <textarea
                            id="video_subtitle"
                            name="subtitle"
                            rows="3"
                            required
                            style="width: 100%;"
                        ><?= htmlspecialchars($demoVideo['subtitle'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label for="video_url">YouTube Link</label>
                        <input
                            id="video_url"
                            type="text"
                            name="video_url"
                            value="<?= htmlspecialchars($demoVideo['video_url'] ?? '') ?>"
                            placeholder="https://www.youtube.com/watch?v=..."
                            required
                            style="width: 100%;"
                        >
                    </div>

                    <button type="submit" name="update_demo_video" class="btn btn-primary">
                        Save Demo Video
                    </button>

                    <?php if (isset($_GET['video_updated']) && $_GET['video_updated'] == '1'): ?>
                        <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                            Demo video updated successfully.
                        </div>
                    <?php endif; ?>
                </form>

                <hr style="margin: 24px 0;">

                <h3 style="margin-bottom: 16px;">Current Demo Video Settings</h3>

                <div style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                    <div style="margin-bottom: 8px;"><strong>Title:</strong> <?= htmlspecialchars($demoVideo['title'] ?? '') ?></div>
                    <div style="margin-bottom: 8px;"><strong>Subtitle:</strong> <?= htmlspecialchars($demoVideo['subtitle'] ?? '') ?></div>
                    <div><strong>YouTube Link:</strong> <?= htmlspecialchars($demoVideo['video_url'] ?? '') ?></div>
                </div>
            </div>

            <!-- FEATURES SECTION -->
            <div id="features-section" class="card" style="padding: 24px; margin-bottom: 24px;">
                <h2 style="margin-bottom: 8px;">Feature Cards Editor</h2>
                <p style="margin-bottom: 24px; color: #6b7280;">
                    Edit the feature cards shown in the “Built for Trusted Journalism” section.
                </p>

                <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 24px; align-items: start;">
                    <div>
                        <h3 style="margin-bottom: 16px;">Current Feature Cards</h3>

                        <?php if (empty($features)): ?>
                            <div style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                                No feature cards found.
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb;">
                                    <thead style="background: #f9fafb;">
                                        <tr>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Title</th>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Order</th>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Icon</th>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($features as $feature): ?>
                                            <tr>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                                                    <div style="font-weight: 600;"><?= htmlspecialchars($feature['title']) ?></div>
                                                    <div style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                                                        <?= htmlspecialchars($feature['description']) ?>
                                                    </div>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                                                    <?= (int) $feature['display_order'] ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">
                                                    <?= htmlspecialchars($feature['icon_path']) ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                                                    <a href="/pages/admin-landing.php?edit_feature=<?= (int) $feature['id'] ?>#features-section" class="btn btn-primary">Edit</a>

                                                    <form method="POST" style="display: inline-block; margin-left: 8px;">
                                                        <input type="hidden" name="feature_id" value="<?= (int) $feature['id'] ?>">
                                                        <button
                                                            type="submit"
                                                            name="delete_feature"
                                                            class="btn btn-ghost"
                                                            onclick="return confirm('Delete this feature card?');"
                                                        >
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h3 style="margin-bottom: 16px;"><?= $editFeature ? 'Edit Feature Card' : 'Add Feature Card' ?></h3>

                        <form method="POST" style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                            <?php if ($editFeature): ?>
                                <input type="hidden" name="feature_id" value="<?= (int) $editFeature['id'] ?>">
                            <?php endif; ?>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="feature_icon_path">Feature Icon Path</label>
                                <input
                                    id="feature_icon_path"
                                    type="text"
                                    name="icon_path"
                                    value="<?= htmlspecialchars($editFeature['icon_path'] ?? '') ?>"
                                    placeholder="public/icons/landingpage/example.png"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="feature_title">Feature Title</label>
                                <input
                                    id="feature_title"
                                    type="text"
                                    name="title"
                                    value="<?= htmlspecialchars($editFeature['title'] ?? '') ?>"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="feature_description">Feature Description</label>
                                <textarea
                                    id="feature_description"
                                    name="description"
                                    rows="5"
                                    required
                                    style="width: 100%;"
                                ><?= htmlspecialchars($editFeature['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="feature_display_order">Feature Display Order</label>
                                <input
                                    id="feature_display_order"
                                    type="number"
                                    name="display_order"
                                    value="<?= htmlspecialchars($editFeature['display_order'] ?? 0) ?>"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div style="margin-top: 16px;">
                                <?php if ($editFeature): ?>
                                    <button type="submit" name="update_feature" class="btn btn-primary">Update Feature Card</button>
                                    <a href="/pages/admin-landing.php#features-section" class="btn btn-ghost" style="margin-left: 8px;">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_feature" class="btn btn-primary">Add Feature Card</button>
                                <?php endif; ?>

                                <?php if (isset($_GET['feature_updated']) && $_GET['feature_updated'] == '1'): ?>
                                    <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                                        Feature card updated successfully.
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['feature_added']) && $_GET['feature_added'] == '1'): ?>
                                    <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                                        Feature card added successfully.
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['feature_deleted']) && $_GET['feature_deleted'] == '1'): ?>
                                    <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                                        Feature card deleted successfully.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- STEPS SECTION -->
            <div id="steps-section" class="card" style="padding: 24px; margin-bottom: 24px;">
                <h2 style="margin-bottom: 8px;">How It Works Steps Editor</h2>
                <p style="margin-bottom: 24px; color: #6b7280;">
                    Edit the step cards shown in the “How SharedSpace Works” section.
                </p>

                <div style="display: grid; grid-template-columns: 1.3fr 1fr; gap: 24px; align-items: start;">
                    <div>
                        <h3 style="margin-bottom: 16px;">Current How It Works Steps</h3>

                        <?php if (empty($steps)): ?>
                            <div style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                                No steps found.
                            </div>
                        <?php else: ?>
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e5e7eb;">
                                    <thead style="background: #f9fafb;">
                                        <tr>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Step</th>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Order</th>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Icon</th>
                                            <th style="text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($steps as $step): ?>
                                            <tr>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                                                    <div style="font-weight: 600;"><?= htmlspecialchars($step['step_number']) ?> - <?= htmlspecialchars($step['title']) ?></div>
                                                    <div style="font-size: 13px; color: #6b7280; margin-top: 4px;">
                                                        <?= htmlspecialchars($step['description']) ?>
                                                    </div>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb;">
                                                    <?= (int) $step['display_order'] ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; font-size: 13px; color: #6b7280;">
                                                    <?= htmlspecialchars($step['icon_path']) ?>
                                                </td>
                                                <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">
                                                    <a href="/pages/admin-landing.php?edit_step=<?= (int) $step['id'] ?>#steps-section" class="btn btn-primary">Edit</a>

                                                    <form method="POST" style="display: inline-block; margin-left: 8px;">
                                                        <input type="hidden" name="step_id" value="<?= (int) $step['id'] ?>">
                                                        <button
                                                            type="submit"
                                                            name="delete_step"
                                                            class="btn btn-ghost"
                                                            onclick="return confirm('Delete this step?');"
                                                        >
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div>
                        <h3 style="margin-bottom: 16px;"><?= $editStep ? 'Edit Step' : 'Add Step' ?></h3>

                        <form method="POST" style="padding: 16px; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px;">
                            <?php if ($editStep): ?>
                                <input type="hidden" name="step_id" value="<?= (int) $editStep['id'] ?>">
                            <?php endif; ?>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="step_icon_path">Step Icon Path</label>
                                <input
                                    id="step_icon_path"
                                    type="text"
                                    name="icon_path"
                                    value="<?= htmlspecialchars($editStep['icon_path'] ?? '') ?>"
                                    placeholder="public/icons/landingpage/step1write.png"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="step_number">Step Number</label>
                                <input
                                    id="step_number"
                                    type="text"
                                    name="step_number"
                                    value="<?= htmlspecialchars($editStep['step_number'] ?? '') ?>"
                                    placeholder="01"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="step_title">Step Title</label>
                                <input
                                    id="step_title"
                                    type="text"
                                    name="title"
                                    value="<?= htmlspecialchars($editStep['title'] ?? '') ?>"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="step_description">Step Description</label>
                                <textarea
                                    id="step_description"
                                    name="description"
                                    rows="5"
                                    required
                                    style="width: 100%;"
                                ><?= htmlspecialchars($editStep['description'] ?? '') ?></textarea>
                            </div>

                            <div class="form-group" style="margin-bottom: 16px;">
                                <label for="step_display_order">Step Display Order</label>
                                <input
                                    id="step_display_order"
                                    type="number"
                                    name="display_order"
                                    value="<?= htmlspecialchars($editStep['display_order'] ?? 0) ?>"
                                    required
                                    style="width: 100%;"
                                >
                            </div>

                            <div style="margin-top: 16px;">
                                <?php if ($editStep): ?>
                                    <button type="submit" name="update_step" class="btn btn-primary">Update Step</button>
                                    <a href="/pages/admin-landing.php#steps-section" class="btn btn-ghost" style="margin-left: 8px;">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_step" class="btn btn-primary">Add Step</button>
                                <?php endif; ?>

                                <?php if (isset($_GET['step_updated']) && $_GET['step_updated'] == '1'): ?>
                                    <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                                        Step updated successfully.
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['step_added']) && $_GET['step_added'] == '1'): ?>
                                    <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                                        Step added successfully.
                                    </div>
                                <?php endif; ?>

                                <?php if (isset($_GET['step_deleted']) && $_GET['step_deleted'] == '1'): ?>
                                    <div style="margin-top: 12px; padding: 12px 14px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534; border-radius: 8px;">
                                        Step deleted successfully.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>

<?php page_foot(); ?>