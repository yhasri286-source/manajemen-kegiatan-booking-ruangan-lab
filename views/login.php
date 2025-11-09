<?php
// views/login.php - Partial view untuk form login
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-form">
            <h1><?php echo APP_NAME; ?></h1>
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
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="remember_me"> Remember me
                    </label>
                </div>
                
                <button type="submit" class="btn-primary">Login</button>
            </form>
            
            <div class="login-links">
                <a href="forgot_password.php">Lupa password?</a>
            </div>
        </div>
    </div>

    <script src="<?php echo ASSETS_URL; ?>js/script.js"></script>
</body>
</html>