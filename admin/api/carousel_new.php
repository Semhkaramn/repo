<?php
require_once '../../includes/config.php';

// Login kontrolü
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

$conn = getDBConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        $query = "
            SELECT c.*, s.site_name, s.logo_path
            FROM carousel_items c
            INNER JOIN sites s ON c.site_id = s.id
            ORDER BY c.display_order ASC
        ";
        $result = $conn->query($query);
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'available_sites':
        // Henüz carousel'de olmayan siteleri getir
        $query = "
            SELECT s.*
            FROM sites s
            WHERE s.id NOT IN (SELECT site_id FROM carousel_items WHERE is_active = 1)
            AND s.is_active = 1
            ORDER BY s.site_name ASC
        ";
        $result = $conn->query($query);
        $sites = [];
        while ($row = $result->fetch_assoc()) {
            $sites[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $sites]);
        break;

    case 'get':
        $id = $_GET['id'] ?? 0;
        $query = "
            SELECT c.*, s.site_name, s.logo_path
            FROM carousel_items c
            INNER JOIN sites s ON c.site_id = s.id
            WHERE c.id = $id
        ";
        $result = $conn->query($query);
        $item = $result->fetch_assoc();
        if ($item) {
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Not found']);
        }
        break;

    case 'add':
        $site_id = $_POST['site_id'] ?? 0;

        // Get max display order
        $max_order = $conn->query("SELECT MAX(display_order) as max_order FROM carousel_items")->fetch_assoc()['max_order'];
        $display_order = ($max_order ?? 0) + 1;

        $stmt = $conn->prepare("INSERT INTO carousel_items (site_id, display_order) VALUES (?, ?)");
        $stmt->bind_param("ii", $site_id, $display_order);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;

        if ($conn->query("DELETE FROM carousel_items WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;

    case 'reorder':
        $orders = json_decode($_POST['orders'] ?? '[]', true);

        foreach ($orders as $order) {
            $id = (int)$order['id'];
            $position = (int)$order['position'];
            $conn->query("UPDATE carousel_items SET display_order = $position WHERE id = $id");
        }

        echo json_encode(['success' => true]);
        break;

    case 'toggle':
        $id = $_POST['id'] ?? 0;
        $is_active = $_POST['is_active'] ?? 1;

        if ($conn->query("UPDATE carousel_items SET is_active = $is_active WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
