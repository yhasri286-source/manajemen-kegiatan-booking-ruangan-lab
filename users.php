<?php
include_once 'config/database.php';
include_once 'includes/auth.php';
include_once 'includes/functions.php';
include_once 'includes/csrf.php';
include_once 'models/User.php';
requireAdmin();

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

$action = isset($_GET['action']) ? $_GET['action'] : '';
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Handle form submissions
if ($_POST) {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!validateCSRFToken($csrf_token)) {
        $_SESSION['flash_error'] = "Token CSRF tidak valid.";
        header("Location: users.php");
        exit();
    }

    switch ($_POST['action']) {
        case 'create':
            $user->username = $_POST['username'];
            $user->email = $_POST['email'];
            $user->password = $_POST['password'];
            $user->role = $_POST['role'];
            
            // Check if username or email already exists
            $check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(1, $user->username);
            $check_stmt->bindParam(2, $user->email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $_SESSION['flash_error'] = "Username atau email sudah digunakan.";
            } else {
                if ($user->create()) {
                    $_SESSION['flash_success'] = "User berhasil ditambahkan.";
                    logActivity($db, $_SESSION['user_id'], 'create_user', 'Created user: ' . $user->username);
                } else {
                    $_SESSION['flash_error'] = "Gagal menambahkan user.";
                }
            }
            break;
            
        case 'update':
            $user->id = $_POST['user_id'];
            $user->username = $_POST['username'];
            $user->email = $_POST['email'];
            $user->role = $_POST['role'];
            
            // Check if username or email already exists (excluding current user)
            $check_query = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
            $check_stmt = $db->prepare($check_query);
            $check_stmt->bindParam(1, $user->username);
            $check_stmt->bindParam(2, $user->email);
            $check_stmt->bindParam(3, $user->id);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                $_SESSION['flash_error'] = "Username atau email sudah digunakan.";
            } else {
                if ($user->update()) {
                    // Update password if provided
                    if (!empty($_POST['password'])) {
                        $update_password_query = "UPDATE users SET password = ? WHERE id = ?";
                        $update_stmt = $db->prepare($update_password_query);
                        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                        $update_stmt->bindParam(1, $hashed_password);
                        $update_stmt->bindParam(2, $user->id);
                        $update_stmt->execute();
                    }
                    
                    $_SESSION['flash_success'] = "User berhasil diperbarui.";
                    logActivity($db, $_SESSION['user_id'], 'update_user', 'Updated user: ' . $user->username);
                } else {
                    $_SESSION['flash_error'] = "Gagal memperbarui user.";
                }
            }
            break;
            
        case 'delete':
            $user->id = $_POST['user_id'];
            
            // Prevent self-deletion
            if ($user->id == $_SESSION['user_id']) {
                $_SESSION['flash_error'] = "Tidak dapat menghapus akun sendiri.";
            } else {
                // Check if user has bookings
                $check_query = "SELECT COUNT(*) as booking_count FROM bookings WHERE user_id = ?";
                $check_stmt = $db->prepare($check_query);
                $check_stmt->bindParam(1, $user->id);
                $check_stmt->execute();
                $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['booking_count'] > 0) {
                    $_SESSION['flash_error'] = "Tidak dapat menghapus user yang memiliki booking.";
                } else {
                    if ($user->delete()) {
                        $_SESSION['flash_success'] = "User berhasil dihapus.";
                        logActivity($db, $_SESSION['user_id'], 'delete_user', 'Deleted user ID: ' . $user->id);
                    } else {
                        $_SESSION['flash_error'] = "Gagal menghapus user.";
                    }
                }
            }
            break;
    }
    
    header("Location: users.php");
    exit();
}

// Handle edit action
$edit_user = null;
if ($action == 'edit' && $id) {
    $user->id = $id;
    if ($user->getById()) {
        $edit_user = [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role
        ];
    } else {
        $_SESSION['flash_error'] = "User tidak ditemukan.";
        header("Location: users.php");
        exit();
    }
}

$users = $user->read();
$page_title = "Manajemen Pengguna";
include 'views/header.php';
?>

