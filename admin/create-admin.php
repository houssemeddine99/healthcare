<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// Check if user is logged in and is an admin
if (!is_logged_in() || $_SESSION['user_type'] !== 'admin') {
    header("Location: " . BASE_URL . "auth/login.php?type=admin");
    exit();
}

$errors = [];
$success = false;

// Enhanced password complexity check
function isPasswordComplex($password) {
    return (
        strlen($password) >= 12 && 
        preg_match('/[A-Z]/', $password) && 
        preg_match('/[a-z]/', $password) && 
        preg_match('/[0-9]/', $password) && 
        preg_match('/[^a-zA-Z0-9]/', $password)
    );
}

// Audit logging function
function logAdminCreation($email, $creator_email) {
    $log_file = __DIR__ . '/../logs/admin_creation.log';
    $log_entry = date('Y-m-d H:i:s') . " | New Admin Created: $email | By: $creator_email\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate input
    if (empty($name)) {
        $errors[] = "Name is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (!isPasswordComplex($password)) {
        $errors[] = "Password must be at least 12 characters long and include uppercase, lowercase, number, and special character";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Check admin account limit
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();

        if ($admin_count >= 5) {
            $errors[] = "Maximum number of admin accounts reached";
        }
    } catch (PDOException $e) {
        $errors[] = "An error occurred checking admin accounts";
        error_log("Admin count check error: " . $e->getMessage());
    }

    // Check if email already exists
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email already exists";
            }
        } catch (PDOException $e) {
            $errors[] = "An error occurred. Please try again later.";
            error_log("Admin registration email check error: " . $e->getMessage());
        }
    }

    // Create new admin account
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO users (name, email, password, user_type, created_at, last_login, status) 
                VALUES (?, ?, ?, 'admin', NOW(), NULL, 'active')
            ");
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
            $stmt->execute([$name, $email, $hashedPassword]);
            
            // Log the admin creation
            logAdminCreation($email, $_SESSION['email']);

            $pdo->commit();
            $success = true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "An error occurred. Please try again later.";
            error_log("Admin registration error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars(SITE_NAME); ?> - Create Admin Account</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <a href="<?php echo htmlspecialchars(BASE_URL); ?>admin/dashboard.php" class="back-link">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
            Back to Dashboard
        </a>

        <div class="auth-form-container">
            <h1>Create Admin Account</h1>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    Admin account created successfully!
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="auth-form">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        required 
                        value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required 
                        value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        minlength="8"
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        required 
                        minlength="8"
                    >
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        Create Admin Account
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.</p>
    </footer>
</body>
</html>