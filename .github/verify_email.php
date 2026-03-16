<?php
// this page is after user click verify button which leads to this page (email click verify button then open this page)
// this page then reads verification token from the url
// checks the database to find the user with this token
// if token is valid, marks the user's email as verified
// removes the verification token after verification
// shows a success message and redirects user to login page

// load DB connection
require_once __DIR__ . '/includes/db.php';

// check if token exists in URL
if (!isset($_GET['token'])) {
    die("Invalid verification link.");
}

$token = $_GET['token'];

// find user with this token
$user = DB::first(
    "SELECT id FROM users WHERE verification_token = ?",
    [$token]
);

if (!$user) {
    die("Invalid or expired verification link.");
}

// update user email to verified
DB::execute(
    "UPDATE users 
     SET email_verified = 1, verification_token = NULL
     WHERE id = ?",
    [$user['id']]
);

// show a simple success message in HTML
echo "<h2>Email verified successfully!</h2>";
echo "<p>Redirecting to login page...</p>";

// redirect user to login page after 3 seconds
header("Refresh:3; url=login.php");