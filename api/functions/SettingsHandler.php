<?php
require_once("PasswordHandler.php");
require_once(__DIR__ ."/../constants/Actions.php");
class SettingsHandler extends PasswordHandler {
    private $conn;

    public function __construct($conn){
        $this->conn = $conn;
    }

    private function errorMessage(PDOException $e, $query, $func){
        // Log the error with detailed information
        $errorDetails = [
          'function' => $func,
          'query' => $query,
          'error_message' => $e->getMessage(),
          'error_code' => $e->getCode(),
          'timestamp' => date('Y-m-d H:i:s'),
        ];
        error_log('Error in ' . $func . ': ' . json_encode($errorDetails, JSON_PRETTY_PRINT));
    
        // Return a user-friendly error response in production
        if (PRODUCTION_MODE) {
          echo json_encode( ['status' => false, 'message' => 'There was an error processing your request. Please try again later.']);
          exit;
        } else {
          // In a development environment, you can expose more detailed error information
          echo json_encode([
            'status' => false,
            'message' => 'Development Error: ' . $e->getMessage(),
            'error_details' => $errorDetails,
          ]);
          exit;
        }
    }

    private function getCurrentPassword($id, $query){

        try {
      
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $response = $statement->fetch(PDO::FETCH_OBJ);
            return $response;
            
        } catch (PDOException $e) {
            return $this->errorMessage($e, $query, __FUNCTION__);
        }

    }

    private function updatePassword($id, $query, $hash_password){

        try {
      
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':hash_password', $hash_password, PDO::PARAM_STR);
            $statement->execute();
            return ['status' => true, 'message' => 'Password updated successfully!'];
            
        } catch (PDOException $e) {
            return $this->errorMessage($e, $query, __FUNCTION__);
        }

    }

    private function addLogs($id, $action, $action_description){

        $query = "INSERT INTO tbl_client_action_logs (
            `action`,
            `action_description`,
            `company_id`,
            `date_added`
        ) 
            VALUES (
            :action, 
            :action_description, 
            :company_id, 
            NOW()
            )
        ";

        try {
      
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':company_id', $id, PDO::PARAM_INT);
            $statement->bindParam(':action', $action, PDO::PARAM_STR);
            $statement->bindParam(':action_description', $action_description, PDO::PARAM_STR);
            $statement->execute();
            return ['status' => true, 'message' => 'Log inserted!'];
            
        } catch (PDOException $e) {
            return $this->errorMessage($e, $query, __FUNCTION__);
        }
    }

    public function updatePasswordAdmin($id, $new_password, $current_password){

        $get_password_q = "SELECT `password` FROM tbl_user WHERE `user_id` = :id";

        $get_current_password = $this->getCurrentPassword($id, $get_password_q)->password;

        if(!$this->verifyPassword($current_password, $get_current_password)){
            echo json_encode(['status' => false, 'message' => "Sorry, the entered old password does not match our records. Please double-check and try again."]);
            exit;
        }

        $hash_password = $this->hashPassword($new_password);

        $update_password_q = "UPDATE tbl_user SET `password` = :hash_password, `date_updated` = NOW() WHERE `user_id` = :id";

        return $this->updatePassword($id, $update_password_q, $hash_password);

    }
    
    public function updatePasswordClient($id, $new_password, $current_password){

        $get_password_q = "SELECT `password` FROM tbl_company WHERE `company_id` = :id";

        $get_current_password = $this->getCurrentPassword($id, $get_password_q)->password;

        if(!$this->verifyPassword($current_password, $get_current_password)){
            echo json_encode(['status' => false, 'message' => "Sorry, the entered old password does not match our records. Please double-check and try again."]);
            exit;
        }

        $hash_password = $this->hashPassword($new_password);

        $update_password_q = "UPDATE tbl_company SET `password` = :hash_password WHERE `company_id` = :id";

        $update_password_result = $this->updatePassword($id, $update_password_q, $hash_password);

        if($update_password_result['status']) {
            $action_description = 'Company update the password.';
            $this->addLogs($id, UPDATE_CLIENT_PASSWORD, $action_description);
            return $update_password_result;
        }

    }

    public function checkEmailAddress($company_eadd, $id) {
        $query = "SELECT COUNT(company_eadd) FROM `tbl_company` WHERE company_eadd = TRIM(:company_eadd) AND `company_id` != :id LIMIT 1"; 
        try{
          $statement = $this->conn->prepare($query);
          $statement->bindParam(':company_eadd', $company_eadd, PDO::PARAM_STR);
          $statement->bindParam(':id', $id, PDO::PARAM_INT);
          $statement->execute();
          return $statement->fetchColumn();
        } catch (PDOException $e) {
          return $this->errorMessage($e, $query, __FUNCTION__);
        }
    
    }

    private function updateEmail($id, $query, $new_email){
        try {
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->bindParam(':new_email', $new_email, PDO::PARAM_STR);
            $statement->execute();
            return ['status' => true, 'message' => 'Email address updated successfully!'];
        } catch (PDOException $e) {
            return $this->errorMessage($e, $query, __FUNCTION__);
        }
    }
    
    private function getPreviousEmail($id, $query){
        try {
            $statement = $this->conn->prepare($query);
            $statement->bindParam(':id', $id, PDO::PARAM_INT);
            $statement->execute();
            $response = $statement->fetch(PDO::FETCH_OBJ);
            return $response;
        } catch (PDOException $e) {
            return $this->errorMessage($e, $query, __FUNCTION__);
        }
    }
   
    public function updateEmailClient($id, $new_email){

        $select_previous_email_q = "SELECT `company_eadd` FROM tbl_company WHERE `company_id` = :id";
        $update_email_q = "UPDATE tbl_company SET `company_eadd` = TRIM(:new_email) WHERE `company_id` = :id";

        $old_email_result = $this->getPreviousEmail($id, $select_previous_email_q);
        $update_email_result = $this->updateEmail($id, $update_email_q, $new_email);

        if($update_email_result['status']) {
            $action_description = "Company update the email. From previous email: $old_email_result->company_eadd to new email: $new_email";
            $this->addLogs($id, UPDATE_CLIENT_EMAIL, $action_description);
            return $update_email_result;
        }

    }


}