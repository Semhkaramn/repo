<?php
require_once 'includes/config.php';

$conn = getDBConnection();

// Sosyal medya ikonlarını çek
$social_media = $conn->query("SELECT * FROM social_media WHERE is_active = 1 ORDER BY display_order ASC");

// Carousel için siteleri çek
$carousel_query = "
    SELECT s.*
    FROM sites s
    INNER JOIN carousel_items c ON s.id = c.site_id
    WHERE s.is_active = 1 AND c.is_active = 1
    ORDER BY c.display_order ASC
";
$carousel_sites = $conn->query($carousel_query);

// Tüm aktif siteleri çek
$sites = $conn->query("SELECT * FROM sites WHERE is_active = 1 ORDER BY display_order ASC");

// Sol manşetleri çek
$left_banners_query = "
    SELECT b.*, s.site_url, s.site_name
    FROM sidebar_banners b
    INNER JOIN sites s ON b.site_id = s.id
    WHERE b.position = 'left' AND b.is_active = 1
    ORDER BY b.display_order ASC
";
$left_banners = $conn->query($left_banners_query);

// Sağ manşetleri çek
$right_banners_query = "
    SELECT b.*, s.site_url, s.site_name
    FROM sidebar_banners b
    INNER JOIN sites s ON b.site_id = s.id
    WHERE b.position = 'right' AND b.is_active = 1
    ORDER BY b.display_order ASC
";
$right_banners = $conn->query($right_banners_query);

// Site ayarlarını çek
$settings = [];
$settings_result = $conn->query("SELECT * FROM site_settings");
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Premium bahis siteleri ve casino bonusları">
    <meta name="theme-color" content="#0a0e27">
    <title><?php echo htmlspecialchars($settings['site_title'] ?? 'Premium Site Manager'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Critical CSS */
        body { margin: 0; padding: 0; background: #0a0e27; color: white; font-family: 'Inter', 'Segoe UI', Arial, sans-serif; }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo-container">
                <div class="site-title"><?php echo htmlspecialchars($settings['site_title'] ?? 'Premium Site Manager'); ?></div>
            </div>

            <div class="social-media-container">
                <?php while ($social = $social_media->fetch_assoc()): ?>
                    <a href="<?php echo htmlspecialchars($social['link']); ?>"
                       class="social-icon"
                       style="background: <?php echo htmlspecialchars($social['bg_color']); ?>;"
                       target="_blank"
                       rel="noopener noreferrer"
                       title="<?php echo htmlspecialchars($social['name']); ?>">
                        <img src="<?php echo htmlspecialchars($social['icon_url']); ?>"
                             alt="<?php echo htmlspecialchars($social['name']); ?>">
                    </a>
                <?php endwhile; ?>
            </div>
        </div>
    </header>

    <!-- Carousel Section -->
    <section class="carousel-section">
        <div class="carousel-divider"></div>
        <div class="carousel-container">
            <div class="carousel-track" id="carouselTrack">
                <?php
                $carousel_items = [];
                $carousel_sites->data_seek(0);
                while ($site = $carousel_sites->fetch_assoc()) {
                    $carousel_items[] = $site;
                }

                // İki kez göster (sonsuz döngü için)
                $all_items = array_merge($carousel_items, $carousel_items);
                foreach ($all_items as $site):
                ?>
                    <a href="<?php echo htmlspecialchars($site['site_url']); ?>"
                       class="carousel-item"
                       target="_blank"
                       rel="noopener noreferrer">
                        <img src="<?php echo htmlspecialchars($site['logo_path']); ?>"
                             alt="<?php echo htmlspecialchars($site['site_name']); ?>">
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="main-container">
        <!-- Left Sidebar -->
        <aside class="sidebar sidebar-left">
            <?php while ($banner = $left_banners->fetch_assoc()): ?>
                <a href="<?php echo htmlspecialchars($banner['site_url']); ?>"
                   class="banner"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php echo htmlspecialchars($banner['site_name']); ?>">
                    <img src="<?php echo htmlspecialchars($banner['banner_image']); ?>"
                         alt="<?php echo htmlspecialchars($banner['site_name']); ?>">
                </a>
            <?php endwhile; ?>
        </aside>

        <!-- Sites Section -->
        <main class="premium-section">
            <div class="search-container">
                <input type="text"
                       class="search-box"
                       id="searchBox"
                       placeholder="Site Arayın...">
            </div>

            <h2 class="section-title">VIP SPONSORLAR</h2>

            <div class="premium-grid" id="premiumGrid">
                <?php while ($site = $sites->fetch_assoc()): ?>
                    <a href="<?php echo htmlspecialchars($site['site_url']); ?>"
                       class="premium-card"
                       data-name="<?php echo strtolower(htmlspecialchars($site['site_name'])); ?>"
                       target="_blank"
                       rel="noopener noreferrer">
                        <img src="<?php echo htmlspecialchars($site['logo_path']); ?>"
                             alt="<?php echo htmlspecialchars($site['site_name']); ?>"
                             class="card-logo">
                        <h3 class="card-name"><?php echo htmlspecialchars($site['site_name']); ?></h3>

                        <?php if ($site['bonus_text']): ?>
                            <div class="card-bonus"><?php echo htmlspecialchars($site['bonus_text']); ?></div>
                        <?php endif; ?>

                        <?php if ($site['description']): ?>
                            <p class="card-description"><?php echo htmlspecialchars($site['description']); ?></p>
                        <?php endif; ?>

                        <?php if ($site['promo_code']): ?>
                            <div class="card-promo">KOD: <?php echo htmlspecialchars($site['promo_code']); ?></div>
                        <?php endif; ?>
                    </a>
                <?php endwhile; ?>
            </div>
        </main>

        <!-- Right Sidebar -->
        <aside class="sidebar sidebar-right">
            <?php while ($banner = $right_banners->fetch_assoc()): ?>
                <a href="<?php echo htmlspecialchars($banner['site_url']); ?>"
                   class="banner"
                   target="_blank"
                   rel="noopener noreferrer"
                   title="<?php echo htmlspecialchars($banner['site_name']); ?>">
                    <img src="<?php echo htmlspecialchars($banner['banner_image']); ?>"
                         alt="<?php echo htmlspecialchars($banner['site_name']); ?>">
                </a>
            <?php endwhile; ?>
        </aside>
    </div>

    <script src="assets/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
<?php $conn->close(); ?>
