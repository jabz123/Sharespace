<?php
set_time_limit(30);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

// ── DB logging (visible in phpMyAdmin) ──────────────────────────────
// Creates the table automatically on first run.
function wh_log(string $msg): void {
    try {
        DB::execute(
            "CREATE TABLE IF NOT EXISTS webhook_log (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                created_at DATETIME DEFAULT NOW(),
                message    TEXT
            )"
        );
        DB::execute("INSERT INTO webhook_log (message) VALUES (?)", [$msg]);
    } catch (\Exception $e) {
        // If DB logging fails there's nothing we can do — carry on
    }
}

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

wh_log('HIT sig=' . ($sigHeader ? 'present' : 'MISSING') . ' bytes=' . strlen($payload));

$raw = json_decode($payload, true);
wh_log('raw_type=' . ($raw['type'] ?? 'none') . ' obj_id=' . ($raw['data']['object']['id'] ?? 'n/a'));

// ── Signature verification ───────────────────────────────────────────
try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    wh_log('SIGNATURE FAILED — secret in config starts with: ' . substr(STRIPE_WEBHOOK_SECRET, 0, 22));
    http_response_code(400);
    exit;
} catch (\Exception $e) {
    wh_log('PARSE ERROR: ' . $e->getMessage());
    http_response_code(400);
    exit;
}

http_response_code(200);
echo 'ok';
flush();

wh_log('SIG OK event=' . $event->type);

// ── checkout.session.completed → upgrade ─────────────────────────────
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $userId  = (int)($session->metadata->user_id ?? 0);
    wh_log("checkout user_id=$userId customer={$session->customer} sub={$session->subscription}");

    if ($userId) {
        $rows = DB::execute(
            "UPDATE users
             SET role='premium', is_premium=1,
                 stripe_customer_id=?,
                 stripe_subscription_id=?,
                 subscribed_at=NOW(),
                 subscription_cancel_at=NULL
             WHERE id=?",
            [$session->customer, $session->subscription, $userId]
        );
        wh_log("upgraded user $userId rows_affected=$rows");
    }
}

// ── customer.subscription.updated ────────────────────────────────────
// Fires when cancellation is SCHEDULED (cancel_at = future unix timestamp).
// Subscription is still active — do NOT downgrade here.
if ($event->type === 'customer.subscription.updated') {
    $sub      = $event->data->object;
    $subId    = $sub->id;
    $cancelAt = $sub->cancel_at;
    $status   = $sub->status;
    wh_log("sub.updated sub=$subId status=$status cancel_at=" . ($cancelAt ?? 'null'));

    if ($cancelAt) {
        $rows = DB::execute(
            "UPDATE users SET subscription_cancel_at=FROM_UNIXTIME(?) WHERE stripe_subscription_id=?",
            [$cancelAt, $subId]
        );
        wh_log("stored cancel_at rows_affected=$rows");

        if ($rows === 0) {
            // No row matched — log what's actually in the DB so we can see the mismatch
            $all = DB::query(
                "SELECT id, stripe_subscription_id FROM users WHERE stripe_subscription_id IS NOT NULL"
            );
            wh_log("NO MATCH — subs in DB: " . json_encode($all));
        }
    } else {
        $rows = DB::execute(
            "UPDATE users SET subscription_cancel_at=NULL WHERE stripe_subscription_id=?",
            [$subId]
        );
        wh_log("cleared cancel_at (reactivated) rows_affected=$rows");
    }
}

// ── customer.subscription.deleted → downgrade ────────────────────────
// Fires when the billing period actually ends.
if ($event->type === 'customer.subscription.deleted') {
    $sub   = $event->data->object;
    $subId = $sub->id;
    wh_log("sub.deleted sub=$subId — downgrading user");

    $rows = DB::execute(
        "UPDATE users
         SET role='free', is_premium=0,
             stripe_subscription_id=NULL,
             subscription_cancel_at=NULL
         WHERE stripe_subscription_id=?",
        [$subId]
    );
    wh_log("downgraded rows_affected=$rows");

    if ($rows === 0) {
        $all = DB::query(
            "SELECT id, stripe_subscription_id FROM users WHERE stripe_subscription_id IS NOT NULL"
        );
        wh_log("NO MATCH — subs in DB: " . json_encode($all));
    }
}

wh_log('done');