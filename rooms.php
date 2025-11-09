<?php
include_once 'config/database.php';
include_once 'includes/auth.php';
include_once 'includes/functions.php';
include_once 'includes/csrf.php';
include_once 'models/Room.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();
$room = new Room($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['flash_error'] = "Token CSRF tidak valid.";
        header("Location: rooms.php");
        exit();
    }

    switch ($_POST['action']) {
        case 'create':
            $room->name = $_POST['name'];
            $room->description = $_POST['description'];
            $room->capacity = $_POST['capacity'];
            $room->facilities = $_POST['facilities'];
            
            if ($room->create()) {
                $_SESSION['flash_success'] = "Ruangan berhasil ditambahkan.";
                logActivity($db, $_SESSION['user_id'], 'create_room', 'Created room: ' . $room->name);
            } else {
                $_SESSION['flash_error'] = "Gagal menambahkan ruangan.";
            }
            break;
            
        case 'update':
            $room->id = $_POST['room_id'];
            $room->name = $_POST['name'];
            $room->description = $_POST['description'];
            $room->capacity = $_POST['capacity'];
            $room->facilities = $_POST['facilities'];
            
            if ($room->update()) {
                $_SESSION['flash_success'] = "Ruangan berhasil diperbarui.";
                logActivity($db, $_SESSION['user_id'], 'update_room', 'Updated room: ' . $room->name);
            } else {
                $_SESSION['flash_error'] = "Gagal memperbarui ruangan.";
            }
            break;
            
        case 'delete':
            $room->id = $_POST['room_id'];
            
            // Check if room has bookings
            $check_query = "SELECT COUNT(*) as booking_count FROM bookings WHERE room_id = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(1, $room->id);
            $check_stmt->execute();
            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['booking_count'] > 0) {
                $_SESSION['flash_error'] = "Tidak dapat menghapus ruangan yang memiliki booking.";
            } else {
                if ($room->delete()) {
                    $_SESSION['flash_success'] = "Ruangan berhasil dihapus.";
                    logActivity($db, $_SESSION['user_id'], 'delete_room', 'Deleted room ID: ' . $room->id);
                } else {
                    $_SESSION['flash_error'] = "Gagal menghapus ruangan.";
                }
            }
            break;
    }
    
    header("Location: rooms.php");
    exit();
}

// Handle edit action
$edit_room = null;
if ($action == 'edit' && $id) {
    $room->id = $id;
    if ($room->getById()) {
        $edit_room = [
            'id' => $room->id,
            'name' => $room->name,
            'description' => $room->description,
            'capacity' => $room->capacity,
            'facilities' => $room->facilities
        ];
    } else {
        $_SESSION['flash_error'] = "Ruangan tidak ditemukan.";
        header("Location: rooms.php");
        exit();
    }
}

$rooms = $room->read();
$page_title = "Manajemen Ruangan";
include 'views/header.php';
?>

<div class="container">
    <h1>Manajemen Ruangan</h1>
    
    <?php include 'views/flash_messages.php'; ?>
    
    <!-- Room Form -->
    <div class="card">
        <h2><?php echo $edit_room ? 'Edit Ruangan' : 'Tambah Ruangan Baru'; ?></h2>
        <form method="post" id="roomForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="<?php echo $edit_room ? 'update' : 'create'; ?>">
            <?php if ($edit_room): ?>
            <input type="hidden" name="room_id" value="<?php echo e($edit_room['id']); ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nama Ruangan:</label>
                    <input type="text" id="name" name="name" 
                           value="<?php echo $edit_room ? e($edit_room['name']) : ''; ?>" required
                           maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="capacity">Kapasitas:</label>
                    <input type="number" id="capacity" name="capacity" 
                           value="<?php echo $edit_room ? e($edit_room['capacity']) : ''; ?>" required
                           min="1" max="1000">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi:</label>
                <textarea id="description" name="description" rows="3"
                          placeholder="Deskripsi ruangan..."><?php echo $edit_room ? e($edit_room['description']) : ''; ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="facilities">Fasilitas:</label>
                <textarea id="facilities" name="facilities" rows="3"
                          placeholder="Fasilitas yang tersedia (pisahkan dengan koma)"><?php echo $edit_room ? e($edit_room['facilities']) : ''; ?></textarea>
            </div>
            
            <button type="submit" class="btn-primary">
                <?php echo $edit_room ? 'Update Ruangan' : 'Tambah Ruangan'; ?>
            </button>
            
            <?php if ($edit_room): ?>
            <a href="rooms.php" class="btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Rooms List -->
    <div class="card">
        <h2>Daftar Ruangan</h2>
        
        <?php if ($rooms->rowCount() > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kapasitas</th>
                        <th>Deskripsi</th>
                        <th>Fasilitas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($room_row = $rooms->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo e($room_row['name']); ?></td>
                        <td><?php echo e($room_row['capacity']); ?></td>
                        <td><?php echo e($room_row['description']); ?></td>
                        <td><?php echo e($room_row['facilities']); ?></td>
                        <td>
                            <a href="rooms.php?action=edit&id=<?php echo e($room_row['id']); ?>" 
                               class="btn-warning btn-sm">Edit</a>
                            <form method="post" class="inline-form" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus ruangan ini?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="room_id" value="<?php echo e($room_row['id']); ?>">
                                <button type="submit" class="btn-danger btn-sm">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p>Belum ada ruangan yang terdaftar.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Client-side validation for room form
document.getElementById('roomForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const capacity = document.getElementById('capacity').value;
    
    let errors = [];
    
    if (name.length === 0) {
        errors.push('Nama ruangan harus diisi.');
    }
    
    if (capacity < 1 || capacity > 1000) {
        errors.push('Kapasitas harus antara 1 dan 1000.');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Terjadi kesalahan:\n' + errors.join('\n'));
    }
});
</script>

<?php include 'views/footer.php'; ?>