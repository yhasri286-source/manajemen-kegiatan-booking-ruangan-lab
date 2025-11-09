<?php
require_once 'config/constants.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' . APP_NAME : APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body>
    <header class="header">
        <nav class="navbar">
            <div class="nav-brand">
                <a href="dashboard.php"><?php echo APP_NAME; ?></a>
            </div>
            <ul class="nav-menu">
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="bookings.php">Booking</a></li>
                <li><a href="rooms.php">Ruangan</a></li>
                <?php if (isAdmin()): ?>
                <li><a href="users.php">Pengguna</a></li>
                <li><a href="reports.php">Laporan</a></li>
                <?php endif; ?>
                <li class="nav-user">
                    <span>Hello, <?php echo e($_SESSION['username']); ?></span>
                    <a href="logout.php">Logout</a>
                </li>
            </ul>
        </nav>
    </header>