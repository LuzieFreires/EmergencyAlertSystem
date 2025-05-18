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
            // Validate required fields
            $requiredFields = ['email', 'contact_num', 'location', 'name', 'age'];
            foreach ($requiredFields as $field) {
                if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
                    throw new Exception("$field is required");
                }
            }

            // Validate age is numeric and within reasonable range
            if (!is_numeric($_POST['age']) || $_POST['age'] < 1 || $_POST['age'] > 150) {
                throw new Exception('Age must be a valid number between 1 and 150');
            }

            $data = [
                'email' => trim($_POST['email']),
                'contact_num' => trim($_POST['contact_num']),
                'location' => trim($_POST['location']),
                'name' => trim($_POST['name']),
                'age' => (int)$_POST['age'],
                'userType' => $userType
            ];

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            $userOps->updateUser($userID, $data);
            
            // Update session with new data
            $_SESSION['user'] = [
                'email' => $data['email'],
                'name' => $data['name'],
                'contact_num' => $data['contact_num'],
                'location' => $data['location'],
                'age' => $data['age']
            ];
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
        'message' => 'Settings updated successfully',
        'redirect' => isset($_POST['redirect']) ? $_POST['redirect'] : null
    ]);

} catch (Exception $e) {
    error_log("Settings update error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}