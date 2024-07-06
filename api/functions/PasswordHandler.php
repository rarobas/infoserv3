<?php

class PasswordHandler
{
    public function hashPassword($str) {
       
        $options = [
            'cost' => 11,
        ];

        return password_hash($str, PASSWORD_BCRYPT, $options);
  
    }

    public function verifyPassword($str, $hash) {
        if(password_verify($str, $hash)) {
            return true;
        } else {
            return false;
        }
    }
}

?>