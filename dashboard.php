<?php
include_once 'config/database.php';
include_once 'includes/auth.php';
include_once 'includes/functions.php';
requireLogin();

include_once 'models/User.php';
include_once 'models/Room.php';
include_once 'models/Booking.php';

$database = new Database();
$db = $database->getConnection();

$user = new User($db);
$room = new Room($db);
$booking = new Booking($db);

// Get statistics
$users_count = $user->read()->rowCount();
$rooms_count = $room->read()->rowCount();
$bookings_count = $booking->read()->rowCount();

// Get pending bookings count for admin
$pending_count = 0;
if (isAdmin()) {
    $pending_stmt = $db->prepare("SELECT COUNT(*) as count FROM bookings WHERE status = 'pending'");
    $pending_stmt->execute();
    $pending_result = $pending_stmt->fetch(PDO::FETCH_ASSOC);
    $pending_count = $pending_result['count'] ?? 0;
}

// Get recent bookings (max 5)
$recent_bookings_stmt = $booking->read();
$recent_bookings = [];
$count = 0;
while ($count < 5 && $row = $recent_bookings_stmt->fetch(PDO::FETCH_ASSOC)) {
    $recent_bookings[] = $row;
    $count++;
}

$page_title = "Dashboard";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'views/header.php'; ?>
    
    <div class="container">
        <h1>Dashboard</h1>
        
        <?php include 'views/flash_messages.php'; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Pengguna</h3>
                <p class="stat-number"><?php echo $users_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Ruangan</h3>
                <p class="stat-number"><?php echo $rooms_count; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total Booking</h3>
                <p class="stat-number"><?php echo $bookings_count; ?></p>
            </div>
            <?php if (isAdmin()): ?>
            <div class="stat-card">
                <h3>Booking Pending</h3>
                <p class="stat-number"><?php echo $pending_count; ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-content">
            <!-- Quick Actions for Admin -->
            <?php if (isAdmin()): ?>
            <div class="card">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="bookings.php" class="btn-primary">Kelola Booking</a>
                    <a href="rooms.php" class="btn-secondary">Kelola Ruangan</a>
                    <a href="users.php" class="btn-secondary">Kelola Pengguna</a>
                    <a href="reports.php" class="btn-info">Lihat Laporan</a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Recent Bookings -->
            <div class="card">
                <div class="card-header">
                    <h2>Booking Terbaru</h2>
                    <a href="bookings.php" class="btn-secondary btn-sm">Lihat Semua</a>
                </div>
                
                <?php if (!empty($recent_bookings)): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Ruangan</th>
                                <th>Tanggal</th>
                                <th>Waktu</th>
                                <th>Status</th>
                                <?php if (isAdmin()): ?>
                                <th>Pemohon</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_bookings as $row): ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($row['room_name'] ?? 'N/A'); ?></strong>
                                </td>
                                <td><?php echo e(formatDate($row['date'] ?? '')); ?></td>
                                <td>
                                    <?php echo e(formatTime($row['start_time'] ?? '')); ?> - 
                                    <?php echo e(formatTime($row['end_time'] ?? '')); ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo e($row['status'] ?? 'pending'); ?>">
                                        <?php 
                                        $status_text = [
                                            'pending' => 'Menunggu',
                                            'approved' => 'Disetujui',
                                            'rejected' => 'Ditolak'
                                        ];
                                        echo $status_text[$row['status'] ?? 'pending'] ?? 'Unknown';
                                        ?>
                                    </span>
                                </td>
                                <?php if (isAdmin()): ?>
                                <td><?php echo e($row['user_name'] ?? 'N/A'); ?></td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <p>Belum ada booking yang dibuat.</p>
                    <a href="bookings.php" class="btn-primary">Ajukan Booking Pertama</a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Room Availability Overview -->
            <div class="card">
                <h2>Ketersediaan Ruangan Hari Ini</h2>
                <?php
                $today = date('Y-m-d');
                $rooms_availability = $room->read();
                ?>
                <div class="availability-grid">
                    <?php while ($room_row = $rooms_availability->fetch(PDO::FETCH_ASSOC)): ?>
                    <?php
                    // Check room bookings for today
                    $booking_check = $db->prepare("
                        SELECT COUNT(*) as booked_slots 
                        FROM bookings 
                        WHERE room_id = ? 
                        AND date = ? 
                        AND status = 'approved'
                    ");
                    $booking_check->execute([$room_row['id'], $today]);
                    $result = $booking_check->fetch(PDO::FETCH_ASSOC);
                    $is_available = $result['booked_slots'] == 0;
                    ?>
                    <div class="room-availability <?php echo $is_available ? 'available' : 'booked'; ?>">
                        <h4><?php echo e($room_row['name']); ?></h4>
                        <p>Kapasitas: <?php echo e($room_row['capacity']); ?> orang</p>
                        <span class="availability-status">
                            <?php echo $is_available ? 'Tersedia' : 'Dibooking'; ?>
                        </span>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include 'views/footer.php'; ?>
    <script src="assets/js/script.js"></script>
</body>
</html>