<?php
class Resident extends User {
    private $residentID;
    private $medicalCondition;

    public function __construct($email, $password, $contact_num, $location, $name, $age, $medicalCondition) {
        parent::__construct($email, $password, $contact_num, $location, $name, $age);
        $this->medicalCondition = $medicalCondition;
        $this->userType = 'resident';
    }

    public function register() {
        global $conn;

        $stmt = $conn->prepare("INSERT INTO users (email, password, contact_num, location, name, age, user_type) VALUES (?, ?, ?, ?, ?, ?, 'resident')");
        $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);
        $stmt->bind_param("sssssi", $this->email, $hashedPassword, $this->contact_num, $this->location, $this->name, $this->age);
        
        if ($stmt->execute()) {
            $this->residentID = $stmt->insert_id;
            $stmt->close();

            // Insert into resident table
            $stmt2 = $conn->prepare("INSERT INTO residents (user_id, medical_condition) VALUES (?, ?)");
            $stmt2->bind_param("is", $this->residentID, $this->medicalCondition);
            $stmt2->execute();
            $stmt2->close();

            return true;
        } else {
            return false;
        }
    }

    public function updateMedicalInfo($medicalInfo) {
        global $conn;
        $this->medicalCondition = $medicalInfo;

        $stmt = $conn->prepare("UPDATE residents SET medical_condition = ? WHERE user_id = ?");
        $stmt->bind_param("si", $this->medicalCondition, $this->residentID);
        return $stmt->execute();
    }

    public function viewResponderStatus() {
        global $conn;
        $result = $conn->query("SELECT name, contact_num, status FROM users WHERE user_type = 'responder'");

        $responders = [];
        while ($row = $result->fetch_assoc()) {
            $responders[] = $row;
        }
        return $responders;
    }

    // Send an emergency request
    public function requestEmergency() {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO emergencies (user_id, location, medical_condition, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("iss", $this->residentID, $this->location, $this->medicalCondition);
        return $stmt->execute();
    }

    // Update resident profile (name, contact, location)
    public function updateProfile($data) {
        global $conn;

        $this->name = $data['name'];
        $this->contact_num = $data['contact_num'];
        $this->location = $data['location'];
        $this->age = $data['age'];

        $stmt = $conn->prepare("UPDATE users SET name = ?, contact_num = ?, location = ?, age = ? WHERE id = ?");
        $stmt->bind_param("sssii", $this->name, $this->contact_num, $this->location, $this->age, $this->residentID);
        return $stmt->execute();
    }

    // View history of emergency alerts made by this resident
    public function viewAlertHistory() {
        global $conn;

        $stmt = $conn->prepare("SELECT * FROM emergencies WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $this->residentID);
        $stmt->execute();
        $result = $stmt->get_result();

        $history = [];
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
        return $history;
    }
}