<?php
// logout.php
require_once '../config/config.php';
require_once '../includes/functions.php';

session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Destroy the session
session_destroy();

// Clear any other cookies if needed
setcookie('remember_me', '', time()-3600, '/');

// Redirect to login page
header("Location: " . BASE_URL . "auth/login.php");
exit();
?>