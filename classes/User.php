<?php
abstract class User {
    protected $userID;
    protected $email;
    protected $password;
    protected $contact_num;
    protected $location;
    protected $name;
    protected $age;
    protected $userType;

    public function __construct($email, $password, $contact_num, $location, $name, $age) {
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->contact_num = $contact_num;
        $this->location = $location;
        $this->name = $name;
        $this->age = $age;
    }

    abstract public function requestEmergency();
    abstract public function updateProfile($data);
    abstract public function viewAlertHistory();

    public function updatePassword($currentPassword, $newPassword) {
        // Verify current password
        if (!password_verify($currentPassword, $this->password)) {
            throw new Exception('Current password is incorrect');
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update in database
        $userOps = new UserOperations();
        if ($userOps->updatePassword($this->userID, $hashedPassword)) {
            $this->password = $hashedPassword;
            return true;
        }
        return false;
    }
}