<?php
// views/rooms.php - Partial view untuk manajemen ruangan
?>

<div class="card">
    <h2>Manajemen Ruangan</h2>
    
    <!-- Form Tambah/Edit Ruangan -->
    <form method="post" id="roomForm">
        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
        <input type="hidden" name="action" value="<?php echo isset($edit_room) ? 'update' : 'create'; ?>">
        <?php if (isset($edit_room)): ?>
        <input type="hidden" name="room_id" value="<?php echo e($edit_room['id']); ?>">
        <?php endif; ?>
        
        <div class="form-row">
            <div class="form-group">
                <label for="name">Nama Ruangan:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo isset($edit_room) ? e($edit_room['name']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="capacity">Kapasitas:</label>
                <input type="number" id="capacity" name="capacity" 
                       value="<?php echo isset($edit_room) ? e($edit_room['capacity']) : ''; ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="description">Deskripsi:</label>
            <textarea id="description" name="description"><?php echo isset($edit_room) ? e($edit_room['description']) : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="facilities">Fasilitas:</label>
            <textarea id="facilities" name="facilities"><?php echo isset($edit_room) ? e($edit_room['facilities']) : ''; ?></textarea>
        </div>
        
        <button type="submit" class="btn-primary">
            <?php echo isset($edit_room) ? 'Update Ruangan' : 'Tambah Ruangan'; ?>
        </button>
        
        <?php if (isset($edit_room)): ?>
        <a href="rooms.php" class="btn-secondary">Batal</a>
        <?php endif; ?>
    </form>
</div>

<!-- Daftar Ruangan -->
<div class="card">
    <h2>Daftar Ruangan</h2>
    
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
                <?php while ($room = $rooms->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                    <td><?php echo e($room['name']); ?></td>
                    <td><?php echo e($room['capacity']); ?></td>
                    <td><?php echo e($room['description']); ?></td>
                    <td><?php echo e($room['facilities']); ?></td>
                    <td>
                        <a href="rooms.php?edit=<?php echo e($room['id']); ?>" class="btn-warning btn-sm">Edit</a>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="room_id" value="<?php echo e($room['id']); ?>">
                            <button type="submit" class="btn-danger btn-sm" 
                                    onclick="return confirm('Hapus ruangan ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>