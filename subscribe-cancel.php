<?php

//display page for when user cancels subscription upgrade

require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

page_head('Upgrade Cancelled');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<div class="page-content" style="text-align:center;padding-top:80px">
    <div style="font-size:56px;margin-bottom:16px">↩</div>
    <h2 style="font-size:24px;font-weight:700;margin-bottom:8px">No changes made</h2>
    <p class="text-muted" style="margin-bottom:32px">You cancelled the upgrade. Your account remains on the free plan.</p>
    <a href="/pages/subscription.php" class="btn btn-secondary">Back to Subscription</a>
</div>
</main>
</div>
<?php page_foot(); ?>
