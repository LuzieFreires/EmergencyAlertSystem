<?php
class Resident extends User {
    private $residentID;
    private $medicalCondition;

    public function __construct($email, $password, $contact_num, $location, $name, $age, $medicalCondition) {
        parent::__construct($email, $password, $contact_num, $location, $name, $age);
        $this->medicalCondition = $medicalCondition;
        $this->userType = 'resident';
    }

    public function register() {
        // Implementation
    }

    public function updateMedicalInfo($medicalInfo) {
        $this->medicalCondition = $medicalInfo;
    }

    public function viewResponderStatus() {
        // Implementation
    }

    // Implement abstract methods
    public function requestEmergency() {}
    public function updateProfile($data) {}
    public function viewAlertHistory() {}
}