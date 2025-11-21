<?php
require_once '../../includes/config.php';

// Login kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['file'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['file'];
$allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];

// Validate file type
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type']);
    exit;
}

// Validate file size (max 5MB)
if ($file['size'] > 5 * 1024 * 1024) {
    echo json_encode(['success' => false, 'error' => 'File too large (max 5MB)']);
    exit;
}

// Create uploads directory if it doesn't exist
$upload_dir = UPLOAD_PATH;
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = uniqid() . '_' . time() . '.' . $extension;
$filepath = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $filepath)) {
    $url = UPLOAD_URL . $filename;
    echo json_encode(['success' => true, 'url' => $url, 'filename' => $filename]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
}
?>
