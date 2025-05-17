<?php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $auth = Auth::getInstance();
    
    if ($auth->login($email, $password)) {
        header('Location: ../dashboard.php');
        exit();
    } else {
        header('Location: ../login.php?error=invalid_credentials');
        exit();
    }
}

header('Location: ../login.php');
exit();