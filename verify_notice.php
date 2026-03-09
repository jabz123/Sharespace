<div class="page-container">
<div class="verify-card">

<?php if(isset($_GET['resent'])): ?>
<p style="color:green;">Verification email sent again. Please check your inbox.</p>
<?php endif; ?>

<?php if(isset($_GET['cooldown'])): ?>
<p style="color:red;">
Resend verification email available in <?php echo intval($_GET['cooldown']); ?> seconds.
</p>
<?php endif; ?>

<?php if(isset($_GET['error'])): ?>
<p style="color:red;">Something went wrong. Please try again.</p>
<?php endif; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email</title>
    <link rel="stylesheet" href="/public/css/app.css">
</head>

<body>


<h2>Account Created Successfully!</h2>

<p>Please check your email and click the verification link before logging in.</p>

<p>If you don't see the email, please check your spam folder.</p>

<div class="button-group">

<a class="btn-primary" href="login.php">
Go to Login Page
</a>

<button
class="btn-secondary"
id="resendBtn"
onclick="window.location.href='resend_verification.php?email=<?php echo urlencode($_GET['email'] ?? ''); ?>'">
Resend Verification Email
</button>

</div>

</div>

</div>

<script src="/public/js/countdown.js"></script>

</body>
</html>