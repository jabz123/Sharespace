<?php
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/controllers/AuthController.php';

$auth = new AuthController();
$auth->requireAuth();
$user = $auth->currentUser();

$allowedFeedbackRoles = ['free', 'premium', 'category_admin'];

if (!in_array($user->role ?? '', $allowedFeedbackRoles, true)) {
    redirect('/pages/profile.php', 'Your account type cannot submit feedback.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/pages/profile.php');
}

$rating = (int) ($_POST['rating'] ?? 0);
$content = trim($_POST['content'] ?? '');

if ($rating < 1 || $rating > 5) {
    redirect('/pages/profile.php', 'Please select a rating from 1 to 5.');
}

if ($content === '') {
    redirect('/pages/profile.php', 'Please enter your feedback.');
}

if (mb_strlen($content) > 500) {
    redirect('/pages/profile.php', 'Feedback must be 500 characters or less.');
}

$name = trim((string)($user->fullName ?? ''));
if ($name === '') {
    $name = trim((string)($user->email ?? 'Anonymous User'));
}

$role = trim((string)($user->role ?? 'free'));
$roleLabel = ucfirst(str_replace('_', ' ', $role));

DB::execute(
    "INSERT INTO site_feedback (user_id, name, role, rating, content, is_approved, created_at)
     VALUES (?, ?, ?, ?, ?, 0, NOW())",
    [
        $user->id,
        $name,
        $roleLabel,
        $rating,
        $content
    ]
);

redirect('/pages/profile.php', null, 'Feedback submitted successfully! It will appear after approval.');