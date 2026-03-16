<?php
// admin dashboard page

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();

// Check if user is logged in
$user = $auth->currentUser();
if (!$user) {
    header('Location: /');
    exit;
}

// Check if user is system_admin
if ($user->role !== 'system_admin') {
    header('Location: /dashboard.php');
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
        <p style="color:#64748b;font-size:15px">
            The admin dashboard is currently under development.
        </p>
        <p style="color:#94a3b8;font-size:14px;margin-top:8px">
            Check back soon for admin features!
        </p>
    </div>
</div>

</main>
</div>

<?php page_foot(); ?>