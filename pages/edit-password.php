<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth(); // ensure user logged in

page_head('Edit Password');
?>
<div class="dashboard-layout">
    <?php sidebar($auth->currentUser()); ?>
    <main>
        <?php dash_header('Edit Password'); ?>
        <div class="page-content">
            <form method="POST" action="process_password_update.php">
                <label for="old_password">Old Password</label>
                <input type="password" id="old_password" name="old_password" required>

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required>

                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>

                <button type="submit" class="btn btn-primary">Save Changes</button>
            </form>
            <div style="margin-top: 20px;">
                <a href="profile.php">Back to Profile</a>
            </div>
        </div>
    </main>
</div>
<?php page_foot(); ?>