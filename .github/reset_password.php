<?php
// displays the reset password page after user clicks the email reset link
// verifies the reset token from the url and checks if it is still valid
// allows the user to enter and confirm a new password
// updates the user password in the database and clears the reset token
// redirects the user back to the login page after successful reset

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';

$token = $_GET['token'] ?? '';

$user = DB::first(
    "SELECT id FROM users
     WHERE reset_token = ?
     AND reset_expires > NOW()",
    [$token]
);

if (!$user) {
    die("Invalid or expired password reset link.");
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {

        DB::execute(
            "UPDATE users
             SET password = ?, reset_token = NULL, reset_expires = NULL
             WHERE id = ?",
            [password_hash($password, PASSWORD_BCRYPT), $user['id']]
        );

        redirect('/login.php', null, 'Password updated successfully.');
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">

<title>Reset Password – SharedSpace</title>

<link rel="stylesheet" href="/public/css/app.css">

<style>
body { background: hsl(213,56%,10%); }
</style>

</head>

<body>

<div class="auth-wrap">

<!-- LEFT SIDE -->
<div class="auth-form-side">

<div class="auth-box">

<a href="/" class="auth-logo">
<span style="font-size:24px">📰</span>
<span>SharedSpace</span>
</a>

<h1>Reset Your Password</h1>
<p class="sub">Enter your new password below</p>

<?php if ($error): ?>
<div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label for="password">Password</label>

<div class="input-icon" style="position:relative">

<span class="icon">🔒</span>

<input
type="password"
id="password"
name="password"
placeholder="••••••••"
required
style="padding-right:44px"
>

<button
type="button"
data-toggle-password="password"
style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px"
>
👁
</button>

</div>
</div>


<div class="form-group">
<label for="confirm_password">Confirm New Password</label>

<div class="input-icon" style="position:relative">

<span class="icon">🔒</span>

<input
type="password"
id="confirm_password"
name="confirm_password"
placeholder="••••••••"
required
style="padding-right:44px"
>

<button
type="button"
data-toggle-password="confirm_password"
style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:16px"
>
👁
</button>

</div>
</div>


<button type="submit" class="btn btn-hero btn-full">
Continue
</button>

</form>

<p class="text-sm text-muted" style="text-align:center;margin-top:20px">
<a href="/login.php">← Back to login</a>
</p>

</div>
</div>


<!-- RIGHT SIDE -->
<div class="auth-brand-side">

<div class="brand-body">

<div class="brand-rule"></div>

<h2>Truth in Every<br>Headline.</h2>

<p>
Join thousands of journalists and readers who trust SharedSpace
for verified, fact-checked news. Our AI-powered platform ensures
every article meets the highest standards of accuracy.
</p>

</div>

</div>

</div>

<script src="/public/js/app.js"></script>

</body>
</html>