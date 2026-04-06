<?php
set_time_limit(30);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

$payload = file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sigHeader, STRIPE_WEBHOOK_SECRET
    );
} catch (\Exception $e) {
    http_response_code(400);
    exit;
}

http_response_code(200);
echo 'ok';
flush();

if ($event->type === 'customer.subscription.updated') {
    $subscription = $event->data->object;
    
    // If subscription is marked for cancellation, downgrade user
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