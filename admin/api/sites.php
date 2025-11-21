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
        $result = $conn->query("SELECT * FROM sites ORDER BY display_order ASC");
        $sites = [];
        while ($row = $result->fetch_assoc()) {
            $sites[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $sites]);
        break;

    case 'get':
        $id = $_GET['id'] ?? 0;
        $result = $conn->query("SELECT * FROM sites WHERE id = $id");
        $item = $result->fetch_assoc();
        if ($item) {
            echo json_encode(['success' => true, 'data' => $item]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Not found']);
        }
        break;

    case 'add':
        $site_name = $_POST['site_name'] ?? '';
        $site_url = $_POST['site_url'] ?? '';
        $bonus_text = $_POST['bonus_text'] ?? '';
        $promo_code = $_POST['promo_code'] ?? '';
        $description = $_POST['description'] ?? '';

        // Logo upload
        $logo_path = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/uploads/';
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('logo_') . '.' . $file_extension;
            $logo_path = 'assets/uploads/' . $new_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $new_filename)) {
                // Logo uploaded successfully
            } else {
                echo json_encode(['success' => false, 'error' => 'Logo yüklenemedi']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Logo gerekli']);
            exit;
        }

        // Get max display order
        $max_order = $conn->query("SELECT MAX(display_order) as max_order FROM sites")->fetch_assoc()['max_order'];
        $display_order = ($max_order ?? 0) + 1;

        $stmt = $conn->prepare("INSERT INTO sites (site_name, logo_path, site_url, bonus_text, promo_code, description, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $site_name, $logo_path, $site_url, $bonus_text, $promo_code, $description, $display_order);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $site_name = $_POST['site_name'] ?? '';
        $site_url = $_POST['site_url'] ?? '';
        $bonus_text = $_POST['bonus_text'] ?? '';
        $promo_code = $_POST['promo_code'] ?? '';
        $description = $_POST['description'] ?? '';
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        // Logo update (optional)
        $logo_update = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/uploads/';
            $file_extension = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('logo_') . '.' . $file_extension;
            $logo_path = 'assets/uploads/' . $new_filename;

            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $new_filename)) {
                // Delete old logo
                $old_logo = $conn->query("SELECT logo_path FROM sites WHERE id = $id")->fetch_assoc()['logo_path'];
                if ($old_logo && file_exists('../../' . $old_logo)) {
                    unlink('../../' . $old_logo);
                }
                $logo_update = ", logo_path = '$logo_path'";
            }
        }

        $stmt = $conn->prepare("UPDATE sites SET site_name = ?, site_url = ?, bonus_text = ?, promo_code = ?, description = ?, is_active = ? $logo_update WHERE id = ?");
        $stmt->bind_param("sssssii", $site_name, $site_url, $bonus_text, $promo_code, $description, $is_active, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;

        // Delete logo file
        $logo = $conn->query("SELECT logo_path FROM sites WHERE id = $id")->fetch_assoc()['logo_path'];
        if ($logo && file_exists('../../' . $logo)) {
            unlink('../../' . $logo);
        }

        if ($conn->query("DELETE FROM sites WHERE id = $id")) {
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
            $conn->query("UPDATE sites SET display_order = $position WHERE id = $id");
        }

        echo json_encode(['success' => true]);
        break;

    case 'toggle':
        $id = $_POST['id'] ?? 0;
        $is_active = $_POST['is_active'] ?? 1;

        if ($conn->query("UPDATE sites SET is_active = $is_active WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
