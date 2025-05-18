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
            // Validate required fields
            $requiredFields = ['email', 'contact_num', 'location', 'name', 'age', 'userType'];
            foreach ($requiredFields as $field) {
                if (!isset($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            $this->db->beginTransaction();

            // First check if user exists
            $checkSql = "SELECT userID FROM users WHERE userID = ?";
            $checkStmt = $this->db->prepare($checkSql);
            $checkStmt->execute([$userID]);
            if (!$checkStmt->fetch()) {
                throw new Exception('User not found');
            }

            $sql = "UPDATE users SET 
                    email = :email,
                    contact_num = :contact_num,
                    location = :location,
                    name = :name,
                    age = :age,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE userID = :userID";
            
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                'email' => $data['email'],
                'contact_num' => $data['contact_num'],
                'location' => $data['location'],
                'name' => $data['name'],
                'age' => $data['age'],
                'userID' => $userID
            ]);

            if (!$result) {
                throw new Exception('Failed to update user data');
            }

            // Update specific user type data
            if ($data['userType'] === 'responder') {
                if (!isset($data['specialization'])) {
                    throw new Exception('Specialization is required for responders');
                }

                $sql = "UPDATE responders SET 
                        specialization = :specialization,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE responderID = :responderID";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    'specialization' => $data['specialization'],
                    'responderID' => $userID
                ]);

                if (!$result) {
                    throw new Exception('Failed to update responder data');
                }
            } else {
                $sql = "UPDATE residents SET 
                        medicalCondition = :medicalCondition,
                        updated_at = CURRENT_TIMESTAMP
                        WHERE residentID = :residentID";
                $stmt = $this->db->prepare($sql);
                $result = $stmt->execute([
                    'medicalCondition' => $data['medicalCondition'] ?? null,
                    'residentID' => $userID
                ]);

                if (!$result) {
                    throw new Exception('Failed to update resident data');
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Update user error: " . $e->getMessage());
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

    public function getAvailableResponders() {
        $sql = "SELECT u.userID, u.contact_num 
                FROM users u 
                JOIN responders r ON u.userID = r.userID 
                WHERE r.availabilityStatus = 'available'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updatePassword($userID, $hashedPassword) {
        try {
            $sql = "UPDATE users SET 
                    password = :password,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE userID = :userID";
            
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                'password' => $hashedPassword,
                'userID' => $userID
            ]);
        } catch (Exception $e) {
            error_log("Error updating password: " . $e->getMessage());
            throw $e;
        }
    }
}