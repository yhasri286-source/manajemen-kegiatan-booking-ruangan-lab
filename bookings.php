<?php
include_once 'config/database.php';
include_once 'includes/auth.php';
include_once 'includes/functions.php';
include_once 'includes/csrf.php';
requireLogin();

include_once 'models/Booking.php';
include_once 'models/Room.php';

$database = new Database();
$db = $database->getConnection();

$booking = new Booking($db);
$room = new Room($db);

// Handle form submissions
if ($_POST && isset($_POST['action'])) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['flash_error'] = "Token CSRF tidak valid.";
    } else {
        switch ($_POST['action']) {
            case 'create':
                $booking->user_id = $_SESSION['user_id'];
                $booking->room_id = $_POST['room_id'];
                $booking->date = $_POST['date'];
                $booking->start_time = $_POST['start_time'];
                $booking->end_time = $_POST['end_time'];
                $booking->purpose = $_POST['purpose'];
                
                // Validasi input
                if (empty($booking->purpose)) {
                    $_SESSION['flash_error'] = "Tujuan booking harus diisi.";
                } elseif ($booking->date < date('Y-m-d')) {
                    $_SESSION['flash_error'] = "Tanggal booking tidak boleh di masa lalu.";
                } else {
                    if ($booking->create()) {
                        $_SESSION['flash_success'] = "Booking berhasil diajukan. Menunggu persetujuan admin.";
                        logActivity($db, $_SESSION['user_id'], 'create_booking', 'Created booking for room ID: ' . $booking->room_id);
                    } else {
                        $_SESSION['flash_error'] = "Gagal membuat booking. Kemungkinan terjadi konflik jadwal.";
                    }
                }
                break;
                
            case 'update_status':
                if (isAdmin()) {
                    $booking->id = $_POST['booking_id'];
                    $booking->status = $_POST['status'];
                    
                    if ($booking->updateStatus()) {
                        $status_text = $booking->status == 'approved' ? 'disetujui' : 'ditolak';
                        $_SESSION['flash_success'] = "Status booking berhasil diupdate menjadi " . $status_text . ".";
                        logActivity($db, $_SESSION['user_id'], 'update_booking_status', 'Updated booking ID: ' . $booking->id . ' to ' . $booking->status);
                    } else {
                        $_SESSION['flash_error'] = "Gagal mengupdate status booking.";
                    }
                }
                break;
        }
    }
    header("Location: bookings.php");
    exit();
}

// Get all bookings
$bookings = $booking->read();
$rooms_result = $room->read();

// Store rooms in array for multiple use
$rooms_list = [];
while ($room_row = $rooms_result->fetch(PDO::FETCH_ASSOC)) {
    $rooms_list[] = $room_row;
}

$page_title = "Manajemen Booking";
include 'views/header.php';
?>

