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

  $username = isset($_POST["_username"]) ? $_POST["_username"] : "";
  $password = isset($_POST["_password"]) ? $_POST["_password"] : "";
  $user_role_id = isset($_POST["_user_role_id"]) ? $_POST["_user_role_id"] : "";
  $first_name = isset($_POST["_first_name"]) ? $_POST["_first_name"] : "";
  $last_name = isset($_POST["_last_name"]) ? $_POST["_last_name"] : "";
  $user_province = isset($_POST["_user_province"]) ? $_POST["_user_province"] : "";
  $access_routes = isset($_POST["_access_routes"]) ? $_POST["_access_routes"] : "";

  // get db connection, simplified version
  $connection = (new DatabaseConnection())->getConnection();


  // hash password password, simplified version
  $hashedPassword = (new PasswordHandler())->hashPassword($password);

  $userManagement = new UserManagement($connection);
  $data = $userManagement->addUser(
    $username, 
    $hashedPassword, 
    $user_role_id, 
    $first_name, 
    $last_name, 
    $user_province, 
    $access_routes
  );

  echo json_encode($data);
} else {
  // Handle error for unsupported request methods
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(array('error' => 'Method Not Allowed'));
  exit;
}

