<?php
require_once("../../config/headers.php");
require_once("../../config/connection.php");
require_once("../../functions/SessionManager.php");
require_once("../../functions/Authentication.php");
require_once("../../functions/SettingsHandler.php");

// Starting the session, simplified version
(new SessionManager())->startSession();

// Check authorization on each request
if (!Authentication::isAuthorized()) {
  Authentication::handleUnauthorized();
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $current_password = isset($_POST["_current_password"]) ? $_POST["_current_password"] : "";
  $new_password = isset($_POST["_new_password"]) ? $_POST["_new_password"] : "";

  $login_type = isset($_SESSION["login_type"]) ? $_SESSION["login_type"] : "";
  $id = $login_type == 'admin' ? $_SESSION["user_id"] : $_SESSION["company_id"];

  // get db connection, simplified version
  $connection = (new DatabaseConnection())->getConnection();
  $settingsHandler = new SettingsHandler($connection);

  if($login_type == 'admin') {
    $result = $settingsHandler->updatePasswordAdmin($id, $new_password, $current_password);
  } elseif($login_type == 'client') {
    $result = $settingsHandler->updatePasswordClient($id, $new_password, $current_password);
  } else {
    echo json_encode(['status' => false, 'message' => "An error occurred. Please contact administrator"]);
    exit;
  }

  echo json_encode($result);

} else {
  // Handle error for unsupported request methods
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(array('error' => 'Method Not Allowed'));
  exit;
}

