-- Sponsor Site Database Schema
-- Hostinger MySQL veritabanınızda çalıştırın

-- Sponsors tablosu
CREATE TABLE IF NOT EXISTS sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    logo_url VARCHAR(500) NOT NULL,
    website_url VARCHAR(500),
    is_vip TINYINT(1) DEFAULT 0,
    bonus_text TEXT,
    bonus_code VARCHAR(100),
    grid_size TINYINT DEFAULT 3,
    color VARCHAR(7) DEFAULT '#ff0000',
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Social links tablosu
CREATE TABLE IF NOT EXISTS social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50) NOT NULL,
    icon_class VARCHAR(100),
    url VARCHAR(500) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Site settings tablosu
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users tablosu
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Varsayılan site ayarları
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_logo', 'assets/images/logo.svg'),
('site_title', 'Sponsor Site'),
('site_description', 'En iyi sponsorlar ve ortaklar'),
('header_text', 'Hoşgeldiniz')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

-- Varsayılan admin kullanıcısı (kullanıcı adı: admin, şifre: admin123)
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE username=username;

-- Örnek sosyal medya linkleri
INSERT INTO social_links (platform, icon_class, url, sort_order) VALUES
('Facebook', 'fab fa-facebook', 'https://facebook.com', 1),
('Twitter', 'fab fa-twitter', 'https://twitter.com', 2),
('Instagram', 'fab fa-instagram', 'https://instagram.com', 3),
('Telegram', 'fab fa-telegram', 'https://telegram.org', 4)
ON DUPLICATE KEY UPDATE platform=platform;

-- Örnek sponsorlar
INSERT INTO sponsors (name, logo_url, website_url, is_vip, bonus_text, bonus_code, grid_size, color, sort_order) VALUES
('Mariobet', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/mariobet.png', 'https://cutt.ly/Fr58m3Po', 1, '1000₺ DENEME BONUSU', 'KOD : ESER1000', 6, '#ff0000', 1),
('Misty Casino', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/mistycasino.png', 'https://t.ly/mistysloteser', 1, '1000₺', 'DENEME BONUSU', 6, '#ff0000', 2),
('Onwin', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/onwin.png', 'https://cutt.ly/S9lgXWl', 1, '750₺', 'DENEME BONUSU', 6, '#ff00ff', 3),
('Tarafbet', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/tarafbet.png', 'https://cutt.ly/lr8W3VgL', 1, '1500₺ DENEME BONUSU', 'KOD: ES1500 (Kayıt Olurken)', 6, '#ff0000', 4),
('Bahiscom', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/bahiscom.png', 'https://cutt.ly/gr7G2Vci', 1, 'BAHİSCOM 1000₺', 'KOD: ESER1000 (Canlıdan Alınız)', 3, '#ff0000', 5),
('Asyabahis', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/asyabahis.png', 'https://t2m.io/absloteser', 1, '250₺', 'DENEME BONUSU', 3, '#ff0000', 6),
('Starzbet', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/starzbet.png', 'https://cutt.ly/SwSbaoKJ', 1, '750₺', 'DENEME BONUSU', 3, '#ff0000', 7),
('Dumanbet', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/dumanbet.png', 'https://t2m.io/dbsloteser', 1, '500₺ DENEME BONUSU', 'Canlı Destekten Alınız', 3, '#ff0000', 8),
('Casibom', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/casibom.png', 'https://t.ly/casibomeser', 0, '1000₺ DENEME BONUSU', 'YAYINDA OYNADIĞIM', 3, '#ff0000', 9),
('Stake', 'https://skjdngjzkngvzjkxgnsdkghsfsd.com//clients/logo/stake.png', 'https://stake1076.com/tr/?c=eserbey&offer=eserbey', 0, '$3 x 7 Kayıt Bonusu', 'VPN\'siz Giriş', 3, '#ff0000', 10)
ON DUPLICATE KEY UPDATE name=name;
