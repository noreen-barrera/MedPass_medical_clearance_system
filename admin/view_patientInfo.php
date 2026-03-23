<?php
include('../database/db_connection.php');

// Start session for displaying messages
session_start();

// Check if patient ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Patient ID is required.";
    header("Location: view_patient.php");
    exit();
}

$patient_id = intval($_GET['id']);

// Get patient information
$patient_query = "
    SELECT 
        p.id, 
        p.first_name, 
        p.last_name, 
        p.dob, 
        p.contact_number, 
        u.email 
    FROM 
        patients p
    JOIN 
        users u ON p.user_id = u.id
    WHERE 
        p.id = ?
";

$stmt = $conn->prepare($patient_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient_result = $stmt->get_result();

if ($patient_result->num_rows === 0) {
    $_SESSION['error_message'] = "Patient not found.";
    header("Location: view_patient.php");
    exit();
}

$patient = $patient_result->fetch_assoc();

// Get all available medical tests
$tests_query = "
    SELECT 
        mt.id,
        mt.test_name,
        mc.id as result_id,
        mc.status,
        mc.test_date,
        mc.test_expiry
    FROM 
        medical_tests mt
    LEFT JOIN 
        medical_clearance mc ON mt.id = mc.test_id AND mc.patient_id = ?
    ORDER BY 
        mt.test_name
";

$stmt = $conn->prepare($tests_query);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$tests_result = $stmt->get_result();

$tests = [];
while ($test = $tests_result->fetch_assoc()) {
    $tests[] = $test;
}

// Handle form submission for editing patient information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    // Update patient basic information
    $update_query = "
        UPDATE patients p
        JOIN users u ON p.user_id = u.id
        SET 
            p.first_name = ?,
            p.last_name = ?,
            p.dob = ?,
            p.contact_number = ?,
            u.email = ? 
        WHERE 
            p.id = ?
    ";
    
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param(
        "sssssi",
        $_POST['first_name'],
        $_POST['last_name'],
        $_POST['dob'],
        $_POST['contact_number'],
        $_POST['email'],
        $patient_id
    );
    
    $update_success = $update_stmt->execute();
    
    // Process each test status update
    if (isset($_POST['test_status']) && is_array($_POST['test_status'])) {
        foreach ($_POST['test_status'] as $test_id => $status) {
            if (empty($status) || $status == "---") {
                continue;
            }
            
            // Check if a result record already exists
            $check_query = "SELECT id FROM medical_clearance WHERE patient_id = ? AND test_id = ?";
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param("ii", $patient_id, $test_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows > 0) {
                // Update existing record
                $result_id = $check_result->fetch_assoc()['id'];
                $test_update_query = "
                    UPDATE medical_clearance
                    SET status = ?, test_date = CURDATE(), test_expiry = DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
                    WHERE id = ?
                ";
                $test_update_stmt = $conn->prepare($test_update_query);
                $test_update_stmt->bind_param("si", $status, $result_id);
                $test_update_stmt->execute();
            } else {
                // Insert new record
                $test_insert_query = "
                    INSERT INTO medical_clearance (patient_id, test_id, status, test_date, test_expiry)
                    VALUES (?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 6 MONTH))
                ";
                $test_insert_stmt = $conn->prepare($test_insert_query);
                $test_insert_stmt->bind_param("iis", $patient_id, $test_id, $status);
                $test_insert_stmt->execute();
            }
        }
    }
    
    // Handle file uploads
    if (isset($_FILES['test_file']) && is_array($_FILES['test_file']['name'])) {
        // Loop through each test and handle file upload
        foreach ($_FILES['test_file']['name'] as $test_id => $file_name) {
            if (!empty($file_name)) {
                // Define the upload directory (within 'assets/test_results' folder)
                $upload_dir = '../assets/test_results/';

                // Ensure the upload directory exists
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
                }

                // Get the file's temporary location and generate a unique file name
                $tmp_name = $_FILES['test_file']['tmp_name'][$test_id];
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);  // Get the file extension
                $new_file_name = $test_id . '-' . uniqid() . '.' . $file_extension; // Unique file name

                // Define the full path for storing the file
                $file_path = $upload_dir . $new_file_name;

                // Move the file to the upload directory
                if (move_uploaded_file($tmp_name, $file_path)) {
                    // Save the file details in the database
                    $file_insert_query = "
                        UPDATE medical_clearance 
                        SET file_name = ?, file_path = ?
                        WHERE patient_id = ? AND test_id = ?
                    ";
                    $file_stmt = $conn->prepare($file_insert_query);
                    $file_stmt->bind_param("ssii", $new_file_name, $file_path, $patient_id, $test_id);
                    $file_stmt->execute();
                } else {
                    $_SESSION['error_message'] = "File upload failed for test $test_id.";
                    header("Location: view_patientInfo.php?id=" . $patient_id);
                    exit();
                }
            }
        }
    }
    
    $_SESSION['success_message'] = "Patient information updated successfully.";
    header("Location: view_patientInfo.php?id=" . $patient_id);
    exit();
}
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
    <!-- Success Message -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success_message']) ?></span>
        </div>
    <?php 
        unset($_SESSION['success_message']);
    endif; ?>

    <!-- Error Message -->
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['error_message']) ?></span>
        </div>
    <?php 
        unset($_SESSION['error_message']);
    endif; ?>

    <form method="POST" action="view_patientInfo.php?id=<?= $patient_id ?>" enctype="multipart/form-data">
        <!-- Profile Section -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Profile Information</h1>
            
            <!-- Toggle Edit Mode Buttons -->
            <div>
                <button type="button" id="editButton" class="px-4 py-2 bg-black text-white rounded hover:bg-gray-700 transition">
                    Edit
                </button>
                <button type="button" id="cancelButton" class="hidden px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300 transition">
                    Back
                </button>
                <button type="submit" id="saveButton" name="save_changes" class="hidden px-4 py-2 bg-black text-white rounded hover:bg-gray-700 transition">
                    Save Changes
                </button>
            </div>
        </div>

        <!-- View Mode -->
        <div id="viewProfile" class="mb-8">
            <div class="grid grid-cols-1 gap-4 mb-8">
                <div class="mb-2">
                    <p class="text-lg"><strong>Name:</strong> <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?></p>
                </div>
                <div class="mb-2">
                    <p class="text-lg"><strong>Date of Birth:</strong> <?= htmlspecialchars($patient['dob']) ?></p>
                </div>
                <div class="mb-2">
                    <p class="text-lg"><strong>Contact Number:</strong> <?= htmlspecialchars($patient['contact_number']) ?></p>
                </div>
                <div class="mb-2">
                    <p class="text-lg"><strong>Email:</strong> <?= htmlspecialchars($patient['email']) ?></p>
                </div>
            </div>
        </div>

        <!-- Edit Mode (Form) -->
        <div id="editProfile" class="hidden">
            <div class="grid grid-cols-1 gap-4 mb-8">
                <div class="mb-2">
                    <label for="first_name" class="block">First Name:</label>
                    <input type="text" id="first_name" name="first_name" class="input-field" value="<?= htmlspecialchars($patient['first_name']) ?>" disabled>
                </div>
                <div class="mb-2">
                    <label for="last_name" class="block">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" class="input-field" value="<?= htmlspecialchars($patient['last_name']) ?>" disabled>
                </div>
                <div class="mb-2">
                    <label for="dob" class="block">Date of Birth:</label>
                    <input type="date" id="dob" name="dob" class="input-field" value="<?= htmlspecialchars($patient['dob']) ?>" disabled>
                </div>
                <div class="mb-2">
                    <label for="contact_number" class="block">Contact Number:</label>
                    <input type="text" id="contact_number" name="contact_number" class="input-field" value="<?= htmlspecialchars($patient['contact_number']) ?>" disabled>
                </div>
                <div class="mb-2">
                    <label for="email" class="block">Email:</label>
                    <input type="email" id="email" name="email" class="input-field" value="<?= htmlspecialchars($patient['email']) ?>" disabled>
                </div>
            </div>
        </div>
        
        <!-- Test Results Section -->
        <h2 class="text-xl font-bold mb-4">Test Results</h2>
        
        <div class="mb-6">
            <table class="min-w-full table-auto border-collapse border border-gray-300">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="px-4 py-2">Test Name</th>
                        <th class="px-4 py-2">Result Status</th>
                        <th class="px-4 py-2">Upload File</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tests as $test): ?>
                        <tr>
                            <td class="px-4 py-2"><?= htmlspecialchars($test['test_name']) ?></td>
                            <td class="px-4 py-2">
                                <select name="test_status[<?= $test['id'] ?>]" class="px-2 py-1 rounded" disabled>                                     <option value="---" <?= ($test['status'] == '---') ? 'selected' : '' ?>>--- Select ---</option>
                                    <option value="Completed" <?= ($test['status'] == 'Completed') ? 'selected' : '' ?>>Completed</option>
                                    <option value="Pending" <?= ($test['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                                </select>
                            </td>
                            <td class="px-4 py-2">
                                <input type="file" name="test_file[<?= $test['id'] ?>]" class="block w-full py-2" disabled>                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <button type="submit" name="save_changes" class="btn bg-blue-500 hover:bg-blue-700 text-white hidden">Save Changes</button>
    </form>
</div>

<script>
    document.getElementById('editButton').addEventListener('click', function() {
    document.getElementById('viewProfile').classList.add('hidden');
    document.getElementById('editProfile').classList.remove('hidden');
    document.getElementById('editButton').classList.add('hidden');
    document.getElementById('saveButton').classList.remove('hidden');
    document.getElementById('cancelButton').classList.remove('hidden');

    // Enable inputs for editing
    const inputs = document.querySelectorAll('#editProfile input, #editProfile select');
    const testInputs = document.querySelectorAll('table select, table input[type="file"]');
    inputs.forEach(input => input.removeAttribute('disabled'));
    testInputs.forEach(input => input.removeAttribute('disabled'));
});

document.getElementById('cancelButton').addEventListener('click', function() {
    document.getElementById('viewProfile').classList.remove('hidden');
    document.getElementById('editProfile').classList.add('hidden');
    document.getElementById('editButton').classList.remove('hidden');
    document.getElementById('saveButton').classList.add('hidden');
    document.getElementById('cancelButton').classList.add('hidden');

    // Disable inputs after cancel
    const inputs = document.querySelectorAll('#editProfile input, #editProfile select');
    const testInputs = document.querySelectorAll('table select, table input[type="file"]');
    inputs.forEach(input => input.setAttribute('disabled', true));
    testInputs.forEach(input => input.setAttribute('disabled', true));
});

</script>

</body>
</html>
