<?php
include_once("../../config/headers.php");
include_once("../../config/connection.php");
include_once("../../functions/SessionManager.php");
include_once("../../functions/Authentication.php");

// Creating an instance of the SessionManager
$sessionManager = new SessionManager();

// Starting the session
$sessionManager->startSession();

// Check authorization on each request
if (!Authentication::isAuthorized()) {
  Authentication::handleUnauthorized();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  // get username value thru session
  $username = $_SESSION['username'] != "" ? $_SESSION['username'] : "" ;
  $login_type = $_SESSION['login_type'] != "" ? $_SESSION['login_type'] : "" ;

  // get db connection
  $dbConn = new DatabaseConnection();
  $connection = $dbConn->getConnection();

  // get user details
  $auth = new Authentication($connection);

  $data = $auth->getUserDetails($username);
 

  // Use explode to split the string into an array
  $access_routes = explode(',', $data[0]->access_routes); // e.q. from dashboard,user to ["dashboard", "user"]

  echo json_encode($access_routes);
 
} else {
  // Handle error for unsupported request methods
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(array('error' => 'Method Not Allowed'));
  exit;
}
