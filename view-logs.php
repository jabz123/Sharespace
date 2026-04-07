<?php
$logFile = __DIR__ . '/logs/webhook-raw.log';

if (!file_exists($logFile)) {
    die("Log file not found at: " . realpath(__DIR__) . "/logs/webhook-raw.log<br>Try cancelling a subscription first.");
}

$logs = file_get_contents($logFile);
echo "<pre>" . htmlspecialchars($logs) . "</pre>";