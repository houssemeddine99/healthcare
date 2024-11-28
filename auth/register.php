<?php
// Explicitly destroy any existing session
if (session_status() !== PHP_SESSION_NONE) {
    session_destroy();
}

// Start a fresh session
session_start();

require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

// Explicitly check database connection
try {
    $stmt = $pdo->query("SELECT DATABASE()");
    $currentDb = $stmt->fetchColumn();
    error_log("Connected to database: " . $currentDb);
} catch (Exception $e) {
    error_log("Database connection test failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Verify database connection
if (!isset($conn) || $conn === null) {
    die("Database connection failed. Please check your configuration.");
}

if (is_logged_in()) {
    header("Location: " . BASE_URL);
    exit();
}

$error = '';
$success = '';
try {
    // Add this at the start of your registration code
    $stmt = $conn->query("SELECT DATABASE()");
    error_log("Current database: " . $stmt->fetchColumn());
    
    $stmt = $conn->query("SHOW TABLES");
    error_log("Tables in database: " . print_r($stmt->fetchAll(PDO::FETCH_COLUMN), true));
} catch(PDOException $e) {
    error_log("Database check error: " . $e->getMessage());
}
// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']);
    $id_number = sanitize_input($_POST['id_number']);
    $apci_number = sanitize_input($_POST['apci_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($name) || empty($email) || empty($id_number) || empty($apci_number) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long";
    } elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
        $error = "Password must contain at least one uppercase letter, one lowercase letter, one number and one special character";
    } elseif (!validate_id_number($id_number)) {
        $error = "Invalid ID number format";
    } else {
        try {
            $conn->beginTransaction();
            
            // Change 'users' to 'patients' in the check
            $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE email = ? OR id_number = ?");
            $stmt->execute([$email, $id_number]);
            
            if ($stmt->fetchColumn() > 0) {
                $error = "A user with this email or ID number already exists";
            } else {
                // Change 'users' to 'patients' in the insert
                $stmt = $conn->prepare("
    INSERT INTO patients (
        name, 
        email, 
        id_number, 
        apci_number, 
        password
    ) VALUES (?, ?, ?, ?, ?)
");


$hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
$stmt->execute([
    $name, 
    $email, 
    $id_number, 
    $apci_number, 
    $hashed_password
]);
                
                $conn->commit();
                $success = "Registration successful! You can now login.";
                
                // Clear sensitive data
                $password = $confirm_password = null;
            }
        } catch(PDOException $e) {
            $conn->rollBack();
            error_log("Registration error: " . $e->getMessage());
            $error = "Registration failed: " . $e->getMessage(); // Keep this during development
            // Change to $error = "Registration failed. Please try again."; in production
        }
    }
}

// Rest of your HTML code remains the same...
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <h1>Patient Registration</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <br>
                <a href="<?php echo BASE_URL; ?>auth/login.php">Click here to login</a>
            </div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" value="<?php echo isset($_POST['id_number']) ? htmlspecialchars($_POST['id_number']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="apci_number">APCI Number</label>
                <input type="text" id="apci_number" name="apci_number" value="<?php echo isset($_POST['apci_number']) ? htmlspecialchars($_POST['apci_number']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <small class="form-text text-muted">
                    Password must be at least 8 characters long and contain at least one uppercase letter, 
                    one lowercase letter, one number and one special character.
                </small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <div class="auth-links">
            <a href="<?php echo BASE_URL; ?>auth/login.php">Already have an account? Login here</a>
            <a href="<?php echo BASE_URL; ?>">Back to Home</a>
        </div>
    </div>

    <script src="<?php echo BASE_URL; ?>assets/js/register.js"></script>
</body>
</html>