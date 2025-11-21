<?php
require_once '../../includes/config.php';

// Login kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $site_title = $_POST['site_title'] ?? '';
    $site_logo = $_POST['site_logo'] ?? '';

    $stmt = $conn->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
                            ON DUPLICATE KEY UPDATE setting_value = ?");

    // Update site title
    $key = 'site_title';
    $stmt->bind_param("sss", $key, $site_title, $site_title);
    $stmt->execute();

    // Update site logo
    $key = 'site_logo';
    $stmt->bind_param("sss", $key, $site_logo, $site_logo);
    $stmt->execute();

    echo json_encode(['success' => true]);
    $stmt->close();
}

$conn->close();
?>
