<?php
class Emergency {
    private $emergencyID;
    private $residentID;
    private $responderID;
    private $location;
    private $type;
    private $severityLevel;
    private $status;
    private $description;
    private $created_at;
    private $updated_at;
    private $db;

    public function __construct($residentID = null, $location = null, $type = null, $severityLevel = null, $description = null) {
        $this->db = Database::getInstance()->getConnection();
        $this->residentID = $residentID;
        $this->location = $location;
        $this->type = $type;
        $this->severityLevel = $severityLevel;
        $this->description = $description;
        $this->status = 'pending';
        $this->created_at = date('Y-m-d H:i:s');
        $this->updated_at = date('Y-m-d H:i:s');
    }

    public function create() {
        try {
            $ops = new EmergencyOperations();
            $data = [
                'residentID' => $this->residentID,
                'location' => $this->location,
                'type' => $this->type,
                'severityLevel' => $this->severityLevel,
                'description' => $this->description
            ];
            $this->emergencyID = $ops->createEmergency($data);
            return $this->emergencyID;
        } catch (Exception $e) {
            error_log("Error creating emergency: " . $e->getMessage());
            throw $e;
        }
    }

    public function assignResponder($responderID) {
        try {
            $ops = new EmergencyOperations();
            if ($ops->assignResponder($this->emergencyID, $responderID)) {
                $this->responderID = $responderID;
                $this->status = 'assigned';
                $this->updated_at = date('Y-m-d H:i:s');
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error assigning responder: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateStatus($newStatus) {
        try {
            $validStatuses = ['pending', 'assigned', 'in_progress', 'resolved', 'cancelled'];
            if (!in_array($newStatus, $validStatuses)) {
                throw new Exception('Invalid status');
            }

            $ops = new EmergencyOperations();
            if ($ops->updateEmergencyStatus($this->emergencyID, $newStatus)) {
                $this->status = $newStatus;
                $this->updated_at = date('Y-m-d H:i:s');
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating status: " . $e->getMessage());
            throw $e;
        }
    }

    public function generateReport() {
        try {
            $sql = "SELECT e.*, 
                           u1.name as resident_name, 
                           u2.name as responder_name,
                           u1.contact_num as resident_contact,
                           u2.contact_num as responder_contact
                    FROM emergencies e
                    JOIN users u1 ON e.residentID = u1.userID
                    LEFT JOIN users u2 ON e.responderID = u2.userID
                    WHERE e.emergencyID = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$this->emergencyID]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                throw new Exception('Emergency not found');
            }

            return [
                'emergency_id' => $data['emergencyID'],
                'type' => $data['type'],
                'severity' => $data['severityLevel'],
                'status' => $data['status'],
                'location' => $data['location'],
                'description' => $data['description'],
                'created_at' => $data['created_at'],
                'response_time' => $data['updated_at'],
                'resident' => [
                    'name' => $data['resident_name'],
                    'contact' => $data['resident_contact']
                ],
                'responder' => [
                    'name' => $data['responder_name'],
                    'contact' => $data['responder_contact']
                ]
            ];
        } catch (Exception $e) {
            error_log("Error generating report: " . $e->getMessage());
            throw $e;
        }
    }

    // Getters
    public function getEmergencyID() { return $this->emergencyID; }
    public function getResidentID() { return $this->residentID; }
    public function getResponderID() { return $this->responderID; }
    public function getLocation() { return $this->location; }
    public function getType() { return $this->type; }
    public function getSeverityLevel() { return $this->severityLevel; }
    public function getStatus() { return $this->status; }
    public function getDescription() { return $this->description; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
}