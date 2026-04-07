<?php
// DROP THIS FILE in your htdocs root, push to AWS branch, then visit:
// http://47.128.202.6/webhook-test.php
// It will show you exactly what's running on the server.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<pre>";

// 1. Confirm this file is actually here (proves deployment worked)
echo "=== DEPLOYMENT CHECK ===\n";
echo "File deployed at: " . date('Y-m-d H:i:s') . "\n";
echo "PHP version: " . PHP_VERSION . "\n\n";

// 2. Check webhook file contents hash so we know which version is live
$webhookFile = __DIR__ . '/actions/stripe-webhook.php';
echo "=== WEBHOOK FILE ===\n";
echo "Path: $webhookFile\n";
echo "Exists: " . (file_exists($webhookFile) ? 'YES' : 'NO') . "\n";
echo "MD5: " . md5_file($webhookFile) . "\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($webhookFile)) . "\n\n";

// 3. Check if webhook_log table exists
echo "=== DATABASE ===\n";
try {
    $result = DB::query("SHOW TABLES LIKE 'webhook_log'");
    echo "webhook_log table exists: " . (count($result) > 0 ? 'YES' : 'NO') . "\n";

    // Try to create it manually
    DB::execute("CREATE TABLE IF NOT EXISTS webhook_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        created_at DATETIME DEFAULT NOW(),
        message TEXT
    )");
    echo "webhook_log table created/verified: YES\n";

    DB::execute("INSERT INTO webhook_log (message) VALUES (?)", ['test from webhook-test.php at ' . date('Y-m-d H:i:s')]);
    echo "Test row inserted: YES\n";

    $rows = DB::query("SELECT * FROM webhook_log ORDER BY id DESC LIMIT 5");
    echo "Recent log rows:\n";
    foreach ($rows as $row) {
        echo "  [{$row['id']}] {$row['created_at']} — {$row['message']}\n";
    }
} catch (\Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

// 4. Check webhook secret config
echo "\n=== STRIPE CONFIG ===\n";
echo "Webhook secret prefix: " . substr(STRIPE_WEBHOOK_SECRET, 0, 22) . "...\n";
echo "Secret key prefix: " . substr(STRIPE_SECRET_KEY, 0, 15) . "...\n";

echo "\n=== DONE ===\n";
echo "</pre>";