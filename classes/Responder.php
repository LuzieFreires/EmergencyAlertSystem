<?php
class Responder extends User {
    private $specialization;
    private $availabilityStatus;

    public function __construct($email, $password, $contact_num, $location, $name, $age, $specialization) {
        parent::__construct($email, $password, $contact_num, $location, $name, $age);
        $this->specialization = $specialization;
        $this->availabilityStatus = true;
        $this->userType = 'responder';
    }

    public function updateAvailability($status) {
        $this->availabilityStatus = $status;
    }

    public function acceptEmergency($emergencyID) {
        // Implementation
    }

    public function closeEmergency($emergencyID) {
        // Implementation
    }

    // Implement abstract methods
    public function requestEmergency() {}
    public function updateProfile($data) {}
    public function viewAlertHistory() {}
}