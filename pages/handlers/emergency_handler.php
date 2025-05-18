<?php
session_start();
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Verify authentication
$auth = Auth::getInstance();
if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Validate CSRF token
if (!isset($_POST['csrf_token']) || 
    !Security::validateCSRFToken($_POST['csrf_token'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit();
}

try {
    // Validate and sanitize input
    $type = Security::sanitizeInput($_POST['type'] ?? '');
    $severityLevel = Security::sanitizeInput($_POST['severityLevel'] ?? '');
    $latitude = filter_var($_POST['latitude'] ?? '', FILTER_VALIDATE_FLOAT);
    $longitude = filter_var($_POST['longitude'] ?? '', FILTER_VALIDATE_FLOAT);
    $description = Security::sanitizeInput($_POST['description'] ?? '');

    // Validate required fields
    if (!$type || !$severityLevel || !$description) {
        throw new Exception('Missing required fields');
    }

    // Validate coordinates
    if (!Security::validateCoordinates($latitude, $longitude)) {
        throw new Exception('Invalid coordinates');
    }

    // Validate emergency type
    $validTypes = ['medical', 'fire', 'police', 'accident'];
    if (!in_array($type, $validTypes)) {
        throw new Exception('Invalid emergency type');
    }

    // Validate severity level
    $validSeverity = ['low', 'medium', 'high', 'critical'];
    if (!in_array($severityLevel, $validSeverity)) {
        throw new Exception('Invalid severity level');
    }

    // Create emergency
    $emergency = new Emergency(
        $_SESSION['userID'],
        "$latitude,$longitude",
        $type,
        $severityLevel
    );

    $emergencyOps = new EmergencyOperations();
    $emergencyID = $emergencyOps->createEmergency($emergency);

    if ($emergencyID) {
        // Create alert for the emergency
        $alert = new Alert(
            "New $severityLevel $type emergency reported",
            $severityLevel,
            $emergencyID
        );
        $alert->createAlert();

        echo json_encode([
            'success' => true,
            'emergencyID' => $emergencyID,
            'message' => 'Emergency reported successfully'
        ]);
    } else {
        throw new Exception('Failed to create emergency');
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage(),
        'details' => DEBUG ? $e->getTraceAsString() : null
    ]);
}