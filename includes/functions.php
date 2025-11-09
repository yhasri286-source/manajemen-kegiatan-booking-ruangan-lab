<?php
// Fungsi untuk redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Fungsi untuk escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk format tanggal
function formatDate($date, $format = 'd-m-Y') {
    $dateTime = new DateTime($date);
    return $dateTime->format($format);
}

// Fungsi untuk format waktu
function formatTime($time, $format = 'H:i') {
    $dateTime = DateTime::createFromFormat('H:i:s', $time);
    return $dateTime ? $dateTime->format($format) : $time;
}

// Fungsi untuk menampilkan flash message
function displayFlashMessage() {
    if (isset($_SESSION['flash_success'])) {
        echo '<div class="flash flash-success">' . e($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        echo '<div class="flash flash-error">' . e($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
}

// Fungsi untuk generate random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Fungsi untuk log aktivitas
function logActivity($db, $user_id, $action, $description = '') {
    $query = "INSERT INTO activity_logs (user_id, action, description) VALUES (:user_id, :action, :description)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':action', $action);
    $stmt->bindParam(':description', $description);
    return $stmt->execute();
}

// Fungsi untuk validasi tanggal
function isValidDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Fungsi untuk validasi waktu
function isValidTime($time) {
    return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $time);
}

// Fungsi untuk mendapatkan IP address pengguna
function getClientIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

// Fungsi untuk sanitasi input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk validasi email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Fungsi untuk generate password hash
function generatePasswordHash($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk verifikasi password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}
?>