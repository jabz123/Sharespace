<?php
session_start();

require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

$auth = new AuthController();
$user = $auth->currentUser();


if (!$user || !$user->stripe_customer_id) {
    die("STOPPING: No user or no stripe_customer_id");
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $session = \Stripe\BillingPortal\Session::create([
        'customer'   => $user->stripe_customer_id,
        'return_url' => 'https://sharedspace.tech/pages/subscription.php', //http://47.128.202.6/pages/subscription.php
    ]);
    
    echo "Portal session created: " . $session->url . "<br>";
    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    die("ERROR: " . $e->getMessage());
}
