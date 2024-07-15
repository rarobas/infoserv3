<?php

class UserManagement {
  private $conn;

  public function __construct($conn) {
    $this->conn = $conn;
  }

  public function addUser($username, $password, $user_role_id, $first_name, $last_name, $user_barangay, $access_routes) {
    $query = "INSERT INTO `tbl_user` (`username`, `password`, `user_role_id`, `first_name`, `last_name`, `user_barangay`, `access_routes`, `date_created`) 
                              VALUES (:username, :user_password, :user_role_id, :first_name, :last_name, :user_barangay, :access_routes, NOW())";
    
    try {
      $statement = $this->conn->prepare($query);
      $arr = array(
        ":username" => $username,
        ":user_password" => $password,
        ":user_role_id" => $user_role_id,
        ":first_name" => $first_name,
        ":last_name" => $last_name,
        ":user_barangay" => $user_barangay,
        ":access_routes" => $access_routes,
      );

      if ($statement->execute($arr)) {
        $data = array(
          "message" => "New user has been added successfully.",
          "status" => true,
        );

        return $data;
      } else {
        $data = array(
          "message" => "Error: Please try again",
          "status" => false,
        );

        return $data;
      }

    } catch (PDOException $e) {
      // Log the error
      error_log('Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
  
      // Return a more user-friendly error response
      return ['status' => false, 'message' => 'There was an error processing your request. Please try again later.'];
    }
  }


  public function updateUser($username, $password, $hashedPassword, $user_role_id, $first_name, $last_name, $user_barangay, $access_routes, $user_id) {
    $query = "UPDATE tbl_user SET `username` = :username, 
                                  `password` = if(TRIM(:u_password) IS NULL OR TRIM(:u_password) = '', `password`, :user_password), -- retain if the value of password in update is empty
                                  `user_role_id` = :user_role_id, 
                                  `first_name` = :first_name, 
                                  `last_name` = :last_name, 
                                  `user_barangay` = :user_barangay, 
                                  `access_routes` = :access_routes
                                  -- `status` = :account_status
              WHERE `user_id` = :user_id LIMIT 1";
    
    try {
      $statement = $this->conn->prepare($query);
      $arr = array(
          ":user_id" => $user_id,
          ":username" => $username,
          ":user_password" => $hashedPassword,
          ":u_password" => $password,
          ":user_role_id" => $user_role_id,
          ":first_name" => $first_name,
          ":last_name" => $last_name,
          ":user_barangay" => $user_barangay,
          ":access_routes" => $access_routes
          // ":account_status" => $status
      );

      if ($statement->execute($arr)) {
        $data = array(
          "message" => "User has been updated successfully.",
          "status" => true,
        );

        return $data;
      } else {
        $data = array(
          "message" => "Error: Please try again",
          "status" => false,
        );

        return $data;
      }

    } catch (PDOException $e) {
      // Log the error
      error_log('Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
  
      // Return a more user-friendly error response
      return ['status' => false, 'message' => 'There was an error processing your request. Please try again later.'];
    }
  }

  public function getUserDt($limit_offset, $username, $limit) {
    $query = "SELECT `user_id`, `username`, `password`, `first_name`, `last_name`, CONCAT_WS(' ', `first_name`, `last_name`) as `full_name`, `tbl_user`.`user_role_id`, `role_desc`, `user_barangay`,  `access_routes`, `user_status`
              FROM `tbl_user`
              INNER JOIN `tbl_user_role` USING (`user_role_id`)
              WHERE IF(:username = '', 1 = 1, (username LIKE TRIM(:username_like) OR first_name LIKE TRIM(:username_like) OR last_name LIKE TRIM(:username_like) OR role_desc LIKE TRIM(:username_like)))
              ORDER BY user_id DESC
              LIMIT :limit OFFSET :limit_offset
              ";
    try {
      $username_like = "%".TRIM($username)."%";
      $statement = $this->conn->prepare($query);

      $statement->bindParam(':limit_offset', $limit_offset, PDO::PARAM_INT); 
      $statement->bindParam(':limit', $limit, PDO::PARAM_INT); 
      $statement->bindParam(':username', $username);
      $statement->bindParam(':username_like', $username_like); // for like

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

  public function getUserCount($username) {
      $query = "SELECT COUNT(*) FROM `tbl_user` 
                INNER JOIN `tbl_user_role` USING (`user_role_id`)
                WHERE IF(:username = '', 1 = 1, (username LIKE :username_like OR first_name LIKE :username_like OR last_name LIKE :username_like OR role_desc LIKE :username_like)) AND `user_role_id` <> 1
                "; 
      try{
        $username_like = "%".TRIM($username)."%";
        $statement = $this->conn->prepare($query);

        $statement->bindParam(':username', $username);
        $statement->bindParam(':username_like', $username_like); // for like

        $statement->execute();
        $response = $statement->fetchColumn(); // to return exact number
        return $response;

      } catch (PDOException $e) {
        // Log the error
        error_log('Error in ' . __FUNCTION__ . ': ' . $e->getMessage());
    
        // Return a more user-friendly error response
        return ['status' => false, 'message' => 'There was an error processing your request. Please try again later.'];
      }

  }

  public function getUserRole() {
    $query = "SELECT *
              FROM tbl_user_role
              WHERE `user_role_id` <> 1";
    try {
      $statement = $this->conn->prepare($query);
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

}
