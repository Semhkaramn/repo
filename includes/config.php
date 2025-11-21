<?php
// Session başlat - en başta olmalı
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Veritabanı Bağlantı Ayarları
// Hostinger'dan aldığınız bilgilerle doldurun
define('DB_HOST', 'localhost'); // Hostinger'dan alacağınız host
define('DB_USER', 'u944078781_semhkaramn'); // Veritabanı kullanıcı adı
define('DB_PASS', 'Abuzittin74.'); // Veritabanı şifresi
define('DB_NAME', 'u944078781_sago'); // Veritabanı adı

// Admin Panel Ayarları
define('ADMIN_USERNAME', 'admin'); // Admin kullanıcı adı
define('ADMIN_PASSWORD', 'admin123'); // Admin şifresi (değiştirin!)

// Site Ayarları
define('SITE_URL', 'deeppink-baboon-608965.hostingersite.com'); // Hostinger'a yükledikten sonra gerçek URL
define('UPLOAD_PATH', __DIR__ . '/../assets/uploads/');
define('UPLOAD_URL', SITE_URL . '/assets/uploads/');

// Veritabanı Bağlantısı
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        if ($conn->connect_error) {
            die("Bağlantı hatası: " . $conn->connect_error);
        }

        $conn->set_charset("utf8mb4");
        return $conn;
    } catch (Exception $e) {
        die("Veritabanı bağlantı hatası: " . $e->getMessage());
    }
}
