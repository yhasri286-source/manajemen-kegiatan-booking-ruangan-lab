<?php
include_once 'config/database.php';
include_once 'includes/auth.php';
include_once 'includes/functions.php';
include_once 'models/Booking.php';
include_once 'models/Room.php';
include_once 'models/User.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$room = new Room($db);
$user = new User($db);

// Get statistics
$total_bookings = $booking->read()->rowCount();
$total_rooms = $room->read()->rowCount();
$total_users = $user->read()->rowCount();

// Get bookings by status
$query = "SELECT status, COUNT(*) as count FROM bookings GROUP BY status";
$stmt = $db->prepare($query);
$stmt->execute();
$bookings_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get monthly bookings
$query = "SELECT 
            DATE_FORMAT(created_at, '%Y-%m') as month,
            COUNT(*) as count 
          FROM bookings 
          WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
          ORDER BY month DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$monthly_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get room usage statistics
$query = "SELECT 
            r.name,
            COUNT(b.id) as booking_count
          FROM rooms r
          LEFT JOIN bookings b ON r.id = b.room_id AND b.status = 'approved'
          GROUP BY r.id
          ORDER BY booking_count DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$room_usage = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user activity
$query = "SELECT 
            u.username,
            COUNT(b.id) as booking_count
          FROM users u
          LEFT JOIN bookings b ON u.id = b.user_id
          GROUP BY u.id
          ORDER BY booking_count DESC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$user_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);

$page_title = "Laporan & Statistik";
include 'views/header.php';
?>

<div class="container">
    <h1>Laporan & Statistik</h1>
    
    <?php include 'views/flash_messages.php'; ?>

    <!-- Summary Statistics -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Booking</h3>
            <p class="stat-number"><?php echo $total_bookings; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Ruangan</h3>
            <p class="stat-number"><?php echo $total_rooms; ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Pengguna</h3>
            <p class="stat-number"><?php echo $total_users; ?></p>
        </div>
        <div class="stat-card">
            <h3>Booking Bulan Ini</h3>
            <p class="stat-number">
                <?php 
                $current_month = date('Y-m');
                $month_count = 0;
                foreach ($monthly_bookings as $monthly) {
                    if ($monthly['month'] == $current_month) {
                        $month_count = $monthly['count'];
                        break;
                    }
                }
                echo $month_count;
                ?>
            </p>
        </div>
    </div>

    <div class="reports-grid">
        <!-- Bookings by Status -->
        <div class="card">
            <h2>Booking Berdasarkan Status</h2>
            <div class="chart-container">
                <canvas id="statusChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Monthly Bookings -->
        <div class="card">
            <h2>Booking 6 Bulan Terakhir</h2>
            <div class="chart-container">
                <canvas id="monthlyChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Room Usage -->
        <div class="card">
            <h2>Penggunaan Ruangan</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Ruangan</th>
                            <th>Jumlah Booking</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($room_usage as $usage): ?>
                        <tr>
                            <td><?php echo e($usage['name']); ?></td>
                            <td><?php echo e($usage['booking_count']); ?></td>
                            <td>
                                <?php 
                                $percentage = $total_bookings > 0 ? ($usage['booking_count'] / $total_bookings) * 100 : 0;
                                echo number_format($percentage, 1) . '%';
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- User Activity -->
        <div class="card">
            <h2>Top 10 Pengguna Aktif</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Jumlah Booking</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_activity as $activity): ?>
                        <tr>
                            <td><?php echo e($activity['username']); ?></td>
                            <td><?php echo e($activity['booking_count']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Export Options -->
    <div class="card">
        <h2>Ekspor Data</h2>
        <div class="export-options">
            <button onclick="exportToCSV('bookingsTable', 'bookings')" class="btn-primary">Ekspor Booking ke CSV</button>
            <button onclick="exportToPDF()" class="btn-secondary">Ekspor Laporan ke PDF</button>
        </div>
        
        <!-- Hidden table for CSV export -->
        <table id="bookingsTable" style="display: none;">
            <thead>
                <tr>
                    <th>Ruangan</th>
                    <th>Pengguna</th>
                    <th>Tanggal</th>
                    <th>Waktu Mulai</th>
                    <th>Waktu Selesai</th>
                    <th>Tujuan</th>
                    <th>Status</th>
                    <th>Tanggal Dibuat</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $all_bookings = $booking->read();
                while ($row = $all_bookings->fetch(PDO::FETCH_ASSOC)):
                ?>
                <tr>
                    <td><?php echo e($row['room_name']); ?></td>
                    <td><?php echo e($row['user_name']); ?></td>
                    <td><?php echo e($row['date']); ?></td>
                    <td><?php echo e($row['start_time']); ?></td>
                    <td><?php echo e($row['end_time']); ?></td>
                    <td><?php echo e($row['purpose']); ?></td>
                    <td><?php echo e($row['status']); ?></td>
                    <td><?php echo e($row['created_at']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart for bookings by status
const statusCtx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: [
            <?php 
            $status_labels = [];
            $status_data = [];
            $status_colors = [];
            
            foreach ($bookings_by_status as $status) {
                $status_labels[] = "'" . ucfirst($status['status']) . "'";
                $status_data[] = $status['count'];
                
                // Assign colors based on status
                switch($status['status']) {
                    case 'approved':
                        $status_colors[] = "'#27ae60'";
                        break;
                    case 'pending':
                        $status_colors[] = "'#f39c12'";
                        break;
                    case 'rejected':
                        $status_colors[] = "'#e74c3c'";
                        break;
                    default:
                        $status_colors[] = "'#95a5a6'";
                }
            }
            echo implode(', ', $status_labels);
            ?>
        ],
        datasets: [{
            data: [<?php echo implode(', ', $status_data); ?>],
            backgroundColor: [<?php echo implode(', ', $status_colors); ?>],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});

// Chart for monthly bookings
const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
const monthlyChart = new Chart(monthlyCtx, {
    type: 'line',
    data: {
        labels: [
            <?php 
            $month_labels = [];
            $month_data = [];
            $reversed_months = array_reverse($monthly_bookings);
            
            foreach ($reversed_months as $monthly) {
                $month_labels[] = "'" . date('M Y', strtotime($monthly['month'] . '-01')) . "'";
                $month_data[] = $monthly['count'];
            }
            echo implode(', ', $month_labels);
            ?>
        ],
        datasets: [{
            label: 'Jumlah Booking',
            data: [<?php echo implode(', ', $month_data); ?>],
            backgroundColor: 'rgba(52, 152, 219, 0.2)',
            borderColor: 'rgba(52, 152, 219, 1)',
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Export to PDF function
function exportToPDF() {
    // In a real implementation, you would make an AJAX call to generate PDF
    alert('Fitur ekspor PDF akan diimplementasikan menggunakan library seperti TCPDF atau DomPDF');
    // window.open('generate_pdf.php', '_blank');
}
</script>

<?php include 'views/footer.php'; ?>