<?php
// Use explicit constants from config
$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$dbname = defined('DB_NAME') ? DB_NAME : 'healthcare_db';
$username = defined('DB_USERNAME') ? DB_USERNAME : 'root';
$password = defined('DB_PASSWORD') ? DB_PASSWORD : '';

try {
    // Ensure error reporting is on
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Check PDO extension
    if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
        throw new Exception("PDO or PDO MySQL extension not loaded");
    }

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;

} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    error_log("Connection details - Host: $host, Database: $dbname, Username: $username");
    die("Database connection failed: " . $e->getMessage());
} catch (Exception $e) {
    error_log("General Connection Error: " . $e->getMessage());
    die("A general error occurred: " . $e->getMessage());
}