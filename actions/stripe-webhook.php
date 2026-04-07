<?php
set_time_limit(30);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

// ── Logging helper ───────────────────────────────────────────────────
function wh_log(string $msg): void {
    $logFile = __DIR__ . '/../logs/webhook.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' | ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
}

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

// Log EVERY incoming request before anything else — even bad ones
wh_log('--- Incoming request ---');
wh_log('Signature header present: ' . ($sigHeader ? 'YES' : 'NO'));
wh_log('Payload length: ' . strlen($payload) . ' bytes');

// Try to decode the type without verification so we can log it even if sig fails
$raw = json_decode($payload, true);
wh_log('Raw event type: ' . ($raw['type'] ?? 'unknown'));

// ── Verify signature ─────────────────────────────────────────────────
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    wh_log('SIGNATURE FAILED — wrong webhook secret? Configured secret starts with: ' . substr(STRIPE_WEBHOOK_SECRET, 0, 20));
    wh_log('Error: ' . $e->getMessage());
    http_response_code(400);
    exit;
} catch (\Exception $e) {
    wh_log('Webhook parse error: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

http_response_code(200);
echo 'ok';
flush();

wh_log('Signature OK — processing event: ' . $event->type);

// ── checkout.session.completed → upgrade user ─────────────────────────
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $userId  = (int)($session->metadata->user_id ?? 0);

    wh_log("checkout.session.completed: user_id=$userId customer={$session->customer} sub={$session->subscription}");

    if ($userId) {
        $rows = DB::execute(
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
        wh_log("Upgraded user $userId to premium — rows affected: $rows");
    } else {
        wh_log('ERROR: no user_id in session metadata');
    }
}

// ── customer.subscription.updated ────────────────────────────────────
// Fires when user schedules cancellation (cancel_at = future timestamp)
// or reactivates (cancel_at = null). Subscription is still ACTIVE either way.
// Never downgrade here — only in subscription.deleted.
if ($event->type === 'customer.subscription.updated') {
    $subscription = $event->data->object;
    $subId        = $subscription->id;
    $cancelAt     = $subscription->cancel_at;   // Unix timestamp or null
    $status       = $subscription->status;

    wh_log("subscription.updated: sub=$subId status=$status cancel_at=" . ($cancelAt ?? 'null'));

    if ($cancelAt) {
        // User scheduled a cancellation — record the end date, keep them premium
        $rows = DB::execute(
            "UPDATE users
             SET subscription_cancel_at = FROM_UNIXTIME(?)
             WHERE stripe_subscription_id = ?",
            [$cancelAt, $subId]
        );
        wh_log("Stored cancel_at for sub $subId — rows affected: $rows");
        if ($rows === 0) {
            // No row matched — log what we have in DB for this sub ID
            $found = DB::first("SELECT id, role, stripe_subscription_id FROM users WHERE stripe_subscription_id = ?", [$subId]);
            wh_log("No matching row! DB lookup result: " . json_encode($found));
            // Also log all subscription IDs to help diagnose mismatch
            $allSubs = DB::query("SELECT id, stripe_subscription_id FROM users WHERE stripe_subscription_id IS NOT NULL");
            wh_log("All non-null sub IDs in DB: " . json_encode($allSubs));
        }
    } else {
        // Cancellation was reversed — clear the scheduled cancel date
        $rows = DB::execute(
            "UPDATE users SET subscription_cancel_at = NULL WHERE stripe_subscription_id = ?",
            [$subId]
        );
        wh_log("Cleared cancel_at for sub $subId (reactivated) — rows affected: $rows");
    }
}

// ── customer.subscription.deleted → downgrade user ───────────────────
// Fires when the subscription period actually ends.
if ($event->type === 'customer.subscription.deleted') {
    $subscription = $event->data->object;
    $subId        = $subscription->id;

    $rows = DB::execute(
        "UPDATE users
         SET role = 'free', is_premium = 0,
             stripe_subscription_id = NULL,
             subscription_cancel_at = NULL
         WHERE stripe_subscription_id = ?",
        [$subId]
    );
    wh_log("subscription.deleted: downgraded user for sub $subId — rows affected: $rows");
}

wh_log('Done.');