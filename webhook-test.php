<?php
// Visit: http://47.128.202.6/webhook-test.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<pre>";

echo "=== DEPLOYMENT CHECK ===\n";
echo "Script run at: " . date('Y-m-d H:i:s') . "\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "__FILE__: " . __FILE__ . "\n";
echo "realpath(__FILE__): " . realpath(__FILE__) . "\n";
echo "readlink /var/www/current: ";
$link = @readlink('/var/www/current');
echo ($link ?: 'FAILED — not readable') . "\n\n";

// List the 3 most recent release folders so we can see what was deployed
echo "=== RELEASES ON SERVER ===\n";
$releases = glob('/var/www/releases/*', GLOB_ONLYDIR);
if ($releases) {
    rsort($releases);
    foreach (array_slice($releases, 0, 3) as $r) {
        $wh = $r . '/actions/stripe-webhook.php';
        echo basename($r) . " — webhook MD5: " . (file_exists($wh) ? md5_file($wh) : 'MISSING') . "\n";
    }
} else {
    echo "(cannot read /var/www/releases — permission denied)\n";
}

echo "\n=== CURRENT WEBHOOK FILE ===\n";
$webhookFile = __DIR__ . '/actions/stripe-webhook.php';
echo "Path: $webhookFile\n";
echo "MD5: " . md5_file($webhookFile) . "\n";
echo "Last modified: " . date('Y-m-d H:i:s', filemtime($webhookFile)) . "\n\n";

echo "=== DATABASE ===\n";
try {
    DB::execute("CREATE TABLE IF NOT EXISTS webhook_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        created_at DATETIME DEFAULT NOW(),
        message TEXT
    )");
    DB::execute("INSERT INTO webhook_log (message) VALUES (?)", ['test ' . date('Y-m-d H:i:s') . ' from ' . realpath(__FILE__)]);
    $rows = DB::query("SELECT * FROM webhook_log ORDER BY id DESC LIMIT 5");
    foreach ($rows as $row) {
        echo "  [{$row['id']}] {$row['created_at']} — {$row['message']}\n";
    }
} catch (\Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== STRIPE CONFIG ===\n";
echo "Webhook secret prefix: " . substr(STRIPE_WEBHOOK_SECRET, 0, 22) . "...\n";

echo "\n=== DONE ===\n";
echo "</pre>";