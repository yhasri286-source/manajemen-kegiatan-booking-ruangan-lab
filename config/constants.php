<?php
// Konstanta untuk path
define('BASE_URL', 'http://localhost/sistem-booking/');
define('ASSETS_URL', BASE_URL . 'assets/');

// Konstanta untuk pengaturan aplikasi
define('APP_NAME', 'Sistem Booking Ruangan');
define('ITEMS_PER_PAGE', 10);

// Konstanta untuk upload file
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_MIME_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);

// Konstanta untuk remember me
define('REMEMBER_ME_EXPIRY', 30 * 24 * 60 * 60); // 30 hari

// Konstanta untuk reset password
define('RESET_TOKEN_EXPIRY', 1 * 60 * 60); // 1 jam

// Konstanta role
define('ROLE_ADMIN', 'admin');
define('ROLE_USER', 'user');

// Konstanta status booking
define('STATUS_PENDING', 'pending');
define('STATUS_APPROVED', 'approved');
define('STATUS_REJECTED', 'rejected');

// Konstanta untuk waktu
define('BUSINESS_HOURS_START', '08:00:00');
define('BUSINESS_HOURS_END', '22:00:00');
?>