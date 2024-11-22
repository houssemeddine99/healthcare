<?php

session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'patient') {
    header('Location: ../index.php');
    exit();
}

$stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT i.*, m.medication_name, m.dosage, m.frequency, m.start_date, m.doctor_name 
    FROM illnesses i 
    LEFT JOIN medications m ON i.id = m.illness_id 
    WHERE i.patient_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$illnesses = [];
while ($row = $result->fetch_assoc()) {
    $illnesses[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - Healthcare Management</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <div class="user-avatar">
                    <img src="../assets/images/default-avatar.png" alt="User Avatar">
                </div>
                <h3><?php echo htmlspecialchars($patient['name']); ?></h3>
                <p>Patient ID: <?php echo htmlspecialchars($patient['id_number']); ?></p>
            </div>
            <ul class="nav-links">
                <li class="active"><a href="#overview">Overview</a></li>
                <li><a href="#medications">My Medications</a></li>
                <li><a href="#reminders">Reminders</a></li>
                <li><a href="#profile">Profile Settings</a></li>
                <li><a href="../auth/logout.php">Logout</a></li>
            </ul>
        </nav>

        <main class="main-content">
            <header class="dashboard-header">
                <h1>Welcome Back, <?php echo htmlspecialchars($patient['name']); ?></h1>
                <div class="header-actions">
                    <button id="addIllnessBtn" class="btn-primary">Add New Condition</button>
                </div>
            </header>

            <section id="overview" class="dashboard-section">
                <h2>My Health Conditions</h2>
                <div class="illness-cards">
                    <?php foreach ($illnesses as $illness): ?>
                        <div class="illness-card">
                            <div class="illness-header">
                                <h3><?php echo htmlspecialchars($illness['illness_name']); ?></h3>
                                <span class="diagnosis-date">
                                    Diagnosed: <?php echo date('M d, Y', strtotime($illness['diagnosis_date'])); ?>
                                </span>
                            </div>
                            <div class="medication-list">
                                <h4>Current Medications:</h4>
                                <ul>
                                    <li>
                                        <strong><?php echo htmlspecialchars($illness['medication_name']); ?></strong>
                                        <p>Dosage: <?php echo htmlspecialchars($illness['dosage']); ?></p>
                                        <p>Frequency: <?php echo htmlspecialchars($illness['frequency']); ?></p>
                                        <p>Prescribed by: Dr. <?php echo htmlspecialchars($illness['doctor_name']); ?></p>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-actions">
                                <button class="btn-secondary edit-illness" data-id="<?php echo $illness['id']; ?>">
                                    Edit Details
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>

            <section id="medications" class="dashboard-section hidden">
                <h2>Medication Schedule</h2>
                <div class="medication-timeline"></div>
            </section>

            <section id="reminders" class="dashboard-section hidden">
                <h2>My Reminders</h2>
                <div class="reminders-container"></div>
            </section>
        </main>
    </div>

    <div id="addIllnessModal" class="modal">
        <div class="modal-content">
            <h2>Add New Health Condition</h2>
            <form id="addIllnessForm">
                <div class="form-group">
                    <label for="illnessType">Condition Type</label>
                    <select id="illnessType" name="illness_type" required>
                        <option value="">Select Condition</option>
                        <option value="diabetes">Diabetes</option>
                        <option value="hypertension">Hypertension</option>
                        <option value="thyroid">Thyroid Condition</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="diagnosisDate">Diagnosis Date</label>
                    <input type="date" id="diagnosisDate" name="diagnosis_date" required>
                </div>

                <div class="form-group">
                    <label for="medicationName">Medication Name</label>
                    <input type="text" id="medicationName" name="medication_name" required>
                </div>

                <div class="form-group">
                    <label for="dosage">Dosage</label>
                    <input type="text" id="dosage" name="dosage" required>
                </div>

                <div class="form-group">
                    <label for="frequency">Frequency</label>
                    <input type="text" id="frequency" name="frequency" required>
                </div>

                <div class="form-group">
                    <label for="doctorName">Doctor's Name</label>
                    <input type="text" id="doctorName" name="doctor_name" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Save</button>
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/dashboard.js"></script>
</body>
</html>