<?php
session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = "Anda harus login untuk mengakses halaman ini.";
        header("Location: login.php");
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $_SESSION['flash_error'] = "Akses ditolak. Hanya admin yang dapat mengakses halaman ini.";
        header("Location: dashboard.php");
        exit();
    }
}
?>