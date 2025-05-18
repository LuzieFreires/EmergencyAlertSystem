<?php
ini_set('session.cookie_secure', '0');
ini_set('session.cookie_httponly', '1');
session_start();
require_once('../config.php');

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
        case 'email_exists':
            $error = 'Email already registered';
            break;
        case 'invalid_input':
            $error = 'Please fill all required fields';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Emergency Alert System</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
</head>
<body>
    <div class="login-container">
        <form id="registerForm" method="POST" action="auth/register_handler.php">
            <h2>Create Account</h2>
            
            <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
                <input type="text" name="name" placeholder="Full Name" required>
            </div>
            
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <div class="form-group">
                <input type="tel" name="contact_num" placeholder="Contact Number" required>
            </div>
            
            <div class="form-group">
                <input type="text" name="location" placeholder="Location" required>
            </div>
            
            <div class="form-group">
                <input type="number" name="age" placeholder="Age" required min="1">
            </div>
            
            <div class="form-group">
                <select name="userType" required>
                    <option value="resident">Resident</option>
                    <option value="responder">Emergency Responder</option>
                </select>
            </div>
            
            <div id="responderFields" style="display: none;">
                <div class="form-group">
                    <input type="text" name="specialization" placeholder="Specialization">
                </div>
            </div>
            
            <div id="residentFields" style="display: none;">
                <div class="form-group">
                    <textarea name="medicalCondition" placeholder="Medical Conditions (if any)"></textarea>
                </div>
            </div>
            
            <button type="submit">Register</button>
            
            <p class="login-link">Already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
    
    <script>
    document.querySelector('select[name="userType"]').addEventListener('change', function() {
        const responderFields = document.getElementById('responderFields');
        const residentFields = document.getElementById('residentFields');
        
        if (this.value === 'responder') {
            responderFields.style.display = 'block';
            residentFields.style.display = 'none';
            document.querySelector('input[name="specialization"]').required = true;
        } else {
            responderFields.style.display = 'none';
            residentFields.style.display = 'block';
            document.querySelector('input[name="specialization"]').required = false;
        }
    });
    </script>
    <script src="../js/main.js"></script>
    </body>
</body>
</html>