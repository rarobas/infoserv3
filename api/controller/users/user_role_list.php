<?php

require_once("../../config/headers.php");
require_once("../../config/connection.php");
require_once("../../functions/UserManagement.php");


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $connection = (new DatabaseConnection())->getConnection();

    $data = (new UserManagement($connection))->getUserRole();
    
    // Echo the result as JSON
    echo json_encode($data);
    exit;
  
} else {
    // Handle error for unsupported request methods
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(array('error' => 'Method Not Allowed'));
    exit;
}