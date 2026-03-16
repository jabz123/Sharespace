<?php
// admin dashboard page
// only accessible to users with the admin role

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();

// ensure user is logged in
$auth->requireAuth();

// get current logged in user
$user = $auth->currentUser();

// only admins can access this page
if ($user->role !== 'admin') {
    redirect('/dashboard.php');
}

page_head('Admin Dashboard');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Admin Dashboard', 'Manage the platform'); ?>
<?php flash_messages(); ?>

<div class="page-content">
</div>
</main>
</div>

<?php page_foot(); ?>
