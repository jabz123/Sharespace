<?php

// Correct path to logs directory
$logDir = __DIR__ . '/../logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logFile = $logDir . '/webhook-raw.log';

// Log every single request
file_put_contents($logFile, 
    date('Y-m-d H:i:s') . " | Method: " . $_SERVER['REQUEST_METHOD'] . 
    " | Body length: " . strlen(file_get_contents('php://input')) . "\n", 
    FILE_APPEND
);


set_time_limit(30);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

// DB logging — check webhook_log table in phpMyAdmin to debug
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
    } catch (\Exception $e) {}
}

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

wh_log('HIT sig=' . ($sigHeader ? 'present' : 'MISSING') . ' bytes=' . strlen($payload));

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, STRIPE_WEBHOOK_SECRET);
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    wh_log('SIGNATURE FAILED: ' . $e->getMessage());
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

//  checkout.session.completed user will be upgraded to premium
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

//customer.subscription.updated, will store cancellation date 
// fires when user cancels via billing portal.
// stripe sets cancel_at_period_end=true OR cancel_at=timestamp.
// end date is stored so UI can show cancel on date.
if ($event->type === 'customer.subscription.updated') {
    $sub   = $event->data->object;
    $subId = $sub->id;

    // cancel_at_period_end = true means "cancel at end of current period"
    $cancelAtPeriodEnd = (bool)($sub->cancel_at_period_end ?? false);
    // cancel_at = explicit unix timestamp (set when cancel_at_period_end is true)
    $cancelAt = $sub->cancel_at ?? null;
    // current_period_end = when the current billing period ends
    $periodEnd = $sub->current_period_end ?? null;

    wh_log("sub.updated sub=$subId cancel_at_period_end=$cancelAtPeriodEnd cancel_at=$cancelAt period_end=$periodEnd");

    if ($cancelAtPeriodEnd || $cancelAt) {
        // use cancel_at if set, otherwise fall back to current_period_end
        $endTimestamp = $cancelAt ?: $periodEnd;
        $rows = DB::execute(
            "UPDATE users SET subscription_cancel_at=FROM_UNIXTIME(?)
             WHERE stripe_subscription_id=?",
            [$endTimestamp, $subId]
        );
        wh_log("stored cancel_at=$endTimestamp rows_affected=$rows");

        if ($rows === 0) {
            $all = DB::query("SELECT id, stripe_subscription_id FROM users WHERE stripe_subscription_id IS NOT NULL");
            wh_log("NO MATCH — subs in DB: " . json_encode($all));
        }
    } else {
        // user reactivated/ resubcribed — clear the cancel date
        $rows = DB::execute(
            "UPDATE users SET subscription_cancel_at=NULL WHERE stripe_subscription_id=?",
            [$subId]
        );
        wh_log("cleared cancel_at (reactivated) rows_affected=$rows");
    }
}

//customer.subscription.deleted downgrade user back to free 
// fires when the billing period actually ends after cancellation.
//alr tested if i manually cancel the subscription on the strripe dashboard. user will auto revert to free.
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
        $all = DB::query("SELECT id, stripe_subscription_id FROM users WHERE stripe_subscription_id IS NOT NULL");
        wh_log("NO MATCH — subs in DB: " . json_encode($all));
    }
}

wh_log('done');