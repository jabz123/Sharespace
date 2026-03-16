<?php
function sidebar() {
    $user_role = get_user_role(); // Assuming this function fetches the current user's role
    if ($user_role === 'admin') {
        echo '<a href="admin-dashboard.php">Logo</a>';
        echo '<ul>';
        echo '<li><a href="admin-dashboard.php">Admin Dashboard</a></li>';
        echo '<li><a href="browse-articles.php">Browse Articles</a></li>';
        echo '</ul>';
        echo '<span class="badge">Admin</span>';
    } else {
        echo '<a href="dashboard.php">Logo</a>';
        echo '<ul>';
        echo '<li><a href="home.php">Home</a></li>';
        echo '<li><a href="browse-articles.php">Browse Articles</a></li>';
        echo '<li><a href="my-articles.php">My Articles</a></li>';
        echo '<li><a href="write-article.php">Write Article</a></li>';
        echo '</ul>';
    }
}
?>