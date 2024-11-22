<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
function firstTimeAdminSetup() {
    global $pdo;
    
    // Check if any admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $stmt->execute();
    
    if ($stmt->fetchColumn() == 0) {
        // No admin exists, create default admin
        $default_email = 'admin@hospital.com';
        $default_password = password_hash('AdminPass123!', PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (email, password, user_type, created_at) VALUES (?, ?, 'admin', NOW())");
        $stmt->execute([$default_email, $default_password]);
    }
}

// Call this function early in the script
firstTimeAdminSetup();
// Check if already logged in
if (is_logged_in()) {
    $redirect_url = BASE_URL . ($_SESSION['user_type'] === 'admin' ? 'admin' : 'patient') . '/dashboard.php';
    header("Location: " . $redirect_url);
    exit();
}

$errors = [];
$email = '';
$login_type = isset($_GET['type']) && $_GET['type'] === 'admin' ? 'admin' : 'user';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $submitted_type = $_POST['login_type'] ?? 'user';

    // Validate input
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    }

   // If no validation errors, attempt login
if (empty($errors)) {
    try {
        // Debugging for admin login
    if ($submitted_type === 'admin') {
        // Log detailed login attempt information
        error_log("Admin Login Attempt Details:");
        error_log("Submitted Email: " . $email);
        error_log("Submitted Password Length: " . strlen($password));

        // Prepare and execute query
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // Log user query results
        if (!$user) {
            error_log("No admin user found with email: " . $email);
        } else {
            // Detailed user information logging
            error_log("Stored User ID: " . $user['id']);
            error_log("Stored Email: " . $user['email']);
            error_log("Stored User Type: " . $user['user_type']);
            error_log("Stored Password Hash Length: " . strlen($user['password']));
            
            // Password verification debug
            $password_match = password_verify($password, $user['password']);
            error_log("Password Verification Result: " . ($password_match ? 'SUCCESS' : 'FAILURE'));
        }
    }
        // Use different queries based on login type
        if ($submitted_type === 'admin') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'admin'");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND user_type = 'patient'");
        }
        
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // DEBUG: Print user details
        error_log("User details: " . print_r($user, true));
        error_log("Submitted email: " . $email);
        error_log("Submitted type: " . $submitted_type);

        if ($user && password_verify($password, $user['password'])) {
            // Existing login success logic
            // ...
        } else {
            $errors[] = "Invalid email or password";
            
            // Additional debug information
            error_log("Password verification failed");
            if (!$user) {
                error_log("No user found with email: " . $email);
            } else {
                error_log("Stored password hash: " . $user['password']);
            }
        }
    } catch (PDOException $e) {
        $errors[] = "An error occurred. Please try again later.";
        error_log("Login error: " . $e->getMessage());
    }
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to access your healthcare portal">
    <title><?php echo htmlspecialchars(SITE_NAME); ?> - <?php echo $login_type === 'admin' ? 'Admin Login' : 'Patient Login'; ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <a href="<?php echo htmlspecialchars(BASE_URL); ?>" class="back-link">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
            </svg>
            Back to Home
        </a>

        <div class="auth-form-container">
            <h1><?php echo $login_type === 'admin' ? 'Admin Login' : 'Patient Login'; ?></h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . ($login_type === 'admin' ? '?type=admin' : '')); ?>" class="auth-form">
                <input type="hidden" name="login_type" value="<?php echo htmlspecialchars($login_type); ?>">
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($email); ?>"
                        required 
                        autocomplete="email"
                        placeholder="Enter your email"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required 
                        autocomplete="current-password"
                        placeholder="Enter your password"
                    >
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5z"/>
                        </svg>
                        <?php echo $login_type === 'admin' ? 'Admin Login' : 'Login'; ?>
                    </button>
                </div>

                <div class="auth-links">
                    <?php if ($login_type !== 'admin'): ?>
                        <a href="<?php echo htmlspecialchars(BASE_URL); ?>auth/forgot-password.php">Forgot Password?</a>
                        <span class="separator">|</span>
                        <a href="<?php echo htmlspecialchars(BASE_URL); ?>auth/register.php">Create Account</a>
                        <span class="separator">|</span>
                    <?php endif; ?>
                    <a href="<?php echo htmlspecialchars(BASE_URL); ?>auth/login.php<?php echo $login_type === 'admin' ? '' : '?type=admin'; ?>">
                        <?php echo $login_type === 'admin' ? 'Patient Login' : 'Admin Login'; ?>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.</p>
    </footer>

    <script src="<?php echo htmlspecialchars(BASE_URL); ?>assets/js/main.js" defer></script>
</body>
</html>