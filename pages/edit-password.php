<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

$isSystemAdmin = $user->role === 'system_admin';

page_head('Edit Password', $isSystemAdmin);
?>
<div class="dashboard-layout <?= $isSystemAdmin ? '' : 'user-dashboard-shell' ?>">
    <?php sidebar($user); ?>
    <main>
        <?php dash_header('Edit Password', 'Change your account password'); ?>
        <?php flash_messages(); ?>
        <div class="page-content">
            <form method="POST" action="/pages/process-password-update.php">
                <label for="old_password">Current Password</label>
                <input type="password" id="old_password" name="old_password" required>

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
            <div style="margin-top:20px">
                <a href="/pages/profile.php">Back to Profile</a>
            </div>
        </div>
    </main>
</div>
<?php page_foot(); ?>
