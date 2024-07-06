<?php
include_once("../../config/headers.php");
include_once("../../config/connection.php");
include_once("../../functions/Authentication.php");
include_once("../../functions/PasswordHandler.php");
include_once("../../functions/SessionManager.php");
include_once("../../functions/ClientUtilities.php");


if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  $username = isset($_GET["username"]) ? $_GET["username"] : "";
  $password = isset($_GET["password"]) ? $_GET["password"] : "";
  $login_type = isset($_GET["login_type"]) ? $_GET["login_type"] : "";
  $user_type = $login_type === 'admin' ? 2 : 1 ;

  // Set database connection
  $connection = (new DatabaseConnection())->getConnection();

  // Creating an instance of a class named Authentication
  $auth = new Authentication($connection);

  $ip_address = (new ClientUtilities($connection))->getIpAddress();

  function validate($auth, $data, $password, $username, $ip_address, $user_type) {
    if (empty($data) || !(new PasswordHandler())->verifyPassword($password, $data[0]->password)) {
        //if wrong credentials -> save the logs
        $auth->addLoginLogs($username, $ip_address, 2, $user_type);
        $auth->invalidCredentialsMessage();
    } else {
        //if correct credentials -> save the logs
        $auth->addLoginLogs($username, $ip_address, 1, $user_type);
    }
  }

if ($login_type === 'admin') {
    $data = $auth->getUserDetails($username);
}
  validate($auth, $data, $password, $username, $ip_address, $user_type);

  // # SET SESSION
  // Creating an instance of the SessionManager. Starting the session
  $sessionManager = new SessionManager();
  $sessionManager->startSession();

  // TODO: change this data depends on the column in the user table
  // Setting multiple session variables.
  if($login_type == 'admin'){

    $dataToSet = [
      'username' => $data[0]->username,
      'user_role_id' => $data[0]->user_role_id,
      'full_name' => $data[0]->full_name,
      'login_type' => $login_type,
      'user_id' => $data[0]->user_id,
      'user_barangay' => $data[0]->user_barangay,
      //add whatever needed to store in localstorage
    ];
  } else {
    //TODO : if there whould be a barangay client account-> set session variable here
    $dataToSet = [
      'username' => $data[0]->username,
      'company_name' => $data[0]->company_name,
      'login_type' => $login_type,
      //add whatever needed to store in localstorage 
    ];

  }

  // Set the session variables and get the values that were set
  $setValues = $sessionManager->setSessionVariables($dataToSet);


  // success login
  $result = array(
    "data" => $dataToSet,
    "status" => true,
  );

  echo json_encode($result);

} else {
  // Handle error for unsupported request methods
  header('HTTP/1.1 405 Method Not Allowed');
  echo json_encode(array('error' => 'Method Not Allowed'));
  exit;
}

?>