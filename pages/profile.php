<?php
session_start();

// Require authentication
if (!isset($_SESSION['user_logged_in'])) {
    header('Location: login.php');
    exit();
}

// Render Sidebar with User Info
$user_type = $_SESSION['user_type'];
$sidebar_content = "<p>User Type: " . htmlspecialchars($user_type) . "</p>";

if ($user_type == 'free') {
    $sidebar_content .= "<p>Free User Content</p>";
} elseif ($user_type == 'premium') {
    $sidebar_content .= "<p>Premium User Content</p>";
} elseif ($user_type == 'system_admin') {
    $sidebar_content .= "<p>Admin Content</p>";
}

// Profile Page Rendering
echo "<h1>Profile Page</h1>";

// Display WIP message
echo "<p>Work in Progress...</p>";
?>