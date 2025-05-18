<?php
session_start();
require_once('../config.php');
$auth = Auth::getInstance();
$auth->logout();

// Redirect if already logged in
$auth = Auth::getInstance();
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

// Get error message if any
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $error = 'Invalid email or password';
            break;
        case 'session_expired':
            $error = 'Session expired. Please login again';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <form id="loginForm" method="POST" action="auth/login_handler.php">
            <h2>Emergency Alert System</h2>
            
            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit">Login</button>
            <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
    <script src="../js/main.js"></script>
</body>
</body>
</html>