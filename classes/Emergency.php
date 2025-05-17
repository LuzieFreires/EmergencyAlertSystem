<?php
class Emergency {
    private $adminID;
    private $residentID;
    private $status;
    private $responseTime;
    private $location;
    private $type;
    private $severityLevel;

    public function __construct($residentID, $location, $type, $severityLevel) {
        $this->residentID = $residentID;
        $this->location = $location;
        $this->type = $type;
        $this->severityLevel = $severityLevel;
        $this->status = 'pending';
        $this->responseTime = date('Y-m-d H:i:s');
    }

    public function assignResponder($responderID) {
        // Implementation
    }

    public function updateStatus($newStatus) {
        $this->status = $newStatus;
    }

    public function generateReport() {
        // Implementation
    }
}