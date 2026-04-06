<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

page_head('Subscription Confirmed');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<div class="page-content" style="text-align:center;padding-top:80px">
    <div style="font-size:56px;margin-bottom:16px">🎉</div>
    <h2 style="font-size:24px;font-weight:700;margin-bottom:8px">You're now Premium!</h2>
    <p class="text-muted" style="margin-bottom:32px">Your subscription is active. Enjoy full access to SharedSpace.</p>
    <a href="/dashboard.php" class="btn btn-primary">Go to Dashboard</a>
</div>
</main>
</div>
<?php page_foot(); ?>
