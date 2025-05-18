<?php
class Resident extends User {
    private $residentID;
    private $medicalCondition;
    private $db;

    public function __construct($email, $password, $contact_num, $location, $name, $age, $medicalCondition) {
        parent::__construct($email, $password, $contact_num, $location, $name, $age);
        $this->medicalCondition = $medicalCondition;
        $this->userType = 'resident';
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

            // Insert into residents table
            $sql = "INSERT INTO residents (userID, medicalCondition) VALUES (:userID, :medicalCondition)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'userID' => $this->userID,
                'medicalCondition' => $this->medicalCondition
            ]);

            $this->residentID = $this->db->lastInsertId();
            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error registering resident: " . $e->getMessage());
            throw $e;
        }
    }

    public function requestEmergency($type, $severityLevel, $location = null, $description = '') {
        try {
            // Use provided location or default to resident's location
            $emergencyLocation = $location ?? $this->location;
            
            $emergency = new Emergency(
                $this->userID,
                $emergencyLocation,
                $type,
                $severityLevel,
                $description
            );
            
            $emergencyID = $emergency->create();
            
            // Get available responders
            $responderOps = new UserOperations();
            $availableResponders = $responderOps->getAvailableResponders();
            
            // Create Google Maps link from coordinates
            $coordinates = explode(',', $emergencyLocation);
            $mapLink = "https://www.google.com/maps?q={$coordinates[0]},{$coordinates[1]}";
            
            // Prepare message with map link
            $message = "New {$type} emergency reported\n";
            $message .= "Severity: {$severityLevel}\n";
            $message .= "Location: {$mapLink}";
            
            if ($description) {
                $message .= "\nDetails: {$description}";
            }
            
            foreach ($availableResponders as $responder) {
                try {
                    $sms = new SMS($responder['contact_num'], $message);
                    $sms->sendSMS();
                } catch (Exception $e) {
                    error_log("Failed to send SMS to responder {$responder['userID']}: " . $e->getMessage());
                    // Continue sending to other responders even if one fails
                    continue;
                }
            }
            
            return $emergencyID;
        } catch (Exception $e) {
            error_log("Error requesting emergency: " . $e->getMessage());
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

            // Update residents table
            if (isset($data['medicalCondition'])) {
                $sql = "UPDATE residents SET 
                        medicalCondition = :medicalCondition
                        WHERE userID = :userID";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'medicalCondition' => $data['medicalCondition'],
                    'userID' => $this->userID
                ]);

                $this->medicalCondition = $data['medicalCondition'];
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
            error_log("Error updating resident profile: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewAlertHistory() {
        try {
            $sql = "SELECT a.*, e.type as emergency_type, e.status as emergency_status 
                    FROM alerts a
                    JOIN emergencies e ON a.emergencyID = e.emergencyID
                    WHERE e.residentID = :residentID
                    ORDER BY a.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['residentID' => $this->userID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error viewing alert history: " . $e->getMessage());
            throw $e;
        }
    }

    public function viewResponderStatus() {
        try {
            $sql = "SELECT u.name, u.contact_num, e.type as emergency_type, 
                           e.status as emergency_status, e.created_at
                    FROM emergencies e
                    JOIN users u ON e.responderID = u.userID
                    WHERE e.residentID = :residentID
                    AND e.status != 'resolved'
                    ORDER BY e.created_at DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['residentID' => $this->userID]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error viewing responder status: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateMedicalInfo($medicalInfo) {
        try {
            $sql = "UPDATE residents SET 
                    medicalCondition = :medicalCondition
                    WHERE userID = :userID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'medicalCondition' => $medicalInfo,
                'userID' => $this->userID
            ]);

            $this->medicalCondition = $medicalInfo;
            return true;
        } catch (Exception $e) {
            error_log("Error updating medical info: " . $e->getMessage());
            throw $e;
        }
    }

    // Getters
    public function getResidentID() { return $this->residentID; }
    public function getMedicalCondition() { return $this->medicalCondition; }
}