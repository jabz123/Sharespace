<?php
session_start();

// Check if user is logged in and if their role is admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /'); // Redirect to homepage if not admin
    exit;
}

// Include the admin header
include_once 'header-admin.php';

// Admin dashboard layout
?>

<div class="dashboard">
    <h1>Admin Dashboard</h1>
    <!-- Add more dashboard content here -->
</div>