<?php
// login.php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

function firstTimeAdminSetup() {
    global $pdo;
    
    if ($pdo === null) {
        error_log("PDO connection is null in firstTimeAdminSetup()");
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
        $stmt->execute();
        
        if ($stmt->fetchColumn() == 0) {
            $default_email = 'admin@hospital.com';
            $default_password = password_hash('AdminPass123!', PASSWORD_DEFAULT, ['cost' => 12]);
            
            $stmt = $pdo->prepare("INSERT INTO users (email, password, user_type, created_at) VALUES (?, ?, 'admin', NOW())");
            $stmt->execute([$default_email, $default_password]);
        }
    } catch (PDOException $e) {
        error_log("Admin setup error: " . $e->getMessage());
    }
}

firstTimeAdminSetup();

// Generate CSRF token at the start of the script
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (is_logged_in()) {
    $redirect_url = BASE_URL . ($_SESSION['user_type'] === 'admin' ? 'admin' : 'patient') . '/dashboard.php';
    header("Location: " . $redirect_url);
    exit();
}

$errors = [];
$email = '';
$login_type = isset($_GET['type']) && $_GET['type'] === 'admin' ? 'admin' : 'patient';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token with better error handling
    if (!isset($_POST['csrf_token'])) {
        $errors[] = "CSRF token is missing";
    } elseif (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $errors[] = "Invalid request. Please refresh the page and try again.";
        // Regenerate token after failed validation
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $submitted_type = $_POST['login_type'] ?? 'patient';

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        }

        if (empty($password)) {
            $errors[] = "Password is required";
        }

        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    SELECT * FROM users 
                    WHERE email = ? 
                    AND user_type = ? 
                    AND status = 'active'
                    LIMIT 1
                ");
                
                $stmt->execute([$email, $submitted_type]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Successful login
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_type'] = $user['user_type'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['last_activity'] = time();
                    
                    // Clear sensitive data
                    $password = null;
                    
                    $redirect_url = BASE_URL . ($user['user_type'] === 'admin' ? 'admin' : 'patient') . '/dashboard.php';
                    header("Location: " . $redirect_url);
                    exit();
                } else {
                    // Failed login attempt
                    $errors[] = "Invalid email or password";
                    sleep(1); // Prevent brute force attacks
                }
            } catch (PDOException $e) {
                $errors[] = "An error occurred. Please try again later.";
                error_log("Login error: " . $e->getMessage());
            }
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
                <!-- CSRF token input INSIDE the form -->
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                
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