<?php
// views/flash_messages.php - Partial view untuk menampilkan flash messages
?>

<div class="flash-messages">
    <?php
    if (isset($_SESSION['flash_success'])) {
        echo '<div class="flash flash-success">' . e($_SESSION['flash_success']) . '</div>';
        unset($_SESSION['flash_success']);
    }
    if (isset($_SESSION['flash_error'])) {
        echo '<div class="flash flash-error">' . e($_SESSION['flash_error']) . '</div>';
        unset($_SESSION['flash_error']);
    }
    if (isset($_SESSION['flash_warning'])) {
        echo '<div class="flash flash-warning">' . e($_SESSION['flash_warning']) . '</div>';
        unset($_SESSION['flash_warning']);
    }
    if (isset($_SESSION['flash_info'])) {
        echo '<div class="flash flash-info">' . e($_SESSION['flash_info']) . '</div>';
        unset($_SESSION['flash_info']);
    }
    ?>
</div>