<?php
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/classes/' . $class . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});
// Error reporting for development
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');


// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'emergency_alert_system');

// Twilio Configuration
putenv('TWILIO_ACCOUNT_SID=your_account_sid_here');
putenv('TWILIO_AUTH_TOKEN=your_auth_token_here');
putenv('TWILIO_PHONE_NUMBER=your_twilio_phone_number');

// Load required files
require_once 'classes/Security.php';
require_once 'database/Database.php';
require_once 'database/operations/UserOperations.php';
require_once 'database/operations/EmergencyOperations.php';
require_once 'database/operations/AlertOperations.php';
require_once 'database/operations/SMSOperations.php';

