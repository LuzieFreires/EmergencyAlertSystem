<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify user is authenticated
    $auth = Auth::getInstance();
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit();
    }

    // Validate input
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
    $priorityLevel = filter_input(INPUT_POST, 'priorityLevel', FILTER_SANITIZE_STRING);
    $emergencyID = filter_input(INPUT_POST, 'emergencyID', FILTER_VALIDATE_INT);

    if (!$message || !$priorityLevel) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid input']);
        exit();
    }

    // Validate priority level
    $validPriorities = ['low', 'medium', 'high', 'critical'];
    if (!in_array($priorityLevel, $validPriorities)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid priority level']);
        exit();
    }

    try {
        $alert = new Alert($message, $priorityLevel, $emergencyID);
        if ($alert->createAlert()) {
            echo json_encode([
                'success' => true,
                'alertID' => $alert->getAlertID(),
                'message' => 'Alert created and notifications sent'
            ]);
        } else {
            throw new Exception('Failed to create alert');
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
    }
    exit();
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
exit();