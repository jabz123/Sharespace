<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// handles category CRUD operations from the admin dashboard
// processes create, update, and delete actions submitted via POST
// redirects back to admin dashboard with success or error messages

require_once __DIR__ . '/../includes/controllers/AuthController.php';
require_once __DIR__ . '/../includes/controllers/CategoryController.php';
require_once __DIR__ . '/../includes/AuditLogger.php';

$auth = new AuthController();
$catCtrl = new CategoryController();

// check authorization - system_admin only
$user = $auth->currentUser();
if (!$user || $user->role !== 'system_admin') {
    header('Location: /pages/admin-dashboard.php?error=' . urlencode('Unauthorized access.'));
    exit;
}

// only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /pages/admin-dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$result = null;

if ($action === 'create') {
    $result = $catCtrl->create(
        $_POST['name'] ?? '',
        $_POST['description'] ?? ''
    );
    if (($result['ok'] ?? false) === true) {
        AuditLogger::log($user->id, 'create_category', 'Category', DB::lastId(), 'Created category: ' . ($_POST['name'] ?? ''));
    }
} elseif ($action === 'update') {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $result = $catCtrl->update(
        $categoryId,
        $_POST['name'] ?? '',
        $_POST['description'] ?? ''
    );
    if (($result['ok'] ?? false) === true) {
        AuditLogger::log($user->id, 'update_category', 'Category', $categoryId, 'Updated category: ' . ($_POST['name'] ?? ''));
    }
} elseif ($action === 'delete') {
    $categoryId = (int) ($_POST['category_id'] ?? 0);
    $result = $catCtrl->delete($categoryId);
    if (($result['ok'] ?? false) === true) {
        AuditLogger::log($user->id, 'delete_category', 'Category', $categoryId, 'Deleted category ID ' . $categoryId);
    }
} else {
    header('Location: /pages/admin-dashboard.php');
    exit;
}

// redirect back with result message
if ($result['ok'] === true) {
    header('Location: /pages/admin-dashboard.php?success=1');
} else {
    $error = urlencode($result['error'] ?? 'Category operation failed.');
    header("Location: /pages/admin-dashboard.php?error=$error");
}
exit;
