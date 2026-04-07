<?php
set_time_limit(30);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

// ── Logging helper ──────────────────────────────────────────────────
function wh_log(string $msg): void {
    $logFile = __DIR__ . '/../logs/webhook.log';
    $line    = date('Y-m-d H:i:s') . ' | ' . $msg . PHP_EOL;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

// ── Verify signature ────────────────────────────────────────────────
$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (\Exception $e) {
    wh_log('Signature verification failed: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

http_response_code(200);
echo 'ok';
flush();

wh_log('Received event: ' . $event->type);

// ── checkout.session.completed → upgrade user ────────────────────────
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $userId  = (int)($session->metadata->user_id ?? 0);

    if ($userId) {
        // Try with subscription_cancel_at column first; fall back if column missing
        try {
            DB::execute(
                "UPDATE users
                 SET role = 'premium',
                     is_premium = 1,
                     stripe_customer_id = ?,
                     stripe_subscription_id = ?,
                     subscribed_at = NOW(),
                     subscription_cancel_at = NULL
                 WHERE id = ?",
                [$session->customer, $session->subscription, $userId]
            );
        } catch (\Exception $e) {
            // Column doesn't exist yet — run without it
            DB::execute(
                "UPDATE users
                 SET role = 'premium',
                     is_premium = 1,
                     stripe_customer_id = ?,
                     stripe_subscription_id = ?,
                     subscribed_at = NOW()
                 WHERE id = ?",
                [$session->customer, $session->subscription, $userId]
            );
        }
        wh_log("Upgraded user $userId to premium");
    } else {
        wh_log('checkout.session.completed: no user_id in metadata');
    }
}

// ── customer.subscription.updated ───────────────────────────────────
// Stripe fires this when user schedules a cancellation (cancel_at is set to future date)
// or when they reactivate (cancel_at cleared).
// The subscription is still ACTIVE — do NOT downgrade here.
// Downgrade only happens in customer.subscription.deleted.
if ($event->type === 'customer.subscription.updated') {
    $subscription = $event->data->object;
    $subId        = $subscription->id;
    $cancelAt     = $subscription->cancel_at; // Unix timestamp or null

    wh_log("subscription.updated: sub=$subId cancel_at=" . ($cancelAt ?? 'null') . " status=" . $subscription->status);

    try {
        if ($cancelAt) {
            // Cancellation scheduled — store the end date, keep user premium
            DB::execute(
                "UPDATE users
                 SET subscription_cancel_at = FROM_UNIXTIME(?)
                 WHERE stripe_subscription_id = ?",
                [$cancelAt, $subId]
            );
            wh_log("Stored cancel_at for sub $subId");
        } else {
            // Cancellation cleared (reactivated) — clear the cancel date
            DB::execute(
                "UPDATE users
                 SET subscription_cancel_at = NULL
                 WHERE stripe_subscription_id = ?",
                [$subId]
            );
            wh_log("Cleared cancel_at for sub $subId (reactivated)");
        }
    } catch (\Exception $e) {
        // subscription_cancel_at column doesn't exist yet — safe to ignore,
        // user stays premium. Add the column: ALTER TABLE users ADD COLUMN
        // subscription_cancel_at DATETIME NULL DEFAULT NULL;
        wh_log('subscription.updated DB error (column missing?): ' . $e->getMessage());
    }
}

// ── customer.subscription.deleted → downgrade user ──────────────────
// This fires when the subscription period actually ends.
if ($event->type === 'customer.subscription.deleted') {
    $subscription = $event->data->object;
    $subId        = $subscription->id;

    try {
        DB::execute(
            "UPDATE users
             SET role = 'free', is_premium = 0,
                 stripe_subscription_id = NULL,
                 subscription_cancel_at = NULL
             WHERE stripe_subscription_id = ?",
            [$subId]
        );
    } catch (\Exception $e) {
        // Fall back if subscription_cancel_at column missing
        DB::execute(
            "UPDATE users
             SET role = 'free', is_premium = 0,
                 stripe_subscription_id = NULL
             WHERE stripe_subscription_id = ?",
            [$subId]
        );
    }
    wh_log("Downgraded user for sub $subId to free");
}