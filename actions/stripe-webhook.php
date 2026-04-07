<?php
set_time_limit(30);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

$payload   = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload,
        $sigHeader,
        STRIPE_WEBHOOK_SECRET
    );
} catch (\Exception $e) {
    http_response_code(400);
    exit;
}

http_response_code(200);
echo 'ok';
flush();

// Subscription payment succeeded — upgrade user to premium
if ($event->type === 'checkout.session.completed') {
    $session = $event->data->object;
    $userId  = (int)($session->metadata->user_id ?? 0);

    if ($userId) {
        DB::execute(
            "UPDATE users
             SET role = 'premium',
                 is_premium = 1,
                 stripe_customer_id = ?,
                 stripe_subscription_id = ?,
                 subscribed_at = NOW(),
                 subscription_cancel_at = NULL
             WHERE id = ?",
            [
                $session->customer,
                $session->subscription,
                $userId,
            ]
        );
    }
}

// Subscription was updated — only act if a cancellation was scheduled or reversed.
// Do NOT downgrade the user here — the subscription is still active until cancel_at.
// The actual downgrade happens in customer.subscription.deleted below.
if ($event->type === 'customer.subscription.updated') {
    $subscription = $event->data->object;

    if ($subscription->cancel_at) {
        // User scheduled a cancellation — store end date so the UI can show
        // "Your plan cancels on [date]", but keep them premium until then.
        DB::execute(
            "UPDATE users
             SET subscription_cancel_at = FROM_UNIXTIME(?)
             WHERE stripe_subscription_id = ?",
            [$subscription->cancel_at, $subscription->id]
        );
    } else {
        // cancel_at was cleared — user reactivated their subscription.
        DB::execute(
            "UPDATE users
             SET subscription_cancel_at = NULL
             WHERE stripe_subscription_id = ?",
            [$subscription->id]
        );
    }
}

// Subscription has fully ended — now downgrade the user
if ($event->type === 'customer.subscription.deleted') {
    $subscription = $event->data->object;
    DB::execute(
        "UPDATE users
         SET role = 'free', is_premium = 0,
             stripe_subscription_id = NULL,
             subscription_cancel_at = NULL
         WHERE stripe_subscription_id = ?",
        [$subscription->id]
    );
}