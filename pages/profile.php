<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/OnboardingController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

$onboardCtrl = new OnboardingController();
$articleCtrl = new ArticleController();

$allCategories = $articleCtrl->getAllCategories();
$currentInterests = $onboardCtrl->getInterests($user->id); // array of category IDs

$allowedFeedbackRoles = ['free', 'premium', 'category_admin'];

page_head('Profile', $user->role === 'system_admin');
?>
<div class="dashboard-layout <?= $user->role === 'system_admin' ? 'profile-admin-shell' : 'user-dashboard-shell' ?>">
    <?php sidebar($user); ?>

    <main>
        <?php dash_header('Profile Page', 'Edit Your Profile'); ?>
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

                        <!-- profile details form here -->
                        <form action="/pages/update-profile.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="remove_avatar" id="removeAvatarFlag" value="0">

                            <div class="image-upload-container">
                                <div id="avatarPreview" class="profile-avatar-preview">
                                    <?php if (!empty($user->avatarUrl)): ?>
                                        <img
                                            id="avatarImg"
                                            src="/public/<?= htmlspecialchars($user->avatarUrl) ?>"
                                            alt="Profile picture">
                                    <?php else: ?>
                                        <span id="avatarInitial"><?= htmlspecialchars($user->initial()) ?></span>
                                    <?php endif; ?>
                                </div>

                                <input
                                    type="file"
                                    id="avatarInput"
                                    name="avatar"
                                    accept="image/jpeg,image/png,image/webp,image/gif"
                                    hidden>

                                <div class="image-buttons">
                                    <button
                                        type="button"
                                        class="btn-dark"
                                        onclick="document.getElementById('avatarInput').click()">
                                        Upload Photo
                                    </button>

                                    <button
                                        type="button"
                                        class="btn-light"
                                        id="removeAvatarBtn"
                                        <?= empty($user->avatarUrl) ? 'style="display:none"' : '' ?>
                                        onclick="removeAvatar()">
                                        Remove Photo
                                    </button>
                                </div>

                                <p class="text-muted profile-upload-note">
                                    JPEG, PNG, WEBP or GIF · Max 2MB
                                </p>
                            </div>

                            <div class="profile-field">
                                <label for="fullName">Full Name:</label>
                                <input
                                    type="text"
                                    id="fullName"
                                    name="fullName"
                                    value="<?= htmlspecialchars($user->fullName) ?>"
                                    required>
                            </div>

                            <div class="profile-field">
                                <label for="email">Email:</label>
                                <input
                                    type="email"
                                    id="email"
                                    name="email"
                                    value="<?= htmlspecialchars($user->email) ?>"
                                    readonly
                                    aria-describedby="emailLockedNote"
                                    required>
                                <p class="profile-field-note" id="emailLockedNote">
                                    NOTE: Email is not changeable
                                </p>
                            </div>

                            <div class="profile-field">
                                <label for="bio">Bio:</label>
                                <textarea
                                    id="bio"
                                    name="bio"
                                    rows="6"
                                    maxlength="150"><?= htmlspecialchars($user->bio) ?></textarea>
                                <div class="profile-bio-counter">
                                    <span id="bioCounter"><?= strlen($user->bio) ?> / 150</span>
                                </div>
                            </div>

                            <div class="profile-actions">
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                                <a href="/pages/edit-password.php" class="btn profile-password-btn">Edit Password</a>
                            </div>
                        </form>

                        <!-- edit interests shit here -->
                        <div class="interests-section">
                            <h3>Your Interests</h3>
                            <p class="interests-hint">Pick exactly 3 topics. Your homepage recommendations update automatically.</p>

                            <form action="/pages/update-profile.php" method="POST" id="interestsForm">
                                <input type="hidden" name="update_interests" value="1">

                                <div class="interest-grid-profile" id="interestGrid">
                                    <?php foreach ($allCategories as $cat): ?>
                                        <?php $checked = in_array($cat->id, $currentInterests); ?>
                                        <label class="interest-chip-profile <?= $checked ? 'selected' : '' ?>">
                                            <input
                                                type="checkbox"
                                                name="interests[]"
                                                value="<?= $cat->id ?>"
                                                <?= $checked ? 'checked' : '' ?>
                                                class="interest-checkbox-profile">
                                            <?= htmlspecialchars($cat->name) ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <p class="interests-counter" id="interestCounter">
                                    <?= count($currentInterests) ?> / 3 selected
                                </p>

                                <button
                                    type="submit"
                                    class="btn btn-primary btn-save-interests"
                                    id="saveInterestsBtn">
                                    Save Interests
                                </button>
                            </form>
                        </div>
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
                                    required></textarea>

                                <div class="feedback-count">
                                    <span id="feedbackCharCount">0</span>/500 characters
                                </div>

                                <button type="submit" class="btn feedback-submit-btn">
                                    Submit Feedback
                                </button>
                            </form>

                            <div class="feedback-divider"></div>

                        </div>
                    </aside>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script>
    // avatar upload preview and remove logic
    document.getElementById('avatarInput').addEventListener('change', function() {
        const file = this.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
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

    // ── Feedback char counter ─────────────────────────────────
    const feedbackInput = document.getElementById('feedbackContent');
    const feedbackCharCount = document.getElementById('feedbackCharCount');
    const feedbackForm = document.querySelector('.feedback-form');
    function countFeedbackWords(value) {
        return (value.match(/[\p{L}\p{N}']+/gu) || []).length;
    }
    if (feedbackInput && feedbackCharCount) {
        feedbackInput.addEventListener('input', function() {
            feedbackCharCount.textContent = this.value.length;
        });
    }
    if (feedbackForm && feedbackInput) {
        feedbackForm.addEventListener('submit', function(event) {
            const charCount = feedbackInput.value.trim().length;
            const wordCount = countFeedbackWords(feedbackInput.value);
            if (charCount < 20) {
                event.preventDefault();
                alert('Feedback must be at least 20 characters. Current character count: ' + charCount + '.');
                feedbackInput.focus();
                return;
            }
            if (wordCount <= 3) {
                event.preventDefault();
                alert('Feedback must be more than 3 words. Current word count: ' + wordCount + '.');
                feedbackInput.focus();
            }
        });
    }

    // Bio char counter - mirrors onboarding bio limit
    const bioInput = document.getElementById('bio');
    const bioCounter = document.getElementById('bioCounter');
    const BIO_MAX_LENGTH = 150;

    function updateBioCounter() {
        if (!bioInput || !bioCounter) return;

        if (bioInput.value.length > BIO_MAX_LENGTH) {
            bioInput.value = bioInput.value.slice(0, BIO_MAX_LENGTH);
        }

        bioCounter.textContent = bioInput.value.length + ' / ' + BIO_MAX_LENGTH;
    }

    if (bioInput && bioCounter) {
        bioInput.addEventListener('input', updateBioCounter);
        updateBioCounter();
    }

    // interest selection logic can choose max 3 with counter and save button enable/disable
    const MAX_INTERESTS = 3;
    const counter = document.getElementById('interestCounter');
    const saveBtn = document.getElementById('saveInterestsBtn');

    function updateCounter() {
        const selected = document.querySelectorAll('.interest-checkbox-profile:checked').length;
        counter.textContent = selected + ' / ' + MAX_INTERESTS + ' selected';

        if (selected === MAX_INTERESTS) {
            counter.classList.remove('error');
            saveBtn.disabled = false;
        } else {
            counter.classList.add('error');
            saveBtn.disabled = true;
        }
    }

    document.querySelectorAll('.interest-chip-profile').forEach(function(chip) {
        chip.addEventListener('click', function() {
            const checkbox = chip.querySelector('input[type="checkbox"]');
            const selected = document.querySelectorAll('.interest-checkbox-profile:checked').length;

            // if trying to select 4th, block it
            if (!checkbox.checked && selected >= MAX_INTERESTS) {
                return;
            }

            checkbox.checked = !checkbox.checked;
            chip.classList.toggle('selected', checkbox.checked);
            updateCounter();
        });
    });

    // set initial save button state
    updateCounter();
</script>

<?php page_foot(); ?>
