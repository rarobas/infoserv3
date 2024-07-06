<?php

class Authentication {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  public function getUserDetails($username) {
    $query = "SELECT `username`, `password`, tbl_user.`user_role_id`, CONCAT_WS(' ', `first_name`, `last_name`) as `full_name`, `access_routes`, `user_barangay`, `user_id`
              FROM tbl_user 
              INNER JOIN tbl_user_role USING (`user_role_id`)
              WHERE `username` = :username AND `user_status` = 0
              LIMIT 1
              ";
    
    try {
      $statement = $this->conn->prepare($query);
      $statement->bindParam(':username', $username);
      $statement->execute();
      $response = $statement->fetchAll(PDO::FETCH_OBJ);
      return $response;

    } catch (PDOException $e) {
      // Log the error
      error_log('Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
  
      // Return a more user-friendly error response
      return ['status' => false, 'message' => 'There was an error processing your request. Please try again later.'];
    }
  }

  public function invalidCredentialsMessage() {

    $result = array(
      "status" => false,
      "message" => "You have entered an invalid username or password.",
    );

    echo json_encode($result);
    exit;

  }

  public static function isAuthorized() {
      // TODO: change this data depends on the session name
      // Check if a session variable indicating login is set
      return isset($_SESSION['username']);
  }

  public static function handleUnauthorized() {
      // header('Content-Type: application/json');
      http_response_code(401);
      echo json_encode(['error' => 'Unauthorized']);
      exit;
  }

  public function addLoginLogs($username, $ip_address, $login_status, $user_type) { 

    $query = "INSERT INTO tbl_login_logs (
        `username`,
        `ip_address`,
        `login_status`, -- 1 = success, 2 = failure
        `user_type`, -- 1 = client, 2 = admin users
        `created_at`
    ) 
        VALUES (
        :username, 
        :ip_address, 
        :login_status, 
        :user_type, 
        NOW()
        )
    ";
    
    try {
      $statement = $this->conn->prepare($query);
      $statement->bindParam(':username', $username, PDO::PARAM_STR);
      $statement->bindParam(':ip_address', $ip_address, PDO::PARAM_STR);
      $statement->bindParam(':login_status', $login_status, PDO::PARAM_INT);
      $statement->bindParam(':user_type', $user_type, PDO::PARAM_INT);
      $statement->execute();

      return ['status' => true, 'message' => 'Login logs saved.'];

    } catch (PDOException $e) {
        // Log the error
        error_log('Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
  
        // Return a more user-friendly error response
        return ['status' => false, 'message' => 'There was an error processing your request. Please try again later.', 'error' => 'Login logs.'];
    }
}

}



?>
