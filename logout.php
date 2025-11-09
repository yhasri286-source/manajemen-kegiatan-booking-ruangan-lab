<?php
session_start();

// Include functions
include_once 'includes/functions.php';

// Log activity sebelum logout
if (isset($_SESSION['user_id'])) {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    logActivity($db, $_SESSION['user_id'], 'logout', 'User logged out');
}

// Hapus semua data session
$_SESSION = array();

// Hapus session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan sukses
$_SESSION['flash_success'] = "Anda telah berhasil logout.";
header("Location: login.php");
exit();
?>