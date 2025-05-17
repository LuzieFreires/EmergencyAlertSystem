<?php
// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/error.log');

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https://unpkg.com; style-src \'self\' \'unsafe-inline\' https://unpkg.com; img-src \'self\' data: https://*.tile.openstreetmap.org');

// Session security
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.use_strict_mode', 1);

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
require_once 'classes/ErrorHandler.php';
require_once 'classes/Security.php';
require_once 'database/Database.php';
require_once 'database/operations/UserOperations.php';
require_once 'database/operations/EmergencyOperations.php';
require_once 'database/operations/AlertOperations.php';
require_once 'database/operations/SMSOperations.php';

// Initialize error handler
ErrorHandler::getInstance();