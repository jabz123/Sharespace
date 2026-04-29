<?php
//config file. change according to own database parameters

// XAMPP defaults — root got no password. update DB_PASS if have
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'sharedspace');
define('DB_USER', 'shareduser');
define('DB_PASS', 'password123'); 
date_default_timezone_set('Asia/Singapore');

define('APP_BASE_URL', 'https://sharedspace.tech');
define('N8N_FEEDBACK_SENTIMENT_WEBHOOK_URL', 'https://n8n.srv1502312.hstgr.cloud/webhook/sharedspace-feedback-sentiment');
define('N8N_VERIFY_WEBHOOK_URL', getenv('N8N_VERIFY_WEBHOOK_URL') ?: 'https://n8n.srv1502312.hstgr.cloud/webhook/sharedspace-ai-verify');
define('FEEDBACK_SENTIMENT_CALLBACK_SECRET', 'ss_feedback_cb_2026_4uH9mQ2pL7xKc8Rv');

//dylan's api key, for article shit, changed from gemini cos i am a jew and this is free. 
//openrouter api key. Hidden inside repo secrets.
define('SUMMARY_API_KEY', getenv('SUMMARY_API_KEY') ?: '');
//define('SUMMARY_API_KEY', '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');
//define('SUMMARY_API_KEY', 'AIzaSyBUvEUMvHFbh1RfcbP8U1KDLisajM9SVKc');

//stripe shit
define('STRIPE_PUBLIC_KEY',  'pk_test_51TJAljQbEqKAvs55VRP6eLrnKdPbJymWgZ0B5oWgzl8N4DHOJ8muCR8Mr25vSsNKjB702hyrCOWEAZCW9x6GktlA00nf9EAGSS');
define('STRIPE_SECRET_KEY',  'sk_test_51TJAljQbEqKAvs55YlyGnED9kz3uETBPejDDOEkbCz8xFW9a3u0Y3ETY6mU09ldBaH9uGzfm0ji1HsVKtI1ll3Uz00Yhjud6uw');
define('STRIPE_WEBHOOK_SECRET', 'whsec_ry1oiK8XVQJr5oDJkyV0v1ZDk6KiieHS'); //  testing webhook secret: whsec_ad7559713353dfbddbdc0ff7f972044a51d3ac9359c606fe2515302a6b8e6a50
define('STRIPE_PRICE_ID',    'price_1TJAyTQbEqKAvs55u6Rm7n8I');  // your monthly price ID from Stripe dashboard

 
// =============================
// SMTP Email Configuration
// =============================

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'sharedspaceplatform@gmail.com');
define('SMTP_PASS', 'fwuxkymwmeahvnnr'); // Gmail app password (no spaces)
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');
define('SMTP_FROM_NAME', 'SharedSpace');

// Backup account below for sending of email:
// define('SMTP_USER', 'marcuskhongg@gmail.com');
// define('SMTP_PASS', 'jltrolbbzzdxhrux');

