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
        <div class="stat-card">
            <h3>Booking Pending</h3>
            <p class="stat-number"><?php echo $pending_count; ?></p>
        </div>
    </div>

    <div class="recent-bookings">
        <h2>Booking Terbaru</h2>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $count = 0;
                    while ($row = $recent_bookings->fetch(PDO::FETCH_ASSOC) && $count < 5): 
                        $count++;
                    ?>
                    <tr>
                        <td><?php echo e($row['room_name']); ?></td>
                        <td><?php echo e(formatDate($row['date'])); ?></td>
                        <td><?php echo e(formatTime($row['start_time']) . ' - ' . formatTime($row['end_time'])); ?></td>
                        <td>
                            <span class="status-<?php echo e($row['status']); ?>">
                                <?php echo ucfirst(e($row['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>