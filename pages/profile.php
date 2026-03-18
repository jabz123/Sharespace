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
   <div class="profile">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="user-info">
                        <h2><?= htmlspecialchars($user->fullName); ?></h2>
                        <p><?= htmlspecialchars(ucfirst($user->role)); // Display user role: e.g., Free, Premium ?></p>
                    </div>
                </div>
                
                <!-- User Info Form -->
                <form action="update_profile.php" method="POST">
                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName" value="<?= htmlspecialchars($user->fullName) ?>" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user->email) ?>" required>

                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user->bio ?? '') ?></textarea>

                    <button type="submit" class="save-button">Save Changes</button>
                </form>

                <!-- Edit Password Button -->
                <a href="edit_password.php" class="btn">Edit Password</a>
            </div>
</div>
</main>
</div>
<?php page_foot(); ?>