<?php
// this is to create stripe billing portal session when user clicks manage subscription
session_start();

require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

$auth = new AuthController();
$user = $auth->currentUser();
//kill session if user has no stripe customer id which means not subscribed.
if (!$user || !$user->stripe_customer_id) {
    die('STOPPING: No user or no stripe_customer_id');
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
//create portal session
try {
    $session = \Stripe\BillingPortal\Session::create([
        'customer' => $user->stripe_customer_id,
        'return_url' => 'https://sharedspace.tech/pages/subscription.php', 
    ]);

    echo 'Portal session created: ' . $session->url . '<br>';
    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    die('ERROR: ' . $e->getMessage());
}
