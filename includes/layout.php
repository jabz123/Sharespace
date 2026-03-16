<?php
// Updated role-based sidebar logic checking for system_admin
if ($user_role === 'system_admin') {
    echo '<div class="badge badge-system-admin">System Administrator</div>';
    // Sidebar items for system_admin
} elseif ($user_role === 'editor') {
    echo '<div class="badge badge-editor">Editor</div>';
    // Sidebar items for editor
} else {
    echo '<div class="badge badge-user">User</div>';
    // Sidebar items for regular users
}
?>