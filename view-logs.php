<?php
// Simple log viewer (delete after debugging)
$logFile = __DIR__ . '/logs/webhook-raw.log';

if (!file_exists($logFile)) {
    die("Log file not found at: $logFile");
}

$logs = file_get_contents($logFile);
echo "<pre>" . htmlspecialchars($logs) . "</pre>";