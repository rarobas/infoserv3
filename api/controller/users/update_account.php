<?php
require_once("../../config/headers.php");
require_once("../../config/connection.php");
require_once("../../functions/SessionManager.php");
require_once("../../functions/Authentication.php");
require_once("../../functions/UserManagement.php");
require_once("../../functions/PasswordHandler.php");

// Starting the session, simplified version
(new SessionManager())->startSession();

// Check authorization on each request
if (!Authentication::isAuthorized()) {
  Authentication::handleUnauthorized();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $user_id = isset($_POST["_user_id"]) ? $_POST["_user_id"] : "";
    $username = isset($_POST["_username"]) ? $_POST["_username"] : "";
    $password = isset($_POST["_password"]) ? $_POST["_password"] : "";
    $user_role_id = isset($_POST["_user_role_id"]) ? $_POST["_user_role_id"] : "";
    $first_name = isset($_POST["_first_name"]) ? $_POST["_first_name"] : "";
    $last_name = isset($_POST["_last_name"]) ? $_POST["_last_name"] : "";
    $user_barangay = isset($_POST["_user_barangay"]) ? $_POST["_user_barangay"] : "";
    $access_routes = isset($_POST["_access_routes"]) ? $_POST["_access_routes"] : "";
    // $status = isset($_POST["_status"]) ? $_POST["_status"] : "";

    // get db connection, simplified version
    $connection = (new DatabaseConnection())->getConnection();

    // hash password password, simplified version
    $hashedPassword = (new PasswordHandler())->hashPassword($password);

    $userManagement = new UserManagement($connection);
    $data = $userManagement->updateUser(
        $username, 
        $password,
        $hashedPassword, 
        $user_role_id, 
        $first_name, 
        $last_name, 
        $user_barangay, 
        $access_routes,
        // $status,
        $user_id
    );

  echo json_encode($data);
} else {
    // Handle error for unsupported request methods
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(array('error' => 'Method Not Allowed'));
    exit;
}

