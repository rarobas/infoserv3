<?php

require_once(__DIR__ . "/../constants/Host.php");
    
// Set the default timezone to Philippine time
date_default_timezone_set('Asia/Manila');

class DatabaseConnection {
    private $host = HOST;
    private $user = USER;
    private $password = PASSWORD;
    private $database = DATABASE;
    private $connection;

    public function __construct() {
        try {
            $this->connection = new PDO("mysql:host=$this->host;dbname=$this->database", $this->user, $this->password);
            $this->connection->exec("SET time_zone='+08:00';"); // SET TIMEZONE
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Handle connection errors if needed
            die("Connection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

}

// to check the database connection
// try {
//     $databaseConnection = new DatabaseConnection();
//     $connection = $databaseConnection->getConnection();
//     // Now $connection can be used for database operations
//     echo "Connection successful!";
// } catch (PDOException $e) {
//     // Handle the connection error
//     echo "Connection failed: " . $e->getMessage();
// }
