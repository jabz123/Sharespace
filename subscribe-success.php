<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

page_head('Subscription Confirmed');
?>
<div class="dashboard-layout user-dashboard-shell">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header('Welcome to Premium'); ?>

        <div class="page-content" style="display: flex; justify-content: center; padding-top: 60px;">
            <div class="card dash-panel-card" style="max-width: 500px; width: 100%; text-align: center; padding: 48px 36px;">
                <div style="font-size: 56px; margin-bottom: 16px;">🎉</div>
                <h2 style="font-size: 24px; font-weight: 700; margin-bottom: 8px; color: var(--text-primary);">
                    You're now Premium!
                </h2>
                <p class="text-muted" style="margin-bottom: 32px; font-size: 14px;">
                    Your subscription is active. Enjoy full access to SharedSpace.
                </p>
                <a href="/dashboard.php" class="btn btn-primary" style="padding: 10px 28px;">
                    Go to Dashboard
                </a>
            </div>
        </div>
    </main>
</div>
<?php page_foot(); ?>