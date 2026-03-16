<?php
session_start();

// Check if user is logged in and if their role is system_admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'system_admin') {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../includes/layout.php';

$user = $_SESSION['user'] ?? null;
if (!$user) {
    header('Location: /');
    exit;
}

page_head('Admin Dashboard');
sidebar($user);
?>

<main class="main-content">
    <div class="container">
        <!-- Empty admin dashboard - ready for content -->
    </div>
</main>

<?php page_foot(); ?>
