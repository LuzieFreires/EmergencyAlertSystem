<?php
class Auth {
    private $db;
    private static $instance = null;

    private function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function login($email, $password) {
        try {
            $sql = "SELECT u.*, 
                    CASE 
                        WHEN r.responderID IS NOT NULL THEN 'responder'
                        ELSE 'resident'
                    END as userType,
                    r.specialization,
                    r.availabilityStatus,
                    res.medicalCondition
                    FROM users u
                    LEFT JOIN responders r ON u.userID = r.responderID
                    LEFT JOIN residents res ON u.userID = res.residentID
                    WHERE u.email = :email";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Start secure session
                $this->startSecureSession();
                
                // Set session variables
                $_SESSION['userID'] = $user['userID'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['userType'] = $user['userType'];
                $_SESSION['last_activity'] = time();
                
                // Log login activity
                $this->logActivity($user['userID'], 'login');
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }

    public function logout() {
        if (isset($_SESSION['userID'])) {
            $this->logActivity($_SESSION['userID'], 'logout');
        }
        
        // Destroy session
        session_destroy();
        
        // Clear session cookie
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
    }

    private function startSecureSession() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.use_strict_mode', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_secure', 1);
            ini_set('session.cookie_httponly', 1);
            ini_set('session.cookie_samesite', 'Lax');
            
            session_start();
        }
    }

    public function isLoggedIn() {
        if (!isset($_SESSION['userID']) || !isset($_SESSION['last_activity'])) {
            return false;
        }

        // Check session timeout (30 minutes)
        if (time() - $_SESSION['last_activity'] > 1800) {
            $this->logout();
            return false;
        }

        $_SESSION['last_activity'] = time();
        return true;
    }

    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            header('Location: /group3/pages/login.php');
            exit();
        }
    }

    public function requireRole($roles) {
        $this->requireLogin();
        
        $roles = (array)$roles;
        if (!in_array($_SESSION['userType'], $roles)) {
            header('Location: /group3/pages/dashboard.php');
            exit();
        }
    }

    private function logActivity($userID, $action) {
        $sql = "INSERT INTO activity_logs (userID, action, ip_address) 
                VALUES (:userID, :action, :ip_address)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'userID' => $userID,
            'action' => $action,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    }
}