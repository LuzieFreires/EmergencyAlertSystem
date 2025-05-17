<?php
require_once '../../config.php';

// Verify the request is from Twilio
function validateTwilioRequest() {
    $twilioSignature = $_SERVER['HTTP_X_TWILIO_SIGNATURE'] ?? '';
    $url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    $validator = new Twilio\Security\RequestValidator(getenv('TWILIO_AUTH_TOKEN'));
    
    return $validator->validate($twilioSignature, $url, $_POST);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && validateTwilioRequest()) {
    $messageSid = $_POST['MessageSid'];
    $messageStatus = $_POST['MessageStatus'];
    
    $smsOps = new SMSOperations();
    $sms = $smsOps->getSMSByTwilioSID($messageSid);
    
    if ($sms) {
        // Map Twilio status to our status
        $status = match($messageStatus) {
            'delivered' => 'delivered',
            'failed', 'undelivered' => 'failed',
            default => 'sent'
        };
        
        $smsOps->updateSMSStatus($sms['smsID'], $status, $messageStatus);
    }
    
    http_response_code(200);
    echo 'OK';
    exit;
}

http_response_code(400);
echo 'Invalid request';
exit;