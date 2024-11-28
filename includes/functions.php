<?php
// includes/functions.php

// Authentication Functions
function login_user($id_number, $password, $user_type) {
    global $conn;
    
    $table = ($user_type === 'staff') ? 'medical_staff' : 'patients';
    
    try {
        $stmt = $conn->prepare("SELECT * FROM $table WHERE id_number = ?");
        $stmt->execute([$id_number]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            init_user_session($user, $user_type);
            return true;
        }
        return false;
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

function init_user_session($user, $user_type) {
    $_SESSION['user_id'] = $user['id_number'];
    $_SESSION['user_type'] = $user_type;
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['last_activity'] = time();
}

function logout_user() {
    session_unset();
    session_destroy();
    session_start();
    set_message('You have been logged out successfully.');
}


// Session Management Functions
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function check_session_timeout() {
    if (!isset($_SESSION['last_activity'])) {
        logout_user();
        return false;
    }

    if (time() - $_SESSION['last_activity'] > SESSION_TIME) {
        logout_user();
        set_message('Your session has expired. Please log in again.', 'warning');
        return false;
    }
    
    $_SESSION['last_activity'] = time();
    return true;
}

// Authorization Functions
function require_login() {
    if (!is_logged_in() || !check_session_timeout()) {
        $redirect_url = BASE_URL . "auth/login.php";
        if (!empty($_SERVER['REQUEST_URI'])) {
            $redirect_url .= "?redirect=" . urlencode($_SERVER['REQUEST_URI']);
        }
        header("Location: " . $redirect_url);
        exit();
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['user_type'] !== 'staff') {
        set_message('Access denied. Staff only area.', 'error');
        header("Location: " . BASE_URL . "patient/dashboard.php");
        exit();
    }
}

function require_patient() {
    require_login();
    if ($_SESSION['user_type'] !== 'patient') {
        set_message('Access denied. Patient only area.', 'error');
        header("Location: " . BASE_URL . "admin/dashboard.php");
        exit();
    }
}

// Database Query Functions
function get_patient_info($id_number) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT * FROM patients WHERE id_number = ?");
        $stmt->execute([$id_number]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching patient info: " . $e->getMessage());
        return false;
    }
}

function get_patient_medications($id_number) {
    global $conn;
    try {
        $stmt = $conn->prepare("
            SELECT m.*, ms.name as prescribed_by 
            FROM medications m 
            JOIN medical_staff ms ON m.prescribed_by = ms.id_number 
            WHERE m.patient_id = ?
            ORDER BY m.prescribed_date DESC
        ");
        $stmt->execute([$id_number]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error fetching medications: " . $e->getMessage());
        return [];
    }
}

// Utility Functions
function format_date($date) {
    return date("F j, Y", strtotime($date));
}

// Flash Message Functions
function set_message($message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
}

function display_message() {
    if (isset($_SESSION['message'])) {
        $type = htmlspecialchars($_SESSION['message_type']);
        $message = htmlspecialchars($_SESSION['message']);
        unset($_SESSION['message'], $_SESSION['message_type']);
        return "<div class='alert alert-$type' role='alert'>$message</div>";
    }
    return '';
}


// Security Functions
function sanitize_output($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($path, $with_message = null, $type = 'success') {
    if ($with_message) {
        set_message($with_message, $type);
    }
    header("Location: " . BASE_URL . $path);
    exit();
}
function sanitize_input($data) {
    return htmlspecialchars(trim($data));
}

function validate_id_number($id_number) {
    // Modify this validation according to your ID number format requirements
    return preg_match('/^[0-9A-Z-]{1,20}$/', $id_number);
}

function user_exists($value, $table, $column) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM $table WHERE $column = ?");
        $stmt->execute([$value]);
        return $stmt->fetchColumn() > 0;
    } catch(PDOException $e) {
        error_log("Error checking user existence: " . $e->getMessage());
        return false;
    }
}