<div class="container">
    <h1>Manajemen Booking</h1>
    
    <?php include 'views/flash_messages.php'; ?>
    
    <!-- Booking Form -->
    <div class="card">
        <h2>Ajukan Booking Baru</h2>
        
        <?php if (count($rooms_list) > 0): ?>
        <form method="post" id="bookingForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="create">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="room_id">Ruangan: *</label>
                    <select id="room_id" name="room_id" required>
                        <option value="">Pilih Ruangan</option>
                        <?php foreach ($rooms_list as $room_row): ?>
                        <option value="<?php echo e($room_row['id']); ?>">
                            <?php echo e($room_row['name']); ?> (Kapasitas: <?php echo e($room_row['capacity']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="date">Tanggal: *</label>
                    <input type="date" id="date" name="date" 
                           min="<?php echo date('Y-m-d'); ?>" 
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="start_time">Waktu Mulai: *</label>
                    <input type="time" id="start_time" name="start_time" 
                           min="08:00" max="21:00" 
                           value="08:00" required>
                </div>
                
                <div class="form-group">
                    <label for="end_time">Waktu Selesai: *</label>
                    <input type="time" id="end_time" name="end_time" 
                           min="09:00" max="22:00" 
                           value="09:00" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="purpose">Tujuan Booking: *</label>
                <textarea id="purpose" name="purpose" rows="4" 
                          placeholder="Jelaskan tujuan penggunaan ruangan..." required></textarea>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-primary">Ajukan Booking</button>
                <button type="reset" class="btn-secondary">Reset Form</button>
            </div>
        </form>
        <?php else: ?>
        <div class="empty-state">
            <p>Tidak ada ruangan yang tersedia untuk booking.</p>
            <p>Silakan hubungi administrator untuk menambahkan ruangan.</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bookings List -->
    <div class="card">
        <div class="card-header">
            <h2>Daftar Booking</h2>
            <div class="card-info">
                Total: <?php echo $bookings->rowCount(); ?> booking
            </div>
        </div>
        
        <?php if ($bookings->rowCount() > 0): ?>
        <!-- Search and Filter -->
        <div class="search-filter">
            <input type="text" id="searchInput" placeholder="Cari booking...">
            <?php if (isAdmin()): ?>
            <select id="statusFilter">
                <option value="">Semua Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            <?php endif; ?>
        </div>
        
        <div class="table-responsive">
            <table id="bookingsTable">
                <thead>
                    <tr>
                        <th>Ruangan</th>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Tujuan</th>
                        <th>Status</th>
                        <?php if (isAdmin()): ?>
                        <th>Pemohon</th>
                        <th>Aksi</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $bookings->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr data-status="<?php echo e($row['status']); ?>">
                        <td>
                            <strong><?php echo e($row['room_name']); ?></strong>
                        </td>
                        <td><?php echo e(formatDate($row['date'])); ?></td>
                        <td>
                            <?php echo e(formatTime($row['start_time'])); ?> - 
                            <?php echo e(formatTime($row['end_time'])); ?>
                        </td>
                        <td><?php echo e($row['purpose']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo e($row['status']); ?>">
                                <?php 
                                $status_text = [
                                    'pending' => 'Menunggu',
                                    'approved' => 'Disetujui',
                                    'rejected' => 'Ditolak'
                                ];
                                echo $status_text[$row['status']] ?? ucfirst($row['status']);
                                ?>
                            </span>
                        </td>
                        <?php if (isAdmin()): ?>
                        <td><?php echo e($row['user_name']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <?php if ($row['status'] == 'pending'): ?>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo e($row['id']); ?>">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-success btn-sm" 
                                            title="Setujui booking">
                                        ✓ Setujui
                                    </button>
                                </form>
                                <form method="post" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="booking_id" value="<?php echo e($row['id']); ?>">
                                    <input type="hidden" name="status" value="rejected">
                                    <button type="submit" class="btn-danger btn-sm" 
                                            title="Tolak booking"
                                            onclick="return confirm('Tolak booking ini?')">
                                        ✗ Tolak
                                    </button>
                                </form>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="pagination" id="pagination">
            <!-- Pagination will be generated by JavaScript -->
        </div>
        <?php else: ?>
        <div class="empty-state">
            <p>Belum ada booking yang dibuat.</p>
            <p>Silakan ajukan booking baru menggunakan form di atas.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize pagination
    initPagination();
    
    // Initialize search functionality
    initSearch();
    
    // Initialize filter functionality
    initFilter();
    
    // Form validation
    initBookingFormValidation();
});

// Booking form validation
function initBookingFormValidation() {
    const form = document.getElementById('bookingForm');
    if (!form) return;
    
    const dateInput = document.getElementById('date');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');
    
    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
    
    // Time validation
    function validateTimes() {
        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;
        const date = dateInput.value;
        
        let isValid = true;
        let message = '';
        
        // Clear previous errors
        startTimeInput.classList.remove('form-error');
        endTimeInput.classList.remove('form-error');
        
        // Basic time validation
        if (startTime >= endTime) {
            isValid = false;
            message = 'Waktu selesai harus setelah waktu mulai.';
            startTimeInput.classList.add('form-error');
            endTimeInput.classList.add('form-error');
        }
        
        // Business hours validation
        const businessStart = '08:00';
        const businessEnd = '22:00';
        
        if (startTime < businessStart || startTime > businessEnd) {
            isValid = false;
            message = 'Waktu mulai harus antara 08:00 dan 22:00.';
            startTimeInput.classList.add('form-error');
        }
        
        if (endTime < businessStart || endTime > businessEnd) {
            isValid = false;
            message = 'Waktu selesai harus antara 08:00 dan 22:00.';
            endTimeInput.classList.add('form-error');
        }
        
        // Date validation (not in past)
        if (date < today) {
            isValid = false;
            message = 'Tanggal booking tidak boleh di masa lalu.';
            dateInput.classList.add('form-error');
        }
        
        return { isValid, message };
    }
    
    // Real-time validation
    [startTimeInput, endTimeInput, dateInput].forEach(input => {
        input.addEventListener('change', function() {
            const validation = validateTimes();
            if (!validation.isValid) {
                showFlashMessage(validation.message, 'error');
            }
        });
    });
    
    // Form submission validation
    form.addEventListener('submit', function(e) {
        const validation = validateTimes();
        
        if (!validation.isValid) {
            e.preventDefault();
            showFlashMessage(validation.message, 'error');
            
            // Scroll to first error
            const firstError = document.querySelector('.form-error');
            if (firstError) {
                firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    });
}

// Filter functionality
function initFilter() {
    const statusFilter = document.getElementById('statusFilter');
    if (!statusFilter) return;
    
    statusFilter.addEventListener('change', function() {
        const selectedStatus = this.value;
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        
        rows.forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            
            if (!selectedStatus || rowStatus === selectedStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Reinitialize pagination after filtering
        initPagination();
    });
}

// Enhanced search functionality
function initSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;
    
    const table = document.getElementById('bookingsTable');
    const rows = table.querySelectorAll('tbody tr');
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            const isVisible = text.includes(searchTerm);
            
            // Check if row should be visible based on filter
            const statusFilter = document.getElementById('statusFilter');
            if (statusFilter) {
                const selectedStatus = statusFilter.value;
                const rowStatus = row.getAttribute('data-status');
                
                if (selectedStatus && rowStatus !== selectedStatus) {
                    row.style.display = 'none';
                    return;
                }
            }
            
            row.style.display = isVisible ? '' : 'none';
        });
        
        // Update pagination
        initPagination();
    });
}

