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
    $formType = $_POST['form_type'] ?? '';
    $userOps = new UserOperations();

    switch ($formType) {
        case 'personalInfoForm':
            $data = [
                'email' => $_POST['email'],
                'contact_num' => $_POST['contact_num'],
                'location' => $_POST['location'],
                'name' => $_POST['name'],
                'age' => $_POST['age'],
                'userType' => $userType
            ];
            $userOps->updateUser($userID, $data);
            break;

        case 'medicalInfoForm':
            if ($userType !== 'resident') {
                throw new Exception('Unauthorized access to medical information');
            }
            $data = [
                'userType' => 'resident',
                'medicalCondition' => $_POST['medicalCondition']
            ];
            $userOps->updateUser($userID, $data);
            break;

        case 'responderInfoForm':
            if ($userType !== 'responder') {
                throw new Exception('Unauthorized access to responder information');
            }
            $data = [
                'userType' => 'responder',
                'specialization' => $_POST['specialization'],
                'availabilityStatus' => $_POST['availabilityStatus']
            ];
            $userOps->updateUser($userID, $data);
            break;

        case 'passwordForm':
            if (empty($_POST['currentPassword'])) {
                throw new Exception('Current password is required');
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
            
            if (empty($_POST['newPassword'])) {
                throw new Exception('New password is required');
            }
            
            $userOps->updatePassword($userID, password_hash($_POST['newPassword'], PASSWORD_DEFAULT));
            break;

        default:
            throw new Exception('Invalid form type');
    }

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