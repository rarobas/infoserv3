<?php
require_once '../../functions/SessionManager.php';

// Creating an instance of the SessionManager
$sessionManager = new SessionManager();

// Starting the session
$sessionManager->startSession();

// Destroying the session
$status = $sessionManager->destroySession();

echo json_encode($status);