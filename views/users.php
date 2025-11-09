<?php
// views/users.php - Partial view untuk manajemen pengguna
?>

<div class="card">
    <h2>Manajemen Pengguna</h2>
    
    <!-- Form Tambah/Edit Pengguna -->
    <form method="post" id="userForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="<?php echo isset($edit_user) ? 'update' : 'create'; ?>">
        <?php if (isset($edit_user)): ?>
        <input type="hidden" name="user_id" value="<?php echo e($edit_user['id']); ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" 
                       value="<?php echo isset($edit_user) ? e($edit_user['username']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" 
                       value="<?php echo isset($edit_user) ? e($edit_user['email']) : ''; ?>" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" 
                       <?php echo !isset($edit_user) ? 'required' : ''; ?>>
                <?php if (isset($edit_user)): ?>
                <small>Kosongkan jika tidak ingin mengubah password</small>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="role">Role:</label>
                <select id="role" name="role" required>
                    <option value="user" <?php echo (isset($edit_user) && $edit_user['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                    <option value="admin" <?php echo (isset($edit_user) && $edit_user['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                </select>
            </div>
        </div>
        
        <button type="submit" class="btn-primary">
            <?php echo isset($edit_user) ? 'Update Pengguna' : 'Tambah Pengguna'; ?>
        </button>
        
        <?php if (isset($edit_user)): ?>
        <a href="users.php" class="btn-secondary">Batal</a>
        <?php endif; ?>
    </form>
</div>

<!-- Daftar Pengguna -->
<div class="card">
    <h2>Daftar Pengguna</h2>
    
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
                <?php while ($user = $users->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo e($user['username']); ?></td>
                    <td><?php echo e($user['email']); ?></td>
                    <td><?php echo e($user['role']); ?></td>
                    <td><?php echo e(formatDate($user['created_at'])); ?></td>
                    <td>
                        <a href="users.php?edit=<?php echo e($user['id']); ?>" class="btn-warning btn-sm">Edit</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="user_id" value="<?php echo e($user['id']); ?>">
                            <button type="submit" class="btn-danger btn-sm" 
                                    onclick="return confirm('Hapus pengguna ini?')">Hapus</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>