<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check config constants
echo "DB_HOST: " . (defined('DB_HOST') ? DB_HOST : 'NOT DEFINED') . "<br>";
echo "DB_NAME: " . (defined('DB_NAME') ? DB_NAME : 'NOT DEFINED') . "<br>";
echo "DB_USERNAME: " . (defined('DB_USERNAME') ? DB_USERNAME : 'NOT DEFINED') . "<br>";

// Check PHP extensions
echo "PDO Extension Loaded: " . (extension_loaded('pdo') ? 'Yes' : 'No') . "<br>";
echo "PDO MySQL Extension Loaded: " . (extension_loaded('pdo_mysql') ? 'Yes' : 'No') . "<br>";

// Attempt connection manually
try {
    $host = 'localhost';
    $dbname = 'healthcare_db';
    $username = 'root';
    $password = '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    echo "Database Connection Successful!<br>";
    
    // Test query
    $stmt = $pdo->query("SELECT 1");
    echo "Test Query Successful!<br>";

} catch (PDOException $e) {
    echo "Connection Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}
?>