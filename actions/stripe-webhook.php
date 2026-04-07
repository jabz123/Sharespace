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

// subscription succeed, upgrade user
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
                 subscribed_at = NOW()
             WHERE id = ?",
            [
                $session->customer,
                $session->subscription,
                $userId,
            ]
        );
    }
}

// cancel subscription and downgrade user 
if ($event->type === 'customer.subscription.updated') {
    $subscription = $event->data->object;

    if ($subscription->cancel_at || $subscription->canceled_at) {
        DB::execute(
            "UPDATE users
             SET role = 'free', is_premium = 0,
                 stripe_subscription_id = NULL
             WHERE stripe_subscription_id = ?",
            [$subscription->id]
        );
    }
}

if ($event->type === 'customer.subscription.deleted') {
    $subscription = $event->data->object;
    DB::execute(
        "UPDATE users
         SET role = 'free', is_premium = 0,
             stripe_subscription_id = NULL
         WHERE stripe_subscription_id = ?",
        [$subscription->id]
    );
}
