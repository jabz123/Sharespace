<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/db.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

if (!in_array($user->role, ['free', 'premium'])) {
    header('Location: /dashboard.php');
    exit;
}

$plan = DB::first(
    "SELECT * FROM landing_pricing_plans WHERE name = 'Premium' LIMIT 1"
);
$features = $plan ? DB::query(
    'SELECT * FROM landing_pricing_features
     WHERE plan_id = ? AND is_included = 1
     ORDER BY display_order ASC',
    [$plan['id']]
) : [];

page_head('Subscription');
?>
<div class="dashboard-layout user-dashboard-shell">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header('Subscription', 'Manage your plan'); ?>
        <?php flash_messages(); ?>

        <div class="page-content sub-page-content">
            <?php if ($user->role === 'premium'): ?>
                <?php $cancelAt = $user->subscription_cancel_at; ?>
                <div class="sub-status-card">
                    <div class="sub-status-left">
                        <div class="sub-status-icon"><?= $cancelAt ? 'Pending' : 'Live' ?></div>
                        <div>
                            <h3>Premium Subscription</h3>
                            <?php if ($cancelAt): ?>
                                <p>Your plan is active but cancels on <strong><?= htmlspecialchars(date('d M Y', strtotime($cancelAt))) ?></strong></p>
                            <?php else: ?>
                                <p>Your subscription is active</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <span class="sub-badge <?= $cancelAt ? 'cancelling' : 'active' ?>">
                        <?= $cancelAt ? 'Cancelling' : 'Active' ?>
                    </span>
                </div>

                <?php if ($cancelAt): ?>
                <div class="sub-status-card" style="background:var(--warning-bg,#fff8e1);border-color:var(--warning-border,#ffe082);margin-top:0">
                    <div class="sub-status-left">
                        <div>
                            <p style="margin:0">You still have full premium access until <strong><?= htmlspecialchars(date('d M Y', strtotime($cancelAt))) ?></strong>. You can reactivate anytime through the billing portal.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <div class="sub-details-card">
                    <h3>Subscription Details</h3>
                    <div class="sub-details-grid">
                        <div>
                            <p class="sub-label">PLAN</p>
                            <p class="sub-value">Premium Monthly</p>
                        </div>
                        <div>
                            <p class="sub-label">PRICE</p>
                            <p class="sub-value">
                                <?= htmlspecialchars($plan['price'] ?? '$12.00') ?>
                                / <?= htmlspecialchars($plan['price_suffix'] ?? 'Monthly') ?>
                            </p>
                        </div>
                    </div>
                    <a href="/actions/create-portal-session.php" class="btn btn-primary btn-manage">
                        <?= $cancelAt ? 'Reactivate / Manage Subscription' : 'Manage Subscription' ?>
                    </a>
                </div>

                <div class="sub-features-card">
                    <h3>What's Included</h3>
                    <p class="sub-features-sub">Everything included in your premium plan</p>
                    <div class="sub-features-grid">
                        <?php foreach ($features as $f): ?>
                            <div class="sub-feature-item">
                                <span class="sub-check">Included</span>
                                <?= htmlspecialchars($f['feature_text']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php else: ?>

                <div class="sub-upgrade-card">
                    <h2><?= htmlspecialchars($plan['name'] ?? 'Premium') ?></h2>
                    <div class="sub-price-row">
                        <span class="sub-price-main"><?= htmlspecialchars($plan['price'] ?? '$12') ?></span>
                        <span class="sub-price-suffix">/<?= htmlspecialchars($plan['price_suffix'] ?? 'per month') ?></span>
                    </div>
                    <p class="sub-price-desc"><?= htmlspecialchars($plan['description'] ?? '') ?></p>

                    <div class="sub-features-list">
                        <?php foreach ($features as $f): ?>
                            <div class="sub-feature-item">
                                <span class="sub-check">Included</span>
                                <?= htmlspecialchars($f['feature_text']) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <form method="POST" action="/actions/create-checkout.php">
                        <button type="submit" class="btn btn-primary btn-upgrade">
                            Upgrade to Premium
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?php page_foot(); ?>