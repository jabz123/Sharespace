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
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Admin Dashboard', 'System Administration Panel'); ?>
<div class="page-content">
    <div class="card" style="text-align:center;padding:64px 32px">
        <div style="font-size:64px;margin-bottom:24px">🚧</div>
        <h2 style="font-size:24px;font-weight:700;margin-bottom:12px">Work In Progress</h2>
        <p style="color:#64748b;font-size:15px">The admin dashboard is currently under development.</p>
        <p style="color:#94a3b8;font-size:14px;margin-top:8px">Check back soon for admin features!</p>
    </div>
</div>
</main>
</div>

<?php page_foot(); ?>
