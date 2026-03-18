<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

page_head('Profile');
?>
<div class="dashboard-layout">
<?php sidebar($user); ?>
<main>
<?php dash_header('Profile Page'); ?>
<div class="page-content">
    <p>🚧 WIP</p>
</div>
</main>
</div>
<?php page_foot(); ?>