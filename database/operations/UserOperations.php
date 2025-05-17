<?php
class UserOperations {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createUser($data) {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO users (email, password, contact_num, location, name, age, userType) 
                    VALUES (:email, :password, :contact_num, :location, :name, :age, :userType)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $data['email'],
                'password' => password_hash($data['password'], PASSWORD_DEFAULT),
                'contact_num' => $data['contact_num'],
                'location' => $data['location'],
                'name' => $data['name'],
                'age' => $data['age'],
                'userType' => $data['userType']
            ]);

            $userID = $this->db->lastInsertId();

            // Create specific user type record
            if ($data['userType'] === 'responder') {
                $sql = "INSERT INTO responders (responderID, specialization, availabilityStatus) 
                        VALUES (:responderID, :specialization, :availabilityStatus)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'responderID' => $userID,
                    'specialization' => $data['specialization'],
                    'availabilityStatus' => true
                ]);
            } else {
                $sql = "INSERT INTO residents (residentID, medicalCondition) 
                        VALUES (:residentID, :medicalCondition)";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'residentID' => $userID,
                    'medicalCondition' => $data['medicalCondition'] ?? null
                ]);
            }

            $this->db->commit();
            return $userID;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updateUser($userID, $data) {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE users SET 
                    email = :email,
                    contact_num = :contact_num,
                    location = :location,
                    name = :name,
                    age = :age
                    WHERE userID = :userID";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'email' => $data['email'],
                'contact_num' => $data['contact_num'],
                'location' => $data['location'],
                'name' => $data['name'],
                'age' => $data['age'],
                'userID' => $userID
            ]);

            // Update specific user type data
            if ($data['userType'] === 'responder') {
                $sql = "UPDATE responders SET 
                        specialization = :specialization
                        WHERE responderID = :responderID";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'specialization' => $data['specialization'],
                    'responderID' => $userID
                ]);
            } else {
                $sql = "UPDATE residents SET 
                        medicalCondition = :medicalCondition
                        WHERE residentID = :residentID";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'medicalCondition' => $data['medicalCondition'],
                    'residentID' => $userID
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getUserById($userID) {
        $sql = "SELECT u.*, 
                CASE 
                    WHEN r.responderID IS NOT NULL THEN 'responder'
                    ELSE 'resident'
                END as userType,
                r.specialization,
                r.availabilityStatus,
                res.medicalCondition
                FROM users u
                LEFT JOIN responders r ON u.userID = r.responderID
                LEFT JOIN residents res ON u.userID = res.residentID
                WHERE u.userID = :userID";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['userID' => $userID]);
        return $stmt->fetch();
    }
}