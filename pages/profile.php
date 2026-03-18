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
        <?php flash_messages(); ?>
        <div class="page-content">
            <div class="profile">
                <!-- profile Header -->
                <div class="profile-header">
                    <div class="user-info">
                        <h2><?= htmlspecialchars($user->fullName); ?></h2>
             
                    </div>
                </div>

                <!-- profile form — multipart for file upload -->
                <form action="/pages/update-profile.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">

                    <!-- avatar upload section -->
                    <div class="image-upload-container">
                       

                        <!-- preview circle -->
                        <div id="avatarPreview" style="
                    width:100px;height:100px;border-radius:50%;
                    background:var(--gradient-hero);
                    display:flex;align-items:center;justify-content:center;
                    font-size:36px;font-weight:700;color:#fff;
                    overflow:hidden;margin-bottom:12px;flex-shrink:0">
                            <?php if (!empty($user->avatarUrl)): ?>
                                <img id="avatarImg"
                                    src="/public/<?= htmlspecialchars($user->avatarUrl) ?>"
                                    alt="Profile picture"
                                    style="width:100%;height:100%;object-fit:cover">
                            <?php else: ?>
                                <span id="avatarInitial"><?= htmlspecialchars($user->initial()) ?></span>
                            <?php endif; ?>
                        </div>

                        <input type="file" id="avatarInput" name="avatar"
                            accept="image/jpeg,image/png,image/webp,image/gif" hidden>

                        <div class="image-buttons">
                            <button type="button" class="btn-dark"
                                onclick="document.getElementById('avatarInput').click()">
                                Upload Photo
                            </button>
                            <button type="button" class="btn-light" id="removeAvatarBtn"
                                <?= empty($user->avatarUrl) ? 'style="display:none"' : '' ?>
                                onclick="removeAvatar()">
                                Remove Photo
                            </button>
                        </div>
                        <p class="text-muted" style="font-size:12px;margin-top:8px">
                            JPEG, PNG, WEBP or GIF · Max 2MB
                        </p>
                    </div>

                    <label for="fullName">Full Name</label>
                    <input type="text" id="fullName" name="fullName"
                        value="<?= htmlspecialchars($user->fullName) ?>" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email"
                        value="<?= htmlspecialchars($user->email) ?>" required>

                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user->bio) ?></textarea>

                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>


                <a href="edit-password.php" class="btn">Edit Password</a>


            </div>
        </div>
    </main>
</div>

<script>
// live preview when user picks a new image
document.getElementById('avatarInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
 
    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('avatarPreview');
        preview.innerHTML = '<img src="' + e.target.result + '" style="width:100%;height:100%;object-fit:cover">';
        document.getElementById('removeAvatarBtn').style.display = '';
        document.getElementById('removeAvatarFlag').value = '0';
    };
    reader.readAsDataURL(file);
});
 
// clear the preview and flag for removal
function removeAvatar() {
    const preview = document.getElementById('avatarPreview');
    preview.innerHTML = '<span><?= htmlspecialchars($user->initial()) ?></span>';
    document.getElementById('avatarInput').value = '';
    document.getElementById('removeAvatarFlag').value = '1';
    document.getElementById('removeAvatarBtn').style.display = 'none';
}
</script>

<?php page_foot(); ?>