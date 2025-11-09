<?php
// Validasi input
class Validation {
    // Validasi required
    public static function required($value) {
        return !empty(trim($value));
    }

    // Validasi email
    public static function email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    // Validasi panjang string
    public static function minLength($value, $min) {
        return strlen(trim($value)) >= $min;
    }

    public static function maxLength($value, $max) {
        return strlen(trim($value)) <= $max;
    }

    // Validasi angka
    public static function numeric($value) {
        return is_numeric($value);
    }

    // Validasi tanggal
    public static function date($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    // Validasi waktu
    public static function time($time) {
        return preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $time);
    }

    // Validasi file upload
    public static function file($file, $allowedTypes, $maxSize) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $allowedTypes)) {
            return false;
        }

        return true;
    }

    // Validasi waktu booking (end time setelah start time)
    public static function bookingTimes($start, $end) {
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        return $endTime > $startTime;
    }

    // Validasi tanggal tidak di masa lalu
    public static function futureDate($date) {
        $today = new DateTime();
        $bookingDate = new DateTime($date);
        return $bookingDate >= $today;
    }

    // Validasi dalam jam operasional
    public static function withinBusinessHours($start, $end) {
        $startTime = strtotime($start);
        $endTime = strtotime($end);
        $businessStart = strtotime(BUSINESS_HOURS_START);
        $businessEnd = strtotime(BUSINESS_HOURS_END);

        return $startTime >= $businessStart && $endTime <= $businessEnd;
    }

    // Validasi username (hanya huruf, angka, underscore)
    public static function username($username) {
        return preg_match('/^[a-zA-Z0-9_]+$/', $username);
    }

    // Validasi kekuatan password
    public static function passwordStrength($password) {
        // Minimal 8 karakter, mengandung huruf besar, kecil, dan angka
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password);
    }
}
?>