<?php
// Check user role
session_start();
if (!isset($_SESSION['user_role'])) {
    die('Access denied. Please log in to access this page.');
}
$role = $_SESSION['user_role'];

// Display sidebar
?>
<div class='sidebar'>
    <ul>
        <li><a href='home.php'>Home</a></li>
        <li><a href='profile.php'>Profile</a></li>
        <li><a href='logout.php'>Logout</a></li>
    </ul>
</div>

<h1>Profile Page</h1>

<p>This page is a work in progress.</p>

<?php
// Role-specific content
if ($role == 'free') {
    echo '<p>Welcome, Free User!</p>';
} elseif ($role == 'premium') {
    echo '<p>Welcome, Premium User!</p>';
} elseif ($role == 'system_admin') {
    echo '<p>Welcome, Administrator!</p>';
}
?>