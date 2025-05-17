<?php
class SMSOperations {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function logSMS($data) {
        $sql = "INSERT INTO sms_logs 
                (recipientNumber, content, status, alertID, twilio_sid, error_message) 
                VALUES 
                (:recipientNumber, :content, :status, :alertID, :twilio_sid, :error_message)";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'recipientNumber' => $data['recipientNumber'],
            'content' => $data['content'],
            'status' => $data['status'],
            'alertID' => $data['alertID'],
            'twilio_sid' => $data['twilio_sid'] ?? null,
            'error_message' => $data['error_message'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    public function updateSMSStatus($smsID, $status, $twilioStatus = null) {
        $sql = "UPDATE sms_logs SET 
                status = :status,
                twilio_status = :twilio_status,
                updated_at = CURRENT_TIMESTAMP
                WHERE smsID = :smsID";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'status' => $status,
            'twilio_status' => $twilioStatus,
            'smsID' => $smsID
        ]);
    }

    public function getSMSByTwilioSID($twilioSid) {
        $sql = "SELECT * FROM sms_logs WHERE twilio_sid = :twilio_sid";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['twilio_sid' => $twilioSid]);
        return $stmt->fetch();
    }
}