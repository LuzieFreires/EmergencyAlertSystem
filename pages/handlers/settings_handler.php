<?php
session_start();
require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['userID'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

try {
    $userID = $_SESSION['userID'];
    $userType = $_SESSION['userType'];
    $userOps = new UserOperations();

    // Handle password update if provided
    if (!empty($_POST['newPassword'])) {
        if (empty($_POST['currentPassword'])) {
            throw new Exception('Current password is required to set new password');
        }
        
        // Verify current password
        $stmt = Database::getInstance()->getConnection()->prepare(
            "SELECT password FROM users WHERE userID = ?"
        );
        $stmt->execute([$userID]);
        $currentHash = $stmt->fetchColumn();
        
        if (!password_verify($_POST['currentPassword'], $currentHash)) {
            throw new Exception('Current password is incorrect');
        }
        
        // Update password
        $userOps->updatePassword($userID, password_hash($_POST['newPassword'], PASSWORD_DEFAULT));
    }

    // Update user data
    $data = [
        'email' => $_POST['email'],
        'contact_num' => $_POST['contact_num'],
        'location' => $_POST['location'],
        'name' => $_POST['name'],
        'age' => $data['age'] ?? null,
        'userType' => $userType
    ];

    // Add type-specific data
    if ($userType === 'responder') {
        $data['specialization'] = $_POST['specialization'];
        $data['availabilityStatus'] = $_POST['availabilityStatus'];
    } else {
        $data['medicalCondition'] = $_POST['medicalCondition'];
    }

    $userOps->updateUser($userID, $data);

    echo json_encode([
        'success' => true,
        'message' => 'Settings updated successfully'
    ]);

} catch (Exception $e) {
    error_log("Settings update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}