<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/db.php';

echo "<pre>";

echo "=== WEBHOOK FILE INVESTIGATION ===\n";
$webhookPath = __DIR__ . '/actions/stripe-webhook.php';

// Is it a symlink?
echo "Is symlink:    " . (is_link($webhookPath) ? 'YES' : 'no') . "\n";
echo "realpath:      " . realpath($webhookPath) . "\n";
echo "readlink:      " . (is_link($webhookPath) ? readlink($webhookPath) : 'n/a') . "\n";
echo "Current MD5:   " . md5_file($webhookPath) . "\n";
echo "Expected MD5:  d88b5d009e3266fec1d9187fa470adc7\n\n";

// Is actions/ directory itself a symlink?
$actionsDir = __DIR__ . '/actions';
echo "=== ACTIONS DIRECTORY ===\n";
echo "Is symlink:    " . (is_link($actionsDir) ? 'YES' : 'no') . "\n";
echo "realpath:      " . realpath($actionsDir) . "\n";
echo "readlink:      " . (is_link($actionsDir) ? readlink($actionsDir) : 'n/a') . "\n\n";

// What does /var/www/storage contain?
echo "=== /var/www/storage CONTENTS ===\n";
$storage = '/var/www/storage';
if (is_dir($storage)) {
    $items = scandir($storage);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $full = $storage . '/' . $item;
        $type = is_link($full) ? 'symlink→' . readlink($full) : (is_dir($full) ? 'dir' : 'file');
        echo "  $item ($type)\n";
    }
} else {
    echo "  /var/www/storage does not exist or not readable\n";
}

// List all symlinks in the current release
echo "\n=== SYMLINKS IN CURRENT RELEASE ===\n";
$release = __DIR__;
$iter = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($release, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);
foreach ($iter as $path => $info) {
    if (is_link($path)) {
        echo "  " . str_replace($release . '/', '', $path) . " → " . readlink($path) . "\n";
    }
}

echo "\n=== DATABASE ===\n";
try {
    DB::execute("CREATE TABLE IF NOT EXISTS webhook_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        created_at DATETIME DEFAULT NOW(),
        message TEXT
    )");
    DB::execute("INSERT INTO webhook_log (message) VALUES (?)", ['investigate at ' . date('Y-m-d H:i:s')]);
    $rows = DB::query("SELECT * FROM webhook_log ORDER BY id DESC LIMIT 3");
    foreach ($rows as $row) {
        echo "  [" . $row['id'] . "] " . $row['created_at'] . " — " . $row['message'] . "\n";
    }
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
echo "</pre>";