// Enhanced pagination with filtering support
function initPagination() {
    const table = document.getElementById('bookingsTable');
    if (!table) return;
    
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => {
        return row.style.display !== 'none';
    });
    
    const rowsPerPage = 10;
    const pageCount = Math.ceil(rows.length / rowsPerPage);
    const pagination = document.getElementById('pagination');
    
    if (pageCount <= 1) {
        pagination.innerHTML = '';
        return;
    }
    
    let currentPage = 1;
    
    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        // Hide all rows first
        rows.forEach(row => {
            row.style.display = 'none';
        });
        
        // Show rows for current page
        rows.slice(start, end).forEach(row => {
            row.style.display = '';
        });
        
        updatePaginationButtons();
    }
    
    function updatePaginationButtons() {
        pagination.innerHTML = '';
        
        // Previous button
        if (currentPage > 1) {
            const prevBtn = createPaginationButton('←', () => {
                currentPage--;
                showPage(currentPage);
            });
            pagination.appendChild(prevBtn);
        }
        
        // Page buttons
        const maxVisiblePages = 5;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(pageCount, startPage + maxVisiblePages - 1);
        
        if (endPage - startPage + 1 < maxVisiblePages) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }
        
        for (let i = startPage; i <= endPage; i++) {
            const btn = createPaginationButton(i, () => {
                currentPage = i;
                showPage(currentPage);
            });
            if (i === currentPage) {
                btn.classList.add('active');
            }
            pagination.appendChild(btn);
        }
        
        // Next button
        if (currentPage < pageCount) {
            const nextBtn = createPaginationButton('→', () => {
                currentPage++;
                showPage(currentPage);
            });
            pagination.appendChild(nextBtn);
        }
    }
    
    function createPaginationButton(text, clickHandler) {
        const btn = document.createElement('button');
        btn.textContent = text;
        btn.addEventListener('click', clickHandler);
        return btn;
    }
    
    showPage(1);
}

// Flash message function
function showFlashMessage(message, type) {
    // Remove existing flash messages
    const existingFlash = document.querySelector('.flash-messages');
    if (existingFlash) {
        existingFlash.remove();
    }
    
    // Create new flash message
    const flashDiv = document.createElement('div');
    flashDiv.className = `flash flash-${type}`;
    flashDiv.textContent = message;
    
    const flashContainer = document.createElement('div');
    flashContainer.className = 'flash-messages';
    flashContainer.appendChild(flashDiv);
    
    // Insert after h1
    const h1 = document.querySelector('h1');
    if (h1) {
        h1.parentNode.insertBefore(flashContainer, h1.nextSibling);
    }
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        flashDiv.remove();
    }, 5000);
}
</script>

<?php include 'views/footer.php'; ?>