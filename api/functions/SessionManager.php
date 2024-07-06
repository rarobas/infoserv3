<?php

class SessionManager {

    public function startSession() {
        session_start();
    }
    //for login
    public function setSessionVariables(array $data) {
        $setValues = [];
    
        foreach ($data as $key => $value) {
            $_SESSION[$key] = $value;
            $setValues[$key] = $value;
        }
    
        return $setValues;
    }
    //for authentication
    public function getSessionVariables(array $keys) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }
        return $result;
    }
    //for logout
    public function destroySession() {
            // Unset all session variables
            session_unset();
    
            // Destroy the session
            $destroyed = session_destroy();
    
            // Unset the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            // Return true if the session is successfully destroyed, otherwise false
            return $destroyed;

    }

}

?>