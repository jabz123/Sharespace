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
    "SELECT * FROM landing_pricing_features
     WHERE plan_id = ? AND is_included = 1
     ORDER BY display_order ASC",
    [$plan['id']]
) : [];

page_head('Subscription');
?>
<style>
body.page-subscription {
    background:
        radial-gradient(circle at top left, rgba(245,166,35,0.08), transparent 24%),
        linear-gradient(180deg, #060b14 0%, #09111d 100%) !important;
    color: #f5f8ff !important;
}

body.page-subscription .dashboard-layout main,
body.page-subscription .page-content {
    background: transparent !important;
}

body.page-subscription .dash-header {
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 22%),
        linear-gradient(180deg, rgba(8,14,26,0.94) 0%, rgba(8,14,26,0.84) 100%) !important;
    border-bottom: 1px solid rgba(245,166,35,0.14) !important;
}

body.page-subscription .dash-title,
body.page-subscription .dash-subtitle,
body.page-subscription .text-muted {
    color: #dbe6f6 !important;
}

body.page-subscription .page-content {
    max-width: 1380px;
    padding: 34px 28px 48px !important;
    display: flex;
    flex-direction: column;
    gap: 18px;
}

body.page-subscription .sub-status-card,
body.page-subscription .sub-details-card,
body.page-subscription .sub-features-card,
body.page-subscription .sub-upgrade-card {
    background:
        radial-gradient(circle at top right, rgba(245,166,35,0.08), transparent 20%),
        linear-gradient(180deg, rgba(11,17,31,0.98) 0%, rgba(9,14,25,1) 100%) !important;
    border: 1px solid rgba(255,255,255,0.08) !important;
    border-radius: 24px !important;
    box-shadow: 0 22px 48px rgba(0,0,0,0.22) !important;
    color: #f5f8ff !important;
}

body.page-subscription .sub-status-card,
body.page-subscription .sub-details-card,
body.page-subscription .sub-features-card {
    padding: 26px 26px !important;
}

body.page-subscription .sub-upgrade-card {
    padding: 30px 28px !important;
    max-width: 760px;
}

body.page-subscription .sub-status-card h3,
body.page-subscription .sub-details-card h3,
body.page-subscription .sub-features-card h3,
body.page-subscription .sub-upgrade-card h2,
body.page-subscription .sub-value,
body.page-subscription .sub-feature-item,
body.page-subscription .sub-price-main {
    color: #ffffff !important;
}

body.page-subscription .sub-status-card p,
body.page-subscription .sub-features-sub,
body.page-subscription .sub-label,
body.page-subscription .sub-price-suffix,
body.page-subscription .sub-price-desc {
    color: #9caeca !important;
}

body.page-subscription .sub-status-icon {
    background: rgba(33, 201, 105, 0.12) !important;
    color: #62e59a !important;
}

body.page-subscription .sub-badge.active {
    background: rgba(33, 201, 105, 0.12) !important;
    border: 1px solid rgba(33, 201, 105, 0.28) !important;
    color: #62e59a !important;
}

body.page-subscription .sub-badge.cancelling {
    background: rgba(245,166,35,0.12) !important;
    border: 1px solid rgba(245,166,35,0.24) !important;
    color: #ffd37a !important;
}

body.page-subscription .sub-details-grid,
body.page-subscription .sub-features-grid {
    gap: 24px !important;
}

body.page-subscription .btn-manage,
body.page-subscription .btn-upgrade {
    min-height: 48px;
    padding: 0 20px !important;
    border-radius: 14px !important;
    border: 1px solid rgba(245,166,35,0.18) !important;
    background: linear-gradient(135deg, #f4a321 0%, #ffca61 100%) !important;
    color: #08111f !important;
    font-weight: 700 !important;
}

body.page-subscription .sub-feature-item {
    border-bottom: 1px solid rgba(255,255,255,0.06);
    padding: 8px 0;
}

body.page-subscription .sub-check {
    color: #62e59a !important;
    font-weight: 700;
}

@media (max-width: 768px) {
    body.page-subscription .page-content {
        padding: 22px 18px 32px !important;
    }
}
</style>
<div class="dashboard-layout">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header('Subscription', 'Manage your plan'); ?>
        <?php flash_messages(); ?>

        <div class="page-content">
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
