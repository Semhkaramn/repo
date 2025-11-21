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

// GET - Fetch single item
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM premium_sites WHERE id = $id");

    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'item' => $row]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Not found']);
    }
}

// POST - Create or Update
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $site_name = $_POST['site_name'] ?? '';
    $logo_url = $_POST['logo_url'] ?? '';
    $description = $_POST['description'] ?? '';
    $bonus_text = $_POST['bonus_text'] ?? '';
    $promo_code = $_POST['promo_code'] ?? '';
    $site_link = $_POST['site_link'] ?? '';
    $is_active = intval($_POST['is_active'] ?? 1);

    if ($id) {
        // Update
        $stmt = $conn->prepare("UPDATE premium_sites SET site_name=?, logo_url=?, description=?, bonus_text=?, promo_code=?, site_link=?, is_active=? WHERE id=?");
        $stmt->bind_param("ssssssii", $site_name, $logo_url, $description, $bonus_text, $promo_code, $site_link, $is_active, $id);
    } else {
        // Create - get max order
        $max_order = $conn->query("SELECT MAX(display_order) as max_order FROM premium_sites")->fetch_assoc()['max_order'] ?? 0;
        $display_order = $max_order + 1;

        $stmt = $conn->prepare("INSERT INTO premium_sites (site_name, logo_url, description, bonus_text, promo_code, site_link, is_active, display_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssii", $site_name, $logo_url, $description, $bonus_text, $promo_code, $site_link, $is_active, $display_order);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    $stmt->close();
}

// DELETE - Delete item
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);

    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM premium_sites WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
    }
}

// PUT - Update order
elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $order = $data['order'] ?? [];

    $success = true;
    foreach ($order as $item) {
        $id = intval($item['id']);
        $display_order = intval($item['order']);

        $stmt = $conn->prepare("UPDATE premium_sites SET display_order = ? WHERE id = ?");
        $stmt->bind_param("ii", $display_order, $id);

        if (!$stmt->execute()) {
            $success = false;
        }
        $stmt->close();
    }

    echo json_encode(['success' => $success]);
}

$conn->close();
?>
