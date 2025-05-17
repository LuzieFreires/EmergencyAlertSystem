<?php
class EmergencyOperations {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createEmergency($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO emergencies (residentID, location, type, severityLevel, status) 
                    VALUES (:residentID, :location, :type, :severityLevel, 'pending')";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'residentID' => $data['residentID'],
                'location' => $data['location'],
                'type' => $data['type'],
                'severityLevel' => $data['severityLevel']
            ]);

            $emergencyID = $this->db->lastInsertId();

            // Create alert for the emergency
            $sql = "INSERT INTO alerts (message, priorityLevel, emergencyID) 
                    VALUES (:message, :priorityLevel, :emergencyID)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'message' => "New {$data['type']} emergency reported!",
                'priorityLevel' => $data['severityLevel'],
                'emergencyID' => $emergencyID
            ]);

            $this->db->commit();
            return $emergencyID;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function assignResponder($emergencyID, $responderID) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE emergencies SET 
                    responderID = :responderID,
                    status = 'assigned'
                    WHERE emergencyID = :emergencyID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'responderID' => $responderID,
                'emergencyID' => $emergencyID
            ]);

            // Update responder availability
            $sql = "UPDATE responders SET 
                    availabilityStatus = false
                    WHERE responderID = :responderID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['responderID' => $responderID]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateEmergencyStatus($emergencyID, $status) {
        $sql = "UPDATE emergencies SET 
                status = :status,
                updated_at = CURRENT_TIMESTAMP
                WHERE emergencyID = :emergencyID";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'emergencyID' => $emergencyID
        ]);
    }

    public function getActiveEmergencies() {
        $sql = "SELECT e.*, u.name as resident_name, r.name as responder_name 
                FROM emergencies e
                JOIN users u ON e.residentID = u.userID
                LEFT JOIN users r ON e.responderID = r.userID
                WHERE e.status != 'resolved'
                ORDER BY e.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}