<?php
namespace App\Models;

class User {
    private $id;
    private $email;
    private $password;

    public function __construct($email, $password) {
        $this->email = $email;
        $this->password = password_hash($password, PASSWORD_DEFAULT);
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPassword() {
        return $this->password;
    }
}