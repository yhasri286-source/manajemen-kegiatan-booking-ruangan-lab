<?php
include_once 'config/database.php';
include_once 'includes/auth.php';
include_once 'models/User.php';

$database = new Database();
$db = $database->getConnection();
$user = new User($db);

if ($_POST) {
    $user->username = $_POST['username'];
    $user->password = $_POST['password'];
    
    if ($user->login()) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        
        $_SESSION['flash_success'] = "Login berhasil!";
        header("Location: dashboard.php");
        exit();
    } else {
        $_SESSION['flash_error'] = "Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Booking Ruangan</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <h1>Sistem Booking Ruangan</h1>
            <h2>Login</h2>
            
            <?php include 'views/flash_messages.php'; ?>
            
            <form method="post" id="loginForm">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>