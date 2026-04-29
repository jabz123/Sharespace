<?php

session_start();

//this is the endpoint that creates a Stripe checkout session when a user clicks "Upgrade to Premium"
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

$auth = new AuthController();
$user = $auth->currentUser();
// only free users can access this page.
if (!$user || $user->role !== 'free') {
    header('Location: /pages/subscription.php');
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
//create new stripe checkout session for new subscription 
try {
    $session = \Stripe\Checkout\Session::create([
        'mode' => 'subscription',
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price' => STRIPE_PRICE_ID,
            'quantity' => 1,
        ]],
        'customer_email' => $user->email,
        'metadata' => ['user_id' => $user->id],
        'success_url' => 'https://sharedspace.tech/subscribe-success.php?session_id={CHECKOUT_SESSION_ID}', 
        'cancel_url' => 'https://sharedspace.tech/subscribe-cancel.php', 
    ]);

    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    die('Error: ' . $e->getMessage());
}
