<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../vendor/stripe/stripe-php/init.php';

$auth = new AuthController();
$user = $auth->currentUser();

if (!$user || $user->role !== 'free') {
    header('Location: /pages/subscription.php');
    exit;
}

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

try {
    $session = \Stripe\Checkout\Session::create([
        'mode'                => 'subscription',
        'payment_method_types' => ['card'],
        'line_items'          => [[
            'price'    => STRIPE_PRICE_ID,
            'quantity' => 1,
        ]],
        'customer_email'      => $user->email,
        'metadata'            => ['user_id' => $user->id],
        'success_url' => 'https://sharedspace.tech/subscribe-success.php?session_id={CHECKOUT_SESSION_ID}', //http://47.128.202.6
        'cancel_url'  => 'https://sharedspace.tech/subscribe-cancel.php', //http://47.128.202.6
    ]);

    header('Location: ' . $session->url);
    exit;
} catch (\Exception $e) {
    die("Error: " . $e->getMessage());
}
