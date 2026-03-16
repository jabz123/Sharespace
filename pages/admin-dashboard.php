<?php
// admin dashboard page
// only accessible to users with role == 'admin'
// blank for now, ready for future feature additions

require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();

$auth->requireAuth();
$user = $auth->currentUser();

if ($user->role !== 'admin') {
    redirect('/dashboard.php');
}

page_head('Admin Dashboard');
?>

<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Admin Dashboard', 'Welcome, ' . htmlspecialchars($user->fullName)); ?>
<?php flash_messages(); ?>

<div class="page-content">
</div>
</main>
</div>

<?php page_foot(); ?>
