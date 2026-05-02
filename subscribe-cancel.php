<?php

//display page for when user cancels subscription upgrade

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

page_head('Upgrade Cancelled');
?>
<div class="dashboard-layout user-dashboard-shell">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header('Upgrade Cancelled'); ?>

        <div class="page-content" style="display:flex;justify-content:center;padding-top:60px;">
            <div class="card dash-panel-card" style="max-width:500px;width:100%;text-align:center;padding:48px 36px;">
                <div style="font-size:56px;margin-bottom:16px;">↩</div>
                <h2 style="font-size:24px;font-weight:700;margin-bottom:8px;color:var(--text-primary);">No changes made</h2>
                <p class="text-muted" style="margin-bottom:32px;font-size:14px;">You cancelled the upgrade. Your account remains on the free plan.</p>
                <a href="/pages/subscription.php" class="btn btn-primary" style="padding:10px 28px;">Back to Subscription</a>
            </div>
        </div>
    </main>
</div>
<?php page_foot(); ?>
