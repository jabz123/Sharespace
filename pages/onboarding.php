<?php
// UI page for the onboarding form.
// Communicates with controllers to load categories and save user preferences.
// connects to onboarding.js for js functions like select only 3 articles and update the number of words _/150
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/OnboardingController.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

$auth = new AuthController();
$onboardCtrl = new OnboardingController();
$articleCtrl = new ArticleController();

$user = $auth->currentUser();

if (!$user) {
    header("Location: /login.php");
    exit;
}

// prevent users who completed onboarding from returning here
if ($onboardCtrl->isCompleted($user->id)) {
    header("Location: /dashboard.php");
    exit;
}

$error = null;

// load available article categories
$categories = $articleCtrl->getAllCategories();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $onboardCtrl->savePreferences(
        $user->id,
        $_POST['age_group'] ?? '',
        $_POST['gender'] ?? '',
        $_POST['bio'] ?? '',
        $_POST['interests'] ?? []
    );
    if (isset($result['ok'])) {
        header("Location: /dashboard.php");
        exit;
    }
    $error = $result['error'];
}

?>

<link rel="stylesheet" href="/public/css/onboarding.css">

<div class="onboard-wrapper">

<div class="onboard-container">

<h2 class="brand-name">SharedSpace</h2>

<img src="/public/icons/clearicon.png" class="brand-logo" alt="logo">

<h1 class="onboard-title">Tell us more about you.</h1>

<p class="onboard-sub">
Choose 3 topics you're interested in to continue
</p>

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">

<!-- Age Group -->
<label class="section-title">Age Group</label>

<select name="age_group" required>
<option value="">Select your age group</option>
<option value="below12">12 and below</option>
<option value="13-17">13-17</option>
<option value="18-24">18-24</option>
<option value="25-34">25-34</option>
<option value="35-44">35-44</option>
<option value="45+">45+</option>
</select>


<!-- Gender -->
<label class="section-title">Gender</label>

<div class="gender-options">

<label>
<input type="radio" name="gender" value="male" required>Male
</label>

<label>
<input type="radio" name="gender" value="female">Female
</label>

</div>


<!-- Interests -->
<label class="section-title">
What are you interested in? <span>(Pick 3)</span>
</label>

<!-- <div class="interest-topbar"> -->
<div class="interest-header">
<span id="interestCounter">0 / 3 selected</span>

<button type="button" id="clearInterests" class="clear-btn">
Clear all
</button>

</div>

<div class="interest-grid">

<?php foreach ($categories as $cat): ?>

<label class="interest-chip">
<input type="checkbox" class="interest-checkbox" name="interests[]" value="<?= $cat->id ?>">

<span><?= htmlspecialchars($cat->name) ?></span>

</label>

<?php endforeach; ?>

</div>


<!-- Bio -->
<label class="section-title">Tell us about yourself</label>

<textarea name="bio" id="bio" maxlength="150" required placeholder="Write a short bio about yourself..." ></textarea>

<div class="bio-counter">
<span id="bioCounter">0 / 150</span>
</div>


<!-- Buttons -->
<button type="submit" class="btn-primary">Save Preferences</button>

<a href="/logout.php" class="btn-secondary">Logout</a>

</form>

</div>

</div>

<script src="/public/js/onboarding.js"></script>

<?php page_foot(); ?>