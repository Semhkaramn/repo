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
        $result = $conn->query("SELECT * FROM social_media ORDER BY display_order ASC");
        $items = [];
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $items]);
        break;

    case 'get':
        $id = $_GET['id'] ?? 0;
        $result = $conn->query("SELECT * FROM social_media WHERE id = $id");
        $item = $result->fetch_assoc();
        if ($item) {
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Not found']);
        }
        break;

    case 'add':
        $name = $_POST['name'] ?? '';
        $icon_url = $_POST['icon_url'] ?? '';
        $link = $_POST['link'] ?? '';
        $bg_color = $_POST['bg_color'] ?? '#000000';

        // Get max display order
        $max_order = $conn->query("SELECT MAX(display_order) as max_order FROM social_media")->fetch_assoc()['max_order'];
        $display_order = ($max_order ?? 0) + 1;

        $stmt = $conn->prepare("INSERT INTO social_media (name, icon_url, link, bg_color, display_order) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $name, $icon_url, $link, $bg_color, $display_order);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $name = $_POST['name'] ?? '';
        $icon_url = $_POST['icon_url'] ?? '';
        $link = $_POST['link'] ?? '';
        $bg_color = $_POST['bg_color'] ?? '#000000';
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        $stmt = $conn->prepare("UPDATE social_media SET name = ?, icon_url = ?, link = ?, bg_color = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssssii", $name, $icon_url, $link, $bg_color, $is_active, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;

        if ($conn->query("DELETE FROM social_media WHERE id = $id")) {
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
            $conn->query("UPDATE social_media SET display_order = $position WHERE id = $id");
        }

        echo json_encode(['success' => true]);
        break;

    case 'toggle':
        $id = $_POST['id'] ?? 0;
        $is_active = $_POST['is_active'] ?? 1;

        if ($conn->query("UPDATE social_media SET is_active = $is_active WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
