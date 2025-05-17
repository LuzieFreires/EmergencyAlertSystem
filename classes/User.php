<?php
abstract class User {
    protected $userID;
    protected $email;
    protected $password;
    protected $contact_num;
    protected $location;
    protected $name;
    protected $age;
    protected $userType;

    public function __construct($email, $password, $contact_num, $location, $name, $age) {
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->contact_num = $contact_num;
        $this->location = $location;
        $this->name = $name;
        $this->age = $age;
    }

    abstract public function requestEmergency();
    abstract public function updateProfile($data);
    abstract public function viewAlertHistory();
}