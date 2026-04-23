<?php
namespace App\Config;

use PDO;

class Database {
    private $host;
    private $db;
    private $user;
    private $pass;
    private $port;

    public function __construct() {
        $this->host = $_ENV['DB_HOST'] ;
        $this->db = $_ENV['DB_NAME'];
        $this->user = $_ENV['DB_USER'] ;
        $this->pass = $_ENV['DB_PASS'] ;
        $this->port = $_ENV['DB_PORT'] ;
    }

    public function connect() {
        $this->conn = new PDO(
            "pgsql:host={$this->host};port={$this->port};dbname={$this->db}",
            $this->user,
            $this->pass
        );

        return $this->conn;
    }
}