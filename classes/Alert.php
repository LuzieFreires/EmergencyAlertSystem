<?php
class Alert {
    private $alertID;
    private $message;
    private $timestamp;
    private $priorityLevel;
    private $emergencyID;
    private $db;

    public function __construct($message, $priorityLevel, $emergencyID = null) {
        $this->message = $message;
        $this->timestamp = date('Y-m-d H:i:s');
        $this->priorityLevel = $priorityLevel;
        $this->emergencyID = $emergencyID;
        $this->db = Database::getInstance()->getConnection();
    }

    public function createAlert() {
        try {
            $alertOps = new AlertOperations();
            $data = [
                'message' => $this->message,
                'priorityLevel' => $this->priorityLevel,
                'emergencyID' => $this->emergencyID
            ];
            
            $this->alertID = $alertOps->createAlert($data);
            
            if ($this->alertID) {
                $this->sendToAllUsers();
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Alert creation error: " . $e->getMessage());
            return false;
        }
    }

    public function sendToAllUsers() {
        try {
            // Get all active users
            $sql = "SELECT userID, contact_num FROM users WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $users = $stmt->fetchAll();

            $smsOps = new SMSOperations();
            
            foreach ($users as $user) {
                // Create SMS notification
                $smsData = [
                    'recipientNumber' => $user['contact_num'],
                    'content' => $this->formatSMSMessage(),
                    'alertID' => $this->alertID
                ];
                
                $smsOps->LogSMS($smsData);
                
                // Log alert history
                $this->logAlertHistory($user['userID']);
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Alert notification error: " . $e->getMessage());
            return false;
        }
    }

    public function logAlertHistory($userID) {
        try {
            $alertOps = new AlertOperations();
            return $alertOps->logAlertHistory($this->alertID, $userID);
        } catch (Exception $e) {
            error_log("Alert history logging error: " . $e->getMessage());
            return false;
        }
    }

    private function formatSMSMessage() {
        $priority = strtoupper($this->priorityLevel);
        return "[{$priority} ALERT] {$this->message}";
    }

    // Getters
    public function getAlertID() { return $this->alertID; }
    public function getMessage() { return $this->message; }
    public function getTimestamp() { return $this->timestamp; }
    public function getPriorityLevel() { return $this->priorityLevel; }
    public function getEmergencyID() { return $this->emergencyID; }
}

