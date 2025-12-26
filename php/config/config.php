<?php
/**
 * Site Configuration File
 * Contains all site-wide settings and constants
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Environment settings
define('DEVELOPMENT_MODE', true); // Set to false in production
define('SITE_NAME', 'Tourist Hotel Management System');
define('SITE_TITLE', 'Silverleaf Hotel - Bandarawela, Sri Lanka');

// Path settings
define('BASE_PATH', dirname(dirname(__DIR__)));
define('BASE_URL', 'http://localhost/TOURIST-HOTEL-MANAGEMENT-SYSTEM/');
define('ADMIN_URL', BASE_URL . 'admin/');

// Directory paths
define('UPLOAD_DIR', BASE_PATH . '/uploads/');
define('GALLERY_DIR', UPLOAD_DIR . 'gallery/');
define('ROOM_IMG_DIR', UPLOAD_DIR . 'rooms/');

// File upload settings
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/jpg']);

// Email settings
define('ADMIN_EMAIL', 'admin@touristhotel.lk');
define('CONTACT_EMAIL', 'info@touristhotel.lk');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM_NAME', 'Silverleaf Hotel');

// Pagination settings
define('ITEMS_PER_PAGE', 10);
define('ROOMS_PER_PAGE', 9);

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour in seconds
define('REMEMBER_ME_LIFETIME', 30 * 24 * 60 * 60); // 30 days

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('HASH_ALGORITHM', 'sha256');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Booking settings
define('MIN_BOOKING_DAYS', 1);
define('MAX_BOOKING_DAYS', 30);
define('BOOKING_CUTOFF_HOURS', 24); // Hours before check-in
define('CANCELLATION_HOURS', 48); // Hours before check-in for free cancellation

// Payment settings (for future implementation)
define('CURRENCY', 'LKR');
define('CURRENCY_SYMBOL', 'Rs.');
define('TAX_RATE', 0.12); // 12% tax
define('SERVICE_CHARGE_RATE', 0.10); // 10% service charge

// Time settings
define('TIMEZONE', 'Asia/Colombo');
date_default_timezone_set(TIMEZONE);

// Error reporting
if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/logs/error.log');
}

// Status constants
define('STATUS_ACTIVE', 1);
define('STATUS_INACTIVE', 0);
define('STATUS_PENDING', 'pending');
define('STATUS_CONFIRMED', 'confirmed');
define('STATUS_CANCELLED', 'cancelled');
define('STATUS_COMPLETED', 'completed');

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_STAFF', 'staff');
define('ROLE_GUEST', 'guest');

// Include database configuration
require_once __DIR__ . '/database.php';
?>
