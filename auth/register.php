<?php
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../includes/functions.php';

if (is_logged_in()) {
    header("Location: " . BASE_URL);
    exit();
}

$error = '';
$success = '';

// In register.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name']);
    $email = sanitize_input($_POST['email']); // Change from ID number
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
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } elseif (!validate_id_number($id_number)) {
        $error = "Invalid ID number format";
    } else {
        try {
            // Check if email or ID number already exists
            if (user_exists($email, 'users', 'email') || user_exists($id_number, 'users', 'id_number')) {
                $error = "A user with this email or ID number already exists";
            } else {
                $stmt = $conn->prepare("
                    INSERT INTO users (
                        name, 
                        email, 
                        id_number, 
                        apci_number, 
                        password, 
                        user_type, 
                        created_at
                    ) VALUES (?, ?, ?, ?, ?, 'patient', NOW())
                ");
                
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt->execute([
                    $name, 
                    $email, 
                    $id_number, 
                    $apci_number, 
                    $hashed_password
                ]);
                
                $success = "Registration successful! You can now login.";
            }
        } catch(PDOException $e) {
            $error = "Registration failed. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
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
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
    <label for="email">Email Address</label>
    <input type="email" id="email" name="email" required>
</div>

            <div class="form-group">
                <label for="id_number">ID Number</label>
                <input type="text" id="id_number" name="id_number" required>
            </div>

            <div class="form-group">
                <label for="apci_number">APCI Number</label>
                <input type="text" id="apci_number" name="apci_number" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
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