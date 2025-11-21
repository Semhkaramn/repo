<?php
require_once '../includes/config.php';

// Login kontrolü - İyileştirilmiş
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Clear any existing session data
    $_SESSION = array();
    session_destroy();

    header('Location: login.php');
    exit();
}

$conn = getDBConnection();

// Verileri çek
$social_media = $conn->query("SELECT * FROM social_media ORDER BY display_order ASC");
$carousel_logos = $conn->query("SELECT c.*, s.site_name, s.logo_path as logo_url, s.site_url as site_link FROM carousel_items c INNER JOIN sites s ON c.site_id = s.id ORDER BY c.display_order ASC");
$premium_sites = $conn->query("SELECT *, logo_path as logo_url, site_url as site_link FROM sites ORDER BY display_order ASC");
$left_banners = $conn->query("SELECT b.*, s.site_name, s.site_url FROM sidebar_banners b INNER JOIN sites s ON b.site_id = s.id WHERE b.position = 'left' ORDER BY b.display_order ASC");
$right_banners = $conn->query("SELECT b.*, s.site_name, s.site_url FROM sidebar_banners b INNER JOIN sites s ON b.site_id = s.id WHERE b.position = 'right' ORDER BY b.display_order ASC");
$settings_result = $conn->query("SELECT * FROM site_settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <h1 class="admin-title">Premium Site Manager - Admin Panel</h1>
        <div>
            <a href="../index.php" class="btn btn-primary btn-small" target="_blank">Siteyi Görüntüle</a>
            <a href="logout.php" class="logout-btn">Çıkış Yap</a>
        </div>
    </header>

    <!-- Container -->
    <div class="admin-container">
        <!-- Tabs -->
        <div class="tabs">
            <button class="tab active" data-tab="settings">Site Ayarları</button>
            <button class="tab" data-tab="social">Sosyal Medya</button>
            <button class="tab" data-tab="carousel">Carousel Logoları</button>
            <button class="tab" data-tab="premium">Premium Siteler</button>
            <button class="tab" data-tab="banners">Manşetler</button>
        </div>

        <!-- Site Ayarları Tab -->
        <div class="tab-content active" id="settings">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Site Ayarları</h2>
                </div>
                <form id="settingsForm">
                    <div class="form-group">
                        <label class="form-label">Site Başlığı</label>
                        <input type="text" class="form-input" name="site_title"
                               value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Logo URL</label>
                        <input type="text" class="form-input" name="site_logo"
                               value="<?php echo htmlspecialchars($settings['site_logo'] ?? ''); ?>">
                        <small style="color: #a0a0a0;">Logo yüklemek için aşağıdaki alana sürükleyin veya tıklayın</small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Logo Yükle</label>
                        <div class="file-upload-area" id="logoUpload">
                            <div class="upload-icon">📁</div>
                            <p class="upload-text">Dosyayı buraya sürükleyin veya tıklayın</p>
                            <input type="file" accept="image/*">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Kaydet</button>
                </form>
            </div>
        </div>

        <!-- Sosyal Medya Tab -->
        <div class="tab-content" id="social">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Sosyal Medya İkonları</h2>
                    <button class="btn btn-primary" onclick="openModal('socialModal')">Yeni Ekle</button>
                </div>
                <table class="data-table" id="socialTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sıra</th>
                            <th>İkon</th>
                            <th>İsim</th>
                            <th>Link</th>
                            <th>Renk</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $social_media->fetch_assoc()): ?>
                        <tr data-id="<?php echo $item['id']; ?>">
                            <td><span class="drag-handle">☰</span></td>
                            <td><img src="<?php echo htmlspecialchars($item['icon_url']); ?>" style="background: <?php echo $item['bg_color']; ?>; padding: 5px; border-radius: 5px;"></td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['link']); ?></td>
                            <td><span class="color-preview" style="background: <?php echo $item['bg_color']; ?>"></span> <?php echo $item['bg_color']; ?></td>
                            <td><?php echo $item['is_active'] ? '✅ Aktif' : '❌ Pasif'; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-small btn-primary" onclick="editSocial(<?php echo $item['id']; ?>)">Düzenle</button>
                                <button class="btn btn-small btn-danger" onclick="deleteSocial(<?php echo $item['id']; ?>)">Sil</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Carousel Tab -->
        <div class="tab-content" id="carousel">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Kayan Logolar</h2>
                    <button class="btn btn-primary" onclick="openModal('carouselModal')">Yeni Ekle</button>
                </div>
                <table class="data-table" id="carouselTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sıra</th>
                            <th>Logo</th>
                            <th>Site Adı</th>
                            <th>Link</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $carousel_logos->fetch_assoc()): ?>
                        <tr data-id="<?php echo $item['id']; ?>">
                            <td><span class="drag-handle">☰</span></td>
                            <td><img src="<?php echo htmlspecialchars($item['logo_url']); ?>"></td>
                            <td><?php echo htmlspecialchars($item['site_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['site_link']); ?></td>
                            <td><?php echo $item['is_active'] ? '✅ Aktif' : '❌ Pasif'; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-small btn-primary" onclick="editCarousel(<?php echo $item['id']; ?>)">Düzenle</button>
                                <button class="btn btn-small btn-danger" onclick="deleteCarousel(<?php echo $item['id']; ?>)">Sil</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Premium Siteler Tab -->
        <div class="tab-content" id="premium">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Premium Siteler</h2>
                    <button class="btn btn-primary" onclick="openModal('premiumModal')">Yeni Ekle</button>
                </div>
                <table class="data-table" id="premiumTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sıra</th>
                            <th>Logo</th>
                            <th>Site Adı</th>
                            <th>Açıklama</th>
                            <th>Bonus</th>
                            <th>Link</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $premium_sites->fetch_assoc()): ?>
                        <tr data-id="<?php echo $item['id']; ?>">
                            <td><span class="drag-handle">☰</span></td>
                            <td><img src="<?php echo htmlspecialchars($item['logo_url']); ?>"></td>
                            <td><?php echo htmlspecialchars($item['site_name']); ?></td>
                            <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</td>
                            <td><?php echo htmlspecialchars($item['bonus_text'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['site_link']); ?></td>
                            <td><?php echo $item['is_active'] ? '✅ Aktif' : '❌ Pasif'; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-small btn-primary" onclick="editPremium(<?php echo $item['id']; ?>)">Düzenle</button>
                                <button class="btn btn-small btn-danger" onclick="deletePremium(<?php echo $item['id']; ?>)">Sil</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manşetler Tab -->
        <div class="tab-content" id="banners">
            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Sol Manşetler</h2>
                    <button class="btn btn-primary" onclick="openBannerModal('left')">Yeni Ekle</button>
                </div>
                <table class="data-table" id="leftBannersTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sıra</th>
                            <th>Resim</th>
                            <th>Başlık</th>
                            <th>Link</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $left_banners->fetch_assoc()): ?>
                        <tr data-id="<?php echo $item['id']; ?>">
                            <td><span class="drag-handle">☰</span></td>
                            <td><img src="<?php echo htmlspecialchars($item['banner_image']); ?>"></td>
                            <td><?php echo htmlspecialchars($item['site_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['site_url'] ?? '-'); ?></td>
                            <td><?php echo $item['is_active'] ? '✅ Aktif' : '❌ Pasif'; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-small btn-primary" onclick="editBanner(<?php echo $item['id']; ?>)">Düzenle</button>
                                <button class="btn btn-small btn-danger" onclick="deleteBanner(<?php echo $item['id']; ?>)">Sil</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="admin-card">
                <div class="card-header">
                    <h2 class="card-title">Sağ Manşetler</h2>
                    <button class="btn btn-primary" onclick="openBannerModal('right')">Yeni Ekle</button>
                </div>
                <table class="data-table" id="rightBannersTable">
                    <thead>
                        <tr>
                            <th style="width: 50px;">Sıra</th>
                            <th>Resim</th>
                            <th>Başlık</th>
                            <th>Link</th>
                            <th>Durum</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $right_banners->fetch_assoc()): ?>
                        <tr data-id="<?php echo $item['id']; ?>">
                            <td><span class="drag-handle">☰</span></td>
                            <td><img src="<?php echo htmlspecialchars($item['banner_image']); ?>"></td>
                            <td><?php echo htmlspecialchars($item['site_name'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($item['site_url'] ?? '-'); ?></td>
                            <td><?php echo $item['is_active'] ? '✅ Aktif' : '❌ Pasif'; ?></td>
                            <td class="action-buttons">
                                <button class="btn btn-small btn-primary" onclick="editBanner(<?php echo $item['id']; ?>)">Düzenle</button>
                                <button class="btn btn-small btn-danger" onclick="deleteBanner(<?php echo $item['id']; ?>)">Sil</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modals - Sosyal Medya -->
    <div class="modal" id="socialModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Sosyal Medya Ekle/Düzenle</h3>
                <button class="modal-close" onclick="closeModal('socialModal')">✕</button>
            </div>
            <form id="socialForm">
                <input type="hidden" name="id" id="social_id">
                <div class="form-group">
                    <label class="form-label">İsim</label>
                    <input type="text" class="form-input" name="name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">İkon URL</label>
                    <input type="text" class="form-input" name="icon_url" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Link</label>
                    <input type="url" class="form-input" name="link" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Arka Plan Rengi</label>
                    <input type="color" class="form-input form-color" name="bg_color" value="#000000">
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-select" name="is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </div>
    </div>

    <!-- Modal - Carousel -->
    <div class="modal" id="carouselModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Carousel'a Site Ekle</h3>
                <button class="modal-close" onclick="closeModal('carouselModal')">✕</button>
            </div>
            <form id="carouselForm">
                <input type="hidden" name="id" id="carousel_id">
                <div class="form-group">
                    <label class="form-label">Site Seç</label>
                    <select class="form-select" name="site_id" required>
                        <option value="">Lütfen bir site seçin...</option>
                        <?php
                        $sites_query = $conn->query("SELECT * FROM sites WHERE is_active = 1 ORDER BY site_name ASC");
                        while ($site = $sites_query->fetch_assoc()):
                        ?>
                        <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['site_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-select" name="is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </div>
    </div>

    <!-- Modal - Premium Site -->
    <div class="modal" id="premiumModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Premium Site Ekle/Düzenle</h3>
                <button class="modal-close" onclick="closeModal('premiumModal')">✕</button>
            </div>
            <form id="premiumForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="premium_id">
                <div class="form-group">
                    <label class="form-label">Site Adı</label>
                    <input type="text" class="form-input" name="site_name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Logo Yükle</label>
                    <input type="file" class="form-input" name="logo" accept="image/*">
                    <small style="color: #a0a0a0;">Logo yüklemek zorunludur (yeni eklerken)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Site URL</label>
                    <input type="url" class="form-input" name="site_url" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Açıklama</label>
                    <textarea class="form-textarea" name="description" required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Bonus Metni</label>
                    <input type="text" class="form-input" name="bonus_text" placeholder="Örn: 1000₺ DENEME BONUSU">
                </div>
                <div class="form-group">
                    <label class="form-label">Promo Kod</label>
                    <input type="text" class="form-input" name="promo_code" placeholder="Örn: ESER1000">
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-select" name="is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </div>
    </div>

    <!-- Modal - Banner -->
    <div class="modal" id="bannerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Banner Ekle/Düzenle</h3>
                <button class="modal-close" onclick="closeModal('bannerModal')">✕</button>
            </div>
            <form id="bannerForm" enctype="multipart/form-data">
                <input type="hidden" name="id" id="banner_id">
                <input type="hidden" name="position" id="banner_position">
                <div class="form-group">
                    <label class="form-label">Site Seç</label>
                    <select class="form-select" name="site_id" required>
                        <option value="">Lütfen bir site seçin...</option>
                        <?php
                        $sites_query2 = $conn->query("SELECT * FROM sites WHERE is_active = 1 ORDER BY site_name ASC");
                        while ($site = $sites_query2->fetch_assoc()):
                        ?>
                        <option value="<?php echo $site['id']; ?>"><?php echo htmlspecialchars($site['site_name']); ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Banner Görseli Yükle</label>
                    <input type="file" class="form-input" name="banner_image" accept="image/*" required>
                    <small style="color: #a0a0a0;">Tavsiye edilen boyut: 300x600 px</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Durum</label>
                    <select class="form-select" name="is_active">
                        <option value="1">Aktif</option>
                        <option value="0">Pasif</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Kaydet</button>
            </form>
        </div>
    </div>

    <script src="../assets/js/admin.js"></script>
</body>
</html>
<?php $conn->close(); ?>
