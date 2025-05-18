<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize input
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $contact_num = filter_input(INPUT_POST, 'contact_num', FILTER_SANITIZE_STRING);
    $location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
    $age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $userType = filter_input(INPUT_POST, 'userType', FILTER_SANITIZE_STRING);
    
    // Additional fields based on user type
    $specialization = filter_input(INPUT_POST, 'specialization', FILTER_SANITIZE_STRING);
    $medicalCondition = filter_input(INPUT_POST, 'medicalCondition', FILTER_SANITIZE_STRING);
    
    // Validate required fields
    if (!$email || !$name || !$password || !$contact_num || !$location || !$age || !$userType) {
        header('Location: ../register.php?error=invalid_input');
        exit();
    }
    
    try {
        $db = Database::getInstance()->getConnection();
        
        // Check if email already exists
        $stmt = $db->prepare("SELECT userID FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            header('Location: ../register.php?error=email_exists');
            exit();
        }
        
        // Begin transaction
        $db->beginTransaction();
        
        // Insert into users table
        $stmt = $db->prepare("INSERT INTO users (email, password, contact_num, location, name, age, userType) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $contact_num, $location, $name, $age, $userType]);
        
        $userID = $db->lastInsertId();
        
        // Insert additional info based on user type
        if ($userType === 'responder' && $specialization) {
            $stmt = $db->prepare("INSERT INTO responders (responderID, specialization) VALUES (?, ?)");
            $stmt->execute([$userID, $specialization]);
        } elseif ($userType === 'resident') {
            $stmt = $db->prepare("INSERT INTO residents (residentID, medicalCondition) VALUES (?, ?)");
            $stmt->execute([$userID, $medicalCondition]);
        }
        
        $db->commit();
        
        // Redirect to login page with success message
        header('Location: ../login.php?registration=success');
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Registration error: " . $e->getMessage());
        header('Location: ../register.php?error=registration_failed');
        exit();
    }
}

header('Location: ../register.php');
exit();