<?php
// ============================================================
// Konfigurasi Aplikasi — Ikatan Alumni PPKD
// ============================================================

// Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'ppkd_alumni');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site
define('SITE_NAME',  'Ikatan Alumni PPKD');
define('SITE_TAGLINE', 'Menghubungkan Alumni, Membangun Masa Depan');

// Otomatis sesuaikan URL berdasarkan host (localhost atau IP Wi-Fi untuk HP)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
define('SITE_URL', rtrim($scheme . $host . '/ppkd', '/'));

define('SITE_EMAIL', 'info@ppkd.ac.id');
define('SITE_PHONE', '+62 21 1234 5678');
define('SITE_ADDRESS', 'Jl. Pendidikan No. 1, Jakarta Selatan, DKI Jakarta');

// Upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL',  SITE_URL . '/uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB

// Pagination
define('ALUMNI_PER_PAGE', 12);
define('BERITA_PER_PAGE', 9);

// Error reporting (set ke 0 di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Jakarta');

// Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
