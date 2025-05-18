<?php
class Responder extends User {
    private $responderID;
    private $specialization;
    private $availabilityStatus;
    private $db;
    
    public function __construct($email, $password, $contact_num, $location, $name, $age, $specialization) {
        parent::__construct($email, $password, $contact_num, $location, $name, $age);
        $this->specialization = $specialization;
        $this->availabilityStatus = true;
        $this->userType = 'responder';
        $this->db = Database::getInstance()->getConnection();
    }

    public function register() {
        try {
            $this->db->beginTransaction();

            // Insert into users table first
            $sql = "INSERT INTO users (email, password, contact_num, location, name, age, userType) 
                    VALUES (:email, :password, :contact_num, :location, :name, :age, :userType)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $this->email,
                'password' => $this->password,
                'contact_num' => $this->contact_num,
                'location' => $this->location,
                'name' => $this->name,
                'age' => $this->age,
                'userType' => $this->userType
            ]);

            $this->userID = $this->db->lastInsertId();

            // Insert into responders table
            $sql = "INSERT INTO responders (userID, specialization, availabilityStatus) 
                    VALUES (:userID, :specialization, :availabilityStatus)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'userID' => $this->userID,
                'specialization' => $this->specialization,
                'availabilityStatus' => $this->availabilityStatus
            ]);

            $this->responderID = $this->db->lastInsertId();
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error registering responder: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateAvailability($status) {
        try {
            $sql = "UPDATE responders SET 
                    availabilityStatus = :status
                    WHERE userID = :userID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'status' => $status,
                'userID' => $this->userID
            ]);

            $this->availabilityStatus = $status;
            return true;
        } catch (Exception $e) {
            error_log("Error updating availability: " . $e->getMessage());
            throw $e;
        }
    }

    public function acceptEmergency($emergencyID) {
        try {
            if (!$this->availabilityStatus) {
                throw new Exception('Responder is not available');
            }

            $emergency = new Emergency();
            $ops = new EmergencyOperations();
            
            if ($ops->assignResponder($emergencyID, $this->userID)) {
                // Update responder availability
                $this->updateAvailability(false);
                
                // Create alert for assignment
                $alert = new Alert(
                    "Emergency assigned to {$this->name}",
                    'high',
                    $emergencyID
                );
                $alert->createAlert();
                
                // Send SMS notification
                $sms = new SMS();
                $message = "Emergency #{$emergencyID} has been assigned to you. Please respond immediately.";
                $sms->sendSMS($this->contact_num, $message);
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error accepting emergency: " . $e->getMessage());
            throw $e;
        }
    }

    public function closeEmergency($emergencyID) {
        try {
            $ops = new EmergencyOperations();
            if ($ops->updateEmergencyStatus($emergencyID, 'resolved')) {
                // Update responder availability
                $this->updateAvailability(true);
                
                // Create alert for resolution
                $alert = new Alert(
                    "Emergency #{$emergencyID} has been resolved",
                    'low',
                    $emergencyID
                );
                $alert->createAlert();
                
                // Generate report
                $emergency = new Emergency();
                $report = $emergency->generateReport();
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error closing emergency: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateProfile($data) {
        try {
            $this->db->beginTransaction();

            // Update users table
            $sql = "UPDATE users SET 
                    name = :name,
                    contact_num = :contact_num,
                    location = :location,
                    age = :age
                    WHERE userID = :userID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'name' => $data['name'] ?? $this->name,
                'contact_num' => $data['contact_num'] ?? $this->contact_num,
                'location' => $data['location'] ?? $this->location,
                'age' => $data['age'] ?? $this->age,
                'userID' => $this->userID
            ]);

            // Update responders table
            if (isset($data['specialization'])) {
                $sql = "UPDATE responders SET 
                        specialization = :specialization
                        WHERE userID = :userID";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'specialization' => $data['specialization'],
                    'userID' => $this->userID
                ]);

                $this->specialization = $data['specialization'];
            }

            // Update object properties
            $this->name = $data['name'] ?? $this->name;
            $this->contact_num = $data['contact_num'] ?? $this->contact_num;
            $this->location = $data['location'] ?? $this->location;
            $this->age = $data['age'] ?? $this->age;

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error updating responder profile: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewAlertHistory() {
        try {
            $sql = "SELECT a.*, e.type as emergency_type, e.status as emergency_status 
                    FROM alerts a
                    JOIN emergencies e ON a.emergencyID = e.emergencyID
                    WHERE e.responderID = :responderID
                    ORDER BY a.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['responderID' => $this->userID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error viewing alert history: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewAssignedEmergencies() {
        try {
            $sql = "SELECT e.*, u.name as resident_name, u.contact_num as resident_contact
                    FROM emergencies e
                    JOIN users u ON e.residentID = u.userID
                    WHERE e.responderID = :responderID
                    AND e.status != 'resolved'
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['responderID' => $this->userID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error viewing assigned emergencies: " . $e->getMessage());
            throw $e;
        }
    }
    // Getters
    public function getResponderID() { return $this->responderID; }
    public function getSpecialization() { return $this->specialization; }
    public function getAvailabilityStatus() { return $this->availabilityStatus; }

    // Abstract method implementation
    public function requestEmergency() { return false; }
}