<div class="container">
    <h1>Manajemen Pengguna</h1>
    
    <?php include 'views/flash_messages.php'; ?>
    
    <!-- User Form -->
    <div class="card">
        <h2><?php echo $edit_user ? 'Edit Pengguna' : 'Tambah Pengguna Baru'; ?></h2>
        <form method="post" id="userForm">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            <input type="hidden" name="action" value="<?php echo $edit_user ? 'update' : 'create'; ?>">
            <?php if ($edit_user): ?>
            <input type="hidden" name="user_id" value="<?php echo e($edit_user['id']); ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" 
                           value="<?php echo $edit_user ? e($edit_user['username']) : ''; ?>" required
                           pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan underscore diperbolehkan">
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo $edit_user ? e($edit_user['email']) : ''; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" 
                           <?php echo !$edit_user ? 'required' : ''; ?>
                           minlength="6">
                    <?php if ($edit_user): ?>
                    <small>Kosongkan jika tidak ingin mengubah password</small>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label for="role">Role:</label>
                    <select id="role" name="role" required>
                        <option value="user" <?php echo ($edit_user && $edit_user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo ($edit_user && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" class="btn-primary">
                <?php echo $edit_user ? 'Update Pengguna' : 'Tambah Pengguna'; ?>
            </button>
            
            <?php if ($edit_user): ?>
            <a href="users.php" class="btn-secondary">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Users List -->
    <div class="card">
        <h2>Daftar Pengguna</h2>
        
        <?php if ($users->rowCount() > 0): ?>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user_row = $users->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <td><?php echo e($user_row['username']); ?></td>
                        <td><?php echo e($user_row['email']); ?></td>
                        <td>
                            <span class="role-<?php echo e($user_row['role']); ?>">
                                <?php echo ucfirst(e($user_row['role'])); ?>
                            </span>
                        </td>
                        <td><?php echo e(formatDate($user_row['created_at'])); ?></td>
                        <td>
                            <a href="users.php?action=edit&id=<?php echo e($user_row['id']); ?>" 
                               class="btn-warning btn-sm">Edit</a>
                            <?php if ($user_row['id'] != $_SESSION['user_id']): ?>
                            <form method="post" class="inline-form" 
                                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus user ini?');">
                                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo e($user_row['id']); ?>">
                                <button type="submit" class="btn-danger btn-sm">Hapus</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p>Belum ada pengguna yang terdaftar.</p>
        <?php endif; ?>
    </div>
</div>

<script>
// Client-side validation for user form
document.getElementById('userForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const isEdit = <?php echo $edit_user ? 'true' : 'false'; ?>;
    
    let errors = [];
    
    // Username validation
    if (!/^[a-zA-Z0-9_]+$/.test(username)) {
        errors.push('Username hanya boleh mengandung huruf, angka, dan underscore.');
    }
    
    if (username.length < 3 || username.length > 30) {
        errors.push('Username harus antara 3 dan 30 karakter.');
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        errors.push('Format email tidak valid.');
    }
    
    // Password validation
    if (!isEdit || password.length > 0) {
        if (password.length < 6) {
            errors.push('Password harus minimal 6 karakter.');
        }
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Terjadi kesalahan:\n' + errors.join('\n'));
    }
});

// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthIndicator = document.getElementById('password-strength') || 
        (function() {
            const indicator = document.createElement('small');
            indicator.id = 'password-strength';
            this.parentNode.appendChild(indicator);
            return indicator;
        }).call(this);
    
    let strength = '';
    let color = '#e74c3c';
    
    if (password.length === 0) {
        strength = '';
    } else if (password.length < 6) {
        strength = 'Lemah';
        color = '#e74c3c';
    } else if (password.length < 10) {
        strength = 'Sedang';
        color = '#f39c12';
    } else {
        strength = 'Kuat';
        color = '#27ae60';
    }
    
    strengthIndicator.textContent = strength;
    strengthIndicator.style.color = color;
});
</script>

<?php include 'views/footer.php'; ?>