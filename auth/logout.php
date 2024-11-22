<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

session_start();
session_destroy();

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

header("Location: " . BASE_URL . "auth/login.php");
exit();