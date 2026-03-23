<?php
include('../database/db_connection.php');

session_start();
$patient_id = $_GET['id'];

// Fetch patient data
$sql = "
    SELECT patients.*, users.email, mc.status, mc.test_date, mc.test_expiry
    FROM patients
    INNER JOIN users ON patients.user_id = users.id
    LEFT JOIN medical_clearance mc ON patients.id = mc.patient_id
    WHERE patients.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if the patient exists
if ($result->num_rows > 0) {
    $patient = $result->fetch_assoc();
} else {
    echo "Patient not found.";
    exit;
}

// Fetch medical tests related to the patient
$test_sql = "
    SELECT mt.id, mt.test_name, mc.status, mc.test_date, mc.test_expiry
    FROM medical_tests mt
    LEFT JOIN medical_clearance mc ON mt.id = mc.test_id
    WHERE mc.patient_id = ?
";

$test_stmt = $conn->prepare($test_sql);
$test_stmt->bind_param("i", $patient_id);
$test_stmt->execute();
$test_result = $test_stmt->get_result();

if ($patient_result->num_rows === 0) {
    $_SESSION['error_message'] = "Patient not found.";
    header("Location: view_patient.php");
    exit();
}

$test_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Information - MedPass</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800">

<!-- Navbar -->
<nav class="navbar admin-navbar">
    <div class="nav-logo">
        <a href="admin_dashboard.php">Med<span>Pass</span></a>
    </div>
    <div class="nav-content">
        <ul class="nav-links">
            <li><a href="view_patient.php">View Patients</a></li>
            <li><a href="add_patient.php">Add New Patient</a></li>
            <li><a href="manage_results.php">Manage Test Results</a></li>
        </ul>
        <div class="nav-actions">
            <a href="admin_logout.php" class="logout-btn">Logout</a>
        </div>
    </div>
</nav>

<!-- Container -->
<div class="container mx-auto p-8 bg-white shadow-lg rounded-lg mt-32 max-w-[1200px]">
    <form method="POST" action="view_patientInfo.php?id=<?= $patient_id ?>" enctype="multipart/form-data">
        <!-- Profile Section -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Profile Information</h1>
            <div>
                <button type="button" id="editButton" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-700 transition">Edit</button>
                <button type="button" id="cancelButton" class="hidden px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">Back</button>
                <button type="submit" id="saveButton" name="save_changes" class="hidden px-4 py-2 bg-black text-white rounded hover:bg-gray-700 transition">Save Changes</button>
            </div>
        </div>

        <!-- View Mode -->
        <div id="viewProfile" class="mb-8">
            <div class="grid grid-cols-1 gap-4 mb-8">
                <div class="mb-2"><p class="text-lg"><strong>Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p></div>
                <div class="mb-2"><p class="text-lg"><strong>Birthdate:</strong> <?= htmlspecialchars(date('m/d/Y', strtotime($patient['dob']))) ?></p></div>
                <div class="mb-2"><p class="text-lg"><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p></div>
                <div class="mb-2"><p class="text-lg"><strong>Phone Number:</strong> <?= htmlspecialchars($patient['contact_number']) ?></p></div>
            </div>
        </div>

        <!-- Medical Clearance Status Section -->
        <h2 class="text-2xl font-bold mb-6">Medical Clearance Status</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse border">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="border p-4 text-left">Test Name</th>
                        <th class="border p-4 text-left">Status</th>
                        <th class="border p-4 text-left">Test Date</th>
                        <th class="border p-4 text-left">Test Expiry</th>
                        <th class="border p-4 text-left">Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                        <tr class="border hover:bg-gray-50">
                            <td class="border p-4"><?= htmlspecialchars($test['test_name']) ?></td>
                            <td class="border p-4"><?= htmlspecialchars($test['status']) ?></td>
                            <td class="border p-4"><?= htmlspecialchars($test['test_date']) ?></td>
                            <td class="border p-4"><?= htmlspecialchars($test['test_expiry']) ?></td>
                            <td class="border p-4">
                                <?php if ($test['status'] === 'Completed'): ?>
                                    [uploaded file]
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </form>
</div>

<script src="../assets/script.js"></script>

</body>
</html>

<?php 
// Close the database connection
$conn->close();
?>
