<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <?php if(isset($is_dashboard)): ?>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dashboard.css">
    <?php endif; ?>
</head>
<body>
    <nav class="main-nav">
        <div class="nav-brand">
            <a href="<?php echo BASE_URL; ?>"><?php echo SITE_NAME; ?></a>
        </div>
        <?php if(is_logged_in()): ?>
        <ul class="nav-links">
            <?php if($_SESSION['user_type'] === 'staff'): ?>
            <li><a href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/manage-patients.php">Manage Patients</a></li>
            <li><a href="<?php echo BASE_URL; ?>admin/reports.php">Reports</a></li>
            <?php else: ?>
            <li><a href="<?php echo BASE_URL; ?>patient/dashboard.php">Dashboard</a></li>
            <li><a href="<?php echo BASE_URL; ?>patient/profile.php">Profile</a></li>
            <li><a href="<?php echo BASE_URL; ?>patient/medications.php">Medications</a></li>
            <?php endif; ?>
            <li><a href="<?php echo BASE_URL; ?>auth/logout.php">Logout</a></li>
        </ul>
        <?php endif; ?>
    </nav>
    <main class="container">
        <?php echo display_message(); ?>

<?php
?>
    </main>
    <footer class="main-footer">
        <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
    </footer>
    <script src="<?php echo BASE_URL; ?>assets/js/<?php echo isset($js_file) ? $js_file : 'main.js'; ?>"></script>
</body>
</html>