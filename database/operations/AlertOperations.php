<?php
class AlertOperations {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createAlert($data) {
        $sql = "INSERT INTO alerts (message, priorityLevel, emergencyID) 
                VALUES (:message, :priorityLevel, :emergencyID)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'message' => $data['message'],
            'priorityLevel' => $data['priorityLevel'],
            'emergencyID' => $data['emergencyID'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    public function getRecentAlerts($limit = 5) {
        $sql = "SELECT * FROM alerts 
                ORDER BY timestamp DESC 
                LIMIT :limit";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function logAlertHistory($alertID, $userID) {
        $sql = "INSERT INTO alert_history (alertID, userID) 
                VALUES (:alertID, :userID)";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'alertID' => $alertID,
            'userID' => $userID
        ]);
    }
}