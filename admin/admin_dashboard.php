<?php
// admin/dashboard.php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: ../index.php');
    exit();
}

// Fetch all patients
$query = "
    SELECT p.*, 
           GROUP_CONCAT(DISTINCT i.illness_name) as illnesses
    FROM patients p
    LEFT JOIN illnesses i ON p.id = i.patient_id
    GROUP BY p.id
";
$result = $conn->query($query);
$patients = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Staff Dashboard - Healthcare Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="../assets/images/default-avatar.png" alt="Staff Avatar">
                </div>
                <h3>Medical Staff Portal</h3>
            </div>
            <ul class="nav-links">
                <li class="active"><a href="#patients">Patients</a></li>
                <li><a href="#reports">Reports</a></li>
                <li><a href="#settings">Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Patient Management</h1>
                <div class="search-container">
                    <input type="text" id="patientSearch" placeholder="Search patients...">
                </div>
            </header>

            <section id="patients" class="dashboard-section">
                <div class="table-container">
                    <table class="patients-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>APCI Number</th>
                                <th>Conditions</th>
                                <th>Last Visit</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['id_number']); ?></td>
                                <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['apci_number']); ?></td>
                                <td><?php echo htmlspecialchars($patient['illnesses'] ?? 'None recorded'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($patient['last_visit'] ?? $patient['created_at'])); ?></td>
                                <td class="actions">
                                    <button class="btn-view" onclick="viewPatient(<?php echo $patient['id']; ?>)">
                                        View
                                    </button>
                                    <button class="btn-edit" onclick="editPatient(<?php echo $patient['id']; ?>)">
                                        Edit
                                    </button>
                                    <button class="btn-delete" onclick="deletePatient(<?php echo $patient['id']; ?>)">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- Patient View Modal -->
    <div id="patientViewModal" class="modal">
        <div class="modal-content">
            <h2>Patient Details</h2>
            <div id="patientDetails"></div>
        </div>
    </div>

    <script src="../assets/js/admin-dashboard.js"></script>
</body>
</html>