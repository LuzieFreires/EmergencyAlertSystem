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

            if (!isset($_POST['medicalCondition'])) {
                throw new Exception('Medical condition field is required');
            }

            $data = [
                'userType' => 'resident',
                'medicalCondition' => trim($_POST['medicalCondition']),
                // Include all required user fields
                'email' => $_SESSION['user']['email'],
                'contact_num' => $_SESSION['user']['contact_num'],
                'location' => $_SESSION['user']['location'],
                'name' => $_SESSION['user']['name'],
                'age' => $_SESSION['user']['age']
            ];

            $userOps->updateUser($userID, $data);
            
            // Update session with new medical data
            $_SESSION['additionalData']['medicalCondition'] = $data['medicalCondition'];
            break;

        case 'passwordForm':
            if (empty($_POST['currentPassword'])) {
                throw new Exception('Current password is required');
            }
            
            if (empty($_POST['newPassword'])) {
                throw new Exception('New password is required');
            }

            if (empty($_POST['confirmPassword'])) {
                throw new Exception('Password confirmation is required');
            }

            if ($_POST['newPassword'] !== $_POST['confirmPassword']) {
                throw new Exception('New passwords do not match');
            }

            // Verify current password
            $stmt = Database::getInstance()->getConnection()->prepare(
                "SELECT password FROM users WHERE userID = ?"
            );
            $stmt->execute([$userID]);
            $currentHash = $stmt->fetchColumn();
            
            if (!$currentHash || !password_verify($_POST['currentPassword'], $currentHash)) {
                throw new Exception('Current password is incorrect');
            }

            // Validate new password strength
            if (strlen($_POST['newPassword']) < 8) {
                throw new Exception('New password must be at least 8 characters long');
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