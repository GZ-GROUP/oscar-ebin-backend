<?php
namespace App\Models;

class User {
    private $id;
    private $name;
    private $email;
    private $password;
    private $visits;

    public function __construct($name, $email, $password) {
        $this->name = $name;
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
        $this->visits = 0;
    }

    public function getName() {
        return $this->name;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }

    public function getVisits() {
        return $this->visits;
    }
}