<?php
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    $redirect_url = BASE_URL . ($_SESSION['user_type'] === 'staff' ? 'admin' : 'patient') . '/dashboard.php';
    header("Location: " . $redirect_url);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Welcome to our Healthcare Management System. Access your medical records, manage appointments, and more.">
    <title><?php echo htmlspecialchars(SITE_NAME); ?> - Welcome</title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars(BASE_URL); ?>assets/css/style.css">
</head>
<body>
    <div class="welcome-container">
        <h1><?php echo htmlspecialchars(SITE_NAME); ?></h1>
        
        <div class="welcome-buttons">
            <a href="<?php echo htmlspecialchars(BASE_URL); ?>auth/login.php" class="btn btn-primary">
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M11 7L9.6 8.4l2.6 2.6H2v2h10.2l-2.6 2.6L11 17l5-5-5-5zm9 12h-8v2h8c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-8v2h8v14z"/>
                </svg>
                Login
            </a>
            <a href="<?php echo htmlspecialchars(BASE_URL); ?>auth/register.php" class="btn btn-secondary">
                <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
                </svg>
                Register
            </a>
        </div>

        <div class="welcome-info">
            <h2>Welcome to our Healthcare Management System</h2>
            <div class="info-cards">
                <div class="card">
                    <h3>
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                        </svg>
                        For Patients
                    </h3>
                    <ul>
                        <li>View your medical history</li>
                        <li>Check prescribed medications</li>
                        <li>Manage appointments</li>
                        <li>Update your profile</li>
                    </ul>
                </div>
                
                <div class="card">
                    <h3>
                        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V5h14v14zm-7-2h2v-4h4v-2h-4V7h-2v4H8v2h4z"/>
                        </svg>
                        For Medical Staff
                    </h3>
                    <ul>
                        <li>Manage patient records</li>
                        <li>Prescribe medications</li>
                        <li>Schedule appointments</li>
                        <li>Generate reports</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(SITE_NAME); ?>. All rights reserved.</p>
    </footer>

    <!-- Add JavaScript file -->
    <script src="<?php echo htmlspecialchars(BASE_URL); ?>assets/js/main.js" defer></script>
</body>
</html>