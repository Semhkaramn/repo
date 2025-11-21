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
            SELECT b.*, s.site_name, s.site_url
            FROM sidebar_banners b
            INNER JOIN sites s ON b.site_id = s.id
            ORDER BY b.position ASC, b.display_order ASC
        ";
        $result = $conn->query($query);
        $banners = [];
        while ($row = $result->fetch_assoc()) {
            $banners[] = $row;
        }
        echo json_encode(['success' => true, 'data' => $banners]);
        break;

    case 'get':
        $id = $_GET['id'] ?? 0;
        $query = "
            SELECT b.*, s.site_name, s.site_url
            FROM sidebar_banners b
            INNER JOIN sites s ON b.site_id = s.id
            WHERE b.id = $id
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
        $position = $_POST['position'] ?? 'left';

        // Banner image upload
        $banner_image = '';
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/uploads/';
            $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('banner_') . '.' . $file_extension;
            $banner_image = 'assets/uploads/' . $new_filename;

            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_dir . $new_filename)) {
                // Banner uploaded successfully
            } else {
                echo json_encode(['success' => false, 'error' => 'Banner yüklenemedi']);
                exit;
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Banner görseli gerekli']);
            exit;
        }

        // Get max display order for position
        $max_order = $conn->query("SELECT MAX(display_order) as max_order FROM sidebar_banners WHERE position = '$position'")->fetch_assoc()['max_order'];
        $display_order = ($max_order ?? 0) + 1;

        $stmt = $conn->prepare("INSERT INTO sidebar_banners (site_id, banner_image, position, display_order) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issi", $site_id, $banner_image, $position, $display_order);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $conn->insert_id]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'update':
        $id = $_POST['id'] ?? 0;
        $site_id = $_POST['site_id'] ?? 0;
        $is_active = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        // Banner image update (optional)
        $banner_update = '';
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../assets/uploads/';
            $file_extension = pathinfo($_FILES['banner_image']['name'], PATHINFO_EXTENSION);
            $new_filename = uniqid('banner_') . '.' . $file_extension;
            $banner_image = 'assets/uploads/' . $new_filename;

            if (move_uploaded_file($_FILES['banner_image']['tmp_name'], $upload_dir . $new_filename)) {
                // Delete old banner
                $old_banner = $conn->query("SELECT banner_image FROM sidebar_banners WHERE id = $id")->fetch_assoc()['banner_image'];
                if ($old_banner && file_exists('../../' . $old_banner)) {
                    unlink('../../' . $old_banner);
                }
                $banner_update = ", banner_image = '$banner_image'";
            }
        }

        $stmt = $conn->prepare("UPDATE sidebar_banners SET site_id = ?, is_active = ? $banner_update WHERE id = ?");
        $stmt->bind_param("iii", $site_id, $is_active, $id);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        $stmt->close();
        break;

    case 'delete':
        $id = $_POST['id'] ?? 0;

        // Delete banner file
        $banner = $conn->query("SELECT banner_image FROM sidebar_banners WHERE id = $id")->fetch_assoc()['banner_image'];
        if ($banner && file_exists('../../' . $banner)) {
            unlink('../../' . $banner);
        }

        if ($conn->query("DELETE FROM sidebar_banners WHERE id = $id")) {
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
            $conn->query("UPDATE sidebar_banners SET display_order = $position WHERE id = $id");
        }

        echo json_encode(['success' => true]);
        break;

    case 'toggle':
        $id = $_POST['id'] ?? 0;
        $is_active = $_POST['is_active'] ?? 1;

        if ($conn->query("UPDATE sidebar_banners SET is_active = $is_active WHERE id = $id")) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
