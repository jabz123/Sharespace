<?php

// toggle save / unsave article (AJAX endpoint)

// include required files
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/controllers/ArticleController.php';

// return JSON response
header('Content-Type: application/json');

// only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

// check login
if (empty($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

// get data
$userId = $_SESSION['user_id'];
$articleId = (int) ($_POST['article_id'] ?? 0);

if (!$articleId) {
    echo json_encode(['error' => 'Invalid article']);
    exit;
}

// use controller
$articleCtrl = new ArticleController();

try {
    $saved = $articleCtrl->toggleSave($userId, $articleId);

    echo json_encode([
        'success' => true,
        'saved' => $saved,
    ]);

} catch (Exception $e) {
    echo json_encode([
        'error' => 'Something went wrong',
    ]);
}
