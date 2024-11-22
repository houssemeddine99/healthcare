<?php
// config/config.php

// Only define constants if they haven't been defined yet
if (!defined('BASE_URL')) {
    // Site Configuration
    define('BASE_URL', 'http://localhost/healthcare/');
    define('SITE_NAME', 'Healthcare Management System');
    
    // Database Configuration
    define('DB_HOST', 'localhost');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_NAME', 'healthcare_db');
    
    // Session Configuration
    define('SESSION_TIME', 1800);
    define('ENVIRONMENT', 'development');
    
    // Error Reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}