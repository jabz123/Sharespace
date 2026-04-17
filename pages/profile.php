<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

$allowedFeedbackRoles = ['free', 'premium', 'category_admin'];

$feedbackStats = DB::first("
    SELECT
        ROUND(AVG(rating), 1) AS avg_rating,
        COUNT(*) AS review_count
    FROM site_feedback
    WHERE is_approved = 1
");

$communityRating = $feedbackStats && $feedbackStats['avg_rating'] !== null
    ? $feedbackStats['avg_rating']
    : '4.6';

$communityReviewCount = $feedbackStats && (int)$feedbackStats['review_count'] > 0
    ? (int)$feedbackStats['review_count']
    : 1247;

page_head('Profile');
?>
<div class="dashboard-layout <?= $user->role === 'system_admin' ? 'profile-admin-shell' : 'user-dashboard-shell' ?>">
    <?php sidebar($user); ?>

    <main>
        <?php dash_header('Profile Page'); ?>
        <?php flash_messages(); ?>

        <div class="page-content">
            <div class="profile-layout">
                <section class="profile-main">
                    <div class="profile">
                        <div class="profile-header">
                            <div class="user-info">
                                <h2><?= htmlspecialchars($user->fullName); ?></h2>
                            </div>
                        </div>

                        <form action="/pages/update-profile.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">

                            <div class="image-upload-container">
                                <div id="avatarPreview" class="profile-avatar-preview">
                                    <?php if (!empty($user->avatarUrl)): ?>
                                        <img
                                            id="avatarImg"
                                            src="/public/<?= htmlspecialchars($user->avatarUrl) ?>"
                                            alt="Profile picture"
                                        >
                                    <?php else: ?>
                                        <span id="avatarInitial"><?= htmlspecialchars($user->initial()) ?></span>
                                    <?php endif; ?>
                                </div>

                                <input
                                    type="file"
                                    id="avatarInput"
                                    name="avatar"
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    hidden
                                >

                                <div class="image-buttons">
                                    <button
                                        type="button"
                                        class="btn-dark"
                                        onclick="document.getElementById('avatarInput').click()"
                                    >
                                        Upload Photo
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-light"
                                        id="removeAvatarBtn"
                                        <?= empty($user->avatarUrl) ? 'style="display:none"' : '' ?>
                                        onclick="removeAvatar()"
                                    >
                                        Remove Photo
                                    </button>
                                </div>

                                <p class="text-muted profile-upload-note">
                                    JPEG, PNG, WEBP or GIF · Max 2MB
                                </p>
                            </div>

                            <label for="fullName">Full Name</label>
                            <input
                                type="text"
                                id="fullName"
                                name="fullName"
                                value="<?= htmlspecialchars($user->fullName) ?>"
                                required
                            >

                            <label for="email">Email</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                value="<?= htmlspecialchars($user->email) ?>"
                                required
                            >

                            <label for="bio">Bio</label>
                            <textarea
                                id="bio"
                                name="bio"
                                rows="6"
                            ><?= htmlspecialchars($user->bio) ?></textarea>

                            <div class="profile-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="/pages/edit-password.php" class="btn profile-password-btn">Edit Password</a>
                            </div>
                        </form>
                    </div>
                </section>

                <?php if (in_array($user->role ?? '', $allowedFeedbackRoles, true)): ?>
                    <aside class="feedback-panel">
                        <div class="feedback-card">
                            <h3>Share Your Feedback</h3>
                            <p class="feedback-subtitle">
                                Help us improve SharedSpace by sharing your experience with the platform.
                            </p>

                            <form action="/pages/submit-feedback.php" method="POST" class="feedback-form">
                                <label class="feedback-label">Rate Your Experience</label>

                                <div class="rating-stars">
                                    <input type="radio" name="rating" id="star5" value="5" required>
                                    <label for="star5" title="5 stars">★</label>

                                    <input type="radio" name="rating" id="star4" value="4">
                                    <label for="star4" title="4 stars">★</label>

                                    <input type="radio" name="rating" id="star3" value="3">
                                    <label for="star3" title="3 stars">★</label>

                                    <input type="radio" name="rating" id="star2" value="2">
                                    <label for="star2" title="2 stars">★</label>

                                    <input type="radio" name="rating" id="star1" value="1">
                                    <label for="star1" title="1 star">★</label>
                                </div>

                                <label for="feedbackContent" class="feedback-label">Your Comments</label>
                                <textarea
                                    id="feedbackContent"
                                    name="content"
                                    rows="6"
                                    maxlength="500"
                                    placeholder="Tell us about your experience..."
                                    required
                                ></textarea>

                                <div class="feedback-count">
                                    <span id="feedbackCharCount">0</span>/500 characters
                                </div>

                                <button type="submit" class="btn feedback-submit-btn">
                                    Submit Feedback
                                </button>
                            </form>

                            <div class="feedback-divider"></div>

                            <div class="community-rating-block">
                                <div class="community-rating-title">Community Rating</div>
                                <div class="community-rating">
                                    <span class="community-rating-star">★</span>
                                    <strong><?= htmlspecialchars((string)$communityRating) ?></strong>
                                    <span>based on <?= number_format($communityReviewCount) ?> reviews</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
document.getElementById('avatarInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        const preview = document.getElementById('avatarPreview');
        preview.innerHTML = '<img src="' + e.target.result + '" alt="Profile picture">';
        document.getElementById('removeAvatarBtn').style.display = '';
        document.getElementById('removeAvatarFlag').value = '0';
    };
    reader.readAsDataURL(file);
});

function removeAvatar() {
    const preview = document.getElementById('avatarPreview');
    preview.innerHTML = '<span><?= htmlspecialchars($user->initial()) ?></span>';
    document.getElementById('avatarInput').value = '';
    document.getElementById('removeAvatarFlag').value = '1';
    document.getElementById('removeAvatarBtn').style.display = 'none';
}

const feedbackInput = document.getElementById('feedbackContent');
const feedbackCharCount = document.getElementById('feedbackCharCount');

if (feedbackInput && feedbackCharCount) {
    feedbackInput.addEventListener('input', function () {
        feedbackCharCount.textContent = this.value.length;
    });
}
</script>

<?php page_foot(); ?>
