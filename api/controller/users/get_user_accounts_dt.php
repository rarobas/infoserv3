<?php
require_once("../../config/headers.php");
require_once("../../config/connection.php");
require_once("../../functions/SessionManager.php");
require_once("../../functions/Authentication.php");
require_once("../../functions/UserManagement.php");

// Starting the session, simplified version
(new SessionManager())->startSession();

// Check authorization on each request
if (!Authentication::isAuthorized()) {
  Authentication::handleUnauthorized();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  $username = isset($_GET["_username"]) ? $_GET["_username"] : "";
  $limit_offset = isset($_GET["_limit_offset"]) ? $_GET["_limit_offset"] : "";
  $limit = isset($_GET["_limit"]) ? $_GET["_limit"] : "";

  // get db connection, simplified version
  $connection = (new DatabaseConnection())->getConnection();

  $userManagement = new UserManagement($connection);
  $data = $userManagement->getUserDt(
    $limit_offset,
    $username,
    $limit
  );
  
  $count = $userManagement->getUserCount($username);

  $data = array(
    "data" => $data,
    "_total_count" => $count
  );

  echo json_encode($data);
} else {
  // Handle error for unsupported request methods
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(array('error' => 'Method Not Allowed'));
  exit;
}

