<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Twilio\Rest\Client;

class SMS {
    private $smsID;
    private $recipientNumber;
    private $content;
    private $status;
    private $twilioClient;
    private static $instance = null;

    public function __construct($recipientNumber, $content) {
        $this->recipientNumber = $recipientNumber;
        $this->content = $content;
        $this->status = 'pending';
        
        // Initialize Twilio client
        $this->twilioClient = new Client(
            getenv('TWILIO_ACCOUNT_SID'),
            getenv('TWILIO_AUTH_TOKEN')
        );
    }

    public function sendSMS() {
        try {
            // Send message via Twilio
            $message = $this->twilioClient->messages->create(
                $this->recipientNumber,
                [
                    'from' => getenv('TWILIO_PHONE_NUMBER'),
                    'body' => $this->content,
                    'statusCallback' => 'https://yourdomain.com/group3/pages/handlers/sms_status_callback.php'
                ]
            );

            // Log SMS in database
            $smsOps = new SMSOperations();
            $data = [
                'recipientNumber' => $this->recipientNumber,
                'content' => $this->content,
                'status' => 'sent',
                'alertID' => null,
                'twilio_sid' => $message->sid
            ];
            
            $this->smsID = $smsOps->logSMS($data);
            $this->status = 'sent';
            
            return true;
        } catch (Exception $e) {
            error_log("SMS sending error: " . $e->getMessage());
            
            // Log failed attempt
            $smsOps = new SMSOperations();
            $data = [
                'recipientNumber' => $this->recipientNumber,
                'content' => $this->content,
                'status' => 'failed',
                'alertID' => null,
                'error_message' => $e->getMessage()
            ];
            $smsOps->logSMS($data);
            
            return false;
        }
    }

    public function checkDeliveryStatus() {
        if (!$this->smsID) {
            return 'unknown';
        }
        return $this->status;
    }

    // Getters
    public function getSMSID() { return $this->smsID; }
    public function getRecipientNumber() { return $this->recipientNumber; }
    public function getContent() { return $this->content; }
    public function getStatus() { return $this->status; }
}