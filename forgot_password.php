<?php
// displays the forgot password page for users
// allows users to enter their email to request a password reset
// sends the request to AuthController to generate a reset token
// AuthController will send an email containing the reset password link

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/controllers/AuthController.php';

$auth = new AuthController();

$message = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $result = $auth->requestPasswordReset($_POST['email'] ?? '');

    if (isset($result['ok'])) {
        $message = "Password reset link sent to your email.";
    } else {
        $message = $result['error'];
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Forgot Password – SharedSpace</title>

<link rel="stylesheet" href="/public/css/app.css">

<style>
body { background: hsl(213,56%,10%); }
</style>

</head>

<body>

<div class="auth-wrap">

<!-- LEFT FORM -->
<div class="auth-form-side">

<div class="auth-box">

<a href="/login.php" class="auth-logo">
<span style="font-size:24px">📰</span>
<span>SharedSpace</span>
</a>

<h1>Forgot Password</h1>
<p class="sub">Enter your email to reset your password</p>

<?php if ($message): ?>
<div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<form method="POST">

<div class="form-group">
<label>Email</label>

<div class="input-icon">
<span class="icon">✉</span>

<input type="email"
name="email"
placeholder="you@example.com"
required>
</div>

</div>

<button type="submit" class="btn btn-hero btn-full">
Send Reset Link
</button>

</form>

<p class="text-sm text-muted" style="text-align:center;margin-top:20px">
<a href="/login.php">Back to login</a>
</p>

</div>
</div>

<!-- RIGHT BRAND SIDE -->

<div class="auth-brand-side">

<div class="brand-body">

<div class="brand-rule"></div>

<h2>Truth in Every<br>Headline.</h2>

<p>
Join thousands of journalists and readers who trust SharedSpace
for verified, fact-checked news.
</p>

</div>

</div>

</div>

</body>
</html>