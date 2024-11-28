<?php
// config/config.php

// Define constants only once
if (!defined('CONFIG_LOADED')) {
    define('CONFIG_LOADED', true);

    // Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'healthcare_db');
    define('DB_USERNAME', 'root');
    define('DB_PASSWORD', '');
    // Site Configuration
    define('SITE_NAME', 'Healthcare System');
    define('BASE_URL', 'http://localhost/healthcare/');

    // Session Configuration
    define('SESSION_TIME', 1800); // 30 minutes in seconds
    
    // Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Modify session handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}