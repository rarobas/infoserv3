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
  $new_email = isset($_POST["_new_email"]) ? $_POST["_new_email"] : "";
  $id = isset($_SESSION["company_id"]) ? $_SESSION["company_id"] : "";

  $connection = (new DatabaseConnection())->getConnection();

  $settingsHandler = new SettingsHandler($connection);

  if($settingsHandler->checkEmailAddress($new_email, $id) >= 1) {
    echo json_encode(['status' => false, 'message' => "Email address already exist."]);
    exit;
  };

  $result = $settingsHandler->updateEmailClient($id, $new_email);
  
  echo json_encode($result);

} else {
  // Handle error for unsupported request methods
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(array('error' => 'Method Not Allowed'));
  exit;
}

