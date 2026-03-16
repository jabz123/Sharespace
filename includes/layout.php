<?php
// Previous code
if ($user->hasRole('system_admin')) {
    // Grants access to system admins
    grantAccess();
}