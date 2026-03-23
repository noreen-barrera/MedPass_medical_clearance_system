<?php
include('../database/db_connection.php');

$errors = [];
$success_message = "";

// Initialize form variables
$first_name = $last_name = $email = $phone = $dob = $password = "";
$selected_tests = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = filter_var(trim($_POST['first_name']), FILTER_SANITIZE_STRING);
    $last_name = filter_var(trim($_POST['last_name']), FILTER_SANITIZE_STRING);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $phone = filter_var(trim($_POST['phone']), FILTER_SANITIZE_STRING);
    $dob = $_POST['dob'];
    $password = $_POST['password'];
    $selected_tests = $_POST['tests'] ?? []; // Fetch selected tests

    // Input validation
    if (!preg_match("/^[a-zA-Z\s]+$/", $first_name)) {
        $errors[] = "Invalid first name (letters only).";
    }
    if (!preg_match("/^[a-zA-Z\s]+$/", $last_name)) {
        $errors[] = "Invalid last name (letters only).";
    }
    if (!preg_match("/^\d{10,15}$/", $phone)) {
        $errors[] = "Invalid phone number (10-15 digits only).";
    }

    $dob_date = new DateTime($dob);
    $today = new DateTime();
    $dob_date->setTime(0, 0, 0);
    $today->setTime(0, 0, 0);

    if ($dob_date >= $today) {
        $errors[] = "Date of birth must be in the past.";
    }


    if (empty($errors)) {

        // Check for duplicate email
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "🚫 Email is already in use. Please use a different one.";
        }
        $stmt->close();

        // Check for duplicate first and last name combination
        $stmt = $conn->prepare("SELECT id FROM patients WHERE first_name = ? AND last_name = ?");
        $stmt->bind_param("ss", $first_name, $last_name);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "🚫 A patient with the same first and last name already exists.";
        }
        $stmt->close();

        if (empty($errors)) {
            $conn->begin_transaction();

            try {
                // Insert into users table
                $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'patient')");
                $stmt->bind_param("ss", $email, $password);
                $stmt->execute();
                $user_id = $stmt->insert_id;

                // Insert into patients table
                $stmt = $conn->prepare("INSERT INTO patients (user_id, first_name, last_name, dob, contact_number) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $first_name, $last_name, $dob, $phone);
                $stmt->execute();
                $patient_id = $stmt->insert_id;

                // Insert tests into the medical_clearance table
                if (!empty($selected_tests)) {
                    $stmt = $conn->prepare("INSERT INTO medical_clearance (patient_id, test_id, status, test_date, test_expiry) VALUES (?, ?, 'Pending', NOW(), DATE_ADD(NOW(), INTERVAL 6 MONTH))");
                    foreach ($selected_tests as $test_id) {
                        $stmt->bind_param("ii", $patient_id, $test_id);
                        $stmt->execute();
                    }
                }

                $conn->commit();
                $success_message = "✅ Patient added successfully.";

                // Clear form values on success
                $first_name = $last_name = $email = $phone = $dob = $password = "";
                $selected_tests = [];

            } catch (mysqli_sql_exception $e) {
                $conn->rollback();
                $errors[] = "❌ Database error: " . $e->getMessage();
            }

            $stmt->close();
            $conn->close();
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Patient</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

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

<div class="container mx-auto p-8 bg-white shadow-lg rounded-lg mt-32 max-w-[1200px]">
    <h1 class="text-3xl font-bold text-gray-800 mb-6 text-center">Add New Patient</h1>

    <!-- Display Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg">
            <strong class="font-bold">Errors:</strong>
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Display Success Message -->
    <?php if (!empty($success_message)): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg">
            <strong class="font-bold">Success:</strong>
            <p><?= htmlspecialchars($success_message) ?></p>
        </div>
    <?php endif; ?>

    <form action="add_patient.php" method="POST" class="bg-white p-8 shadow-md rounded-lg space-y-6" onsubmit="return validateForm()">

        <!-- Patient Details -->
        <div class="space-y-4">
            <label class="block text-gray-700">First Name:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($first_name) ?>" required class="w-full p-2 border rounded">

            <label class="block text-gray-700">Last Name:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($last_name) ?>" required class="w-full p-2 border rounded">

            <label class="block text-gray-700">Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required class="w-full p-2 border rounded">

            <label class="block text-gray-700">Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($phone) ?>" required class="w-full p-2 border rounded">

            <label class="block text-gray-700">Date of Birth:</label>
            <input type="date" name="dob" value="<?= htmlspecialchars($dob) ?>" required class="w-full p-2 border rounded">

            <label class="block text-gray-700">Temporary Password:</label>
            <input type="text" name="password" value="<?= htmlspecialchars($password) ?>" required class="w-full p-2 border rounded">
        </div>

        <!-- Assign Tests -->
        <div class="mt-4">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Assign Tests:</h3>
            <div class="flex flex-wrap gap-x-6 items-center">
                <?php
                $conn = new mysqli("localhost", "root", "", "medpass_db");
                $result = $conn->query("SELECT id, test_name FROM medical_tests");
                while ($row = $result->fetch_assoc()) {
                    $isChecked = in_array($row['id'], $selected_tests) ? "checked" : "";
                    echo "
                    <label class='flex items-center space-x-2'>
                        <input type='checkbox' name='tests[]' value='{$row['id']}' $isChecked class='form-checkbox'>
                        <span>{$row['test_name']}</span>
                    </label>";
                }
                $conn->close();
                ?>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="text-center">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded hover:bg-green-700 w-full">
                Add Patient
            </button>
        </div>

    </form>
</div>

<!-- Link External JavaScript File -->
<script src="../assets/script.js"></script>

</body>
</html>
