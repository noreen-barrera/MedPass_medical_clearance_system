<?php
include('../database/db_connection.php');

// Start session for displaying messages
session_start();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['status'])) {
    // Prepare a statement to update existing test results
    $stmt = $conn->prepare("
        UPDATE medical_clearance mc
        JOIN medical_tests mt ON mc.test_id = mt.id
        SET mc.status = ?, mc.test_date = CURDATE(), mc.test_expiry = DATE_ADD(CURDATE(), INTERVAL 6 MONTH)
        WHERE mc.patient_id = ? AND mt.test_name = ?
    ");

    // Track any errors during processing
    $errors = [];

    // Process each patient's test statuses
    foreach ($_POST['status'] as $patient_id => $tests) {
        foreach ($tests as $test_name => $status) {
            // Bind parameters
            $stmt->bind_param(
                "sis", 
                $status,
                $patient_id, 
                $test_name
            );

            // Execute the statement
            if (!$stmt->execute()) {
                $errors[] = "Failed to update status for patient $patient_id, test $test_name: " . $stmt->error;
            }
        }
    }

    // Close the prepared statement
    $stmt->close();

    // Redirect with status message
    if (empty($errors)) {
        $_SESSION['success_message'] = "Test results updated successfully.";
    } else {
        $_SESSION['error_messages'] = $errors;
    }

    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Initialize search parameters
$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_test = isset($_GET['test']) ? $conn->real_escape_string($_GET['test']) : '';
$search_status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';

// Fetch patient test results grouped by patient with search functionality
$query = "
SELECT 
    p.id AS patient_id,
    CONCAT(p.first_name, ' ', p.last_name) AS patient_name,
    mt.test_name,
    IFNULL(mc.status, '---') AS status
FROM patients p
CROSS JOIN medical_tests mt
LEFT JOIN medical_clearance mc ON mc.patient_id = p.id AND mc.test_id = mt.id
WHERE 1=1
";

// Add search conditions
if (!empty($search_term)) {
    $query .= " AND (p.first_name LIKE '%$search_term%' OR p.last_name LIKE '%$search_term%')";
}
if (!empty($search_test)) {
    $query .= " AND mt.test_name = '$search_test'";
}
if (!empty($search_status) && $search_status !== 'all') {
    $query .= " AND IFNULL(mc.status, '---') = '$search_status'";
}

$query .= " ORDER BY p.id, mt.test_name";

$result = $conn->query($query);

$patients = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patient_id = $row['patient_id'];

        if (!isset($patients[$patient_id])) {
            $patients[$patient_id] = [
                'name' => $row['patient_name'],
                'tests' => []
            ];
        }

        $patients[$patient_id]['tests'][$row['test_name']] = $row['status'];
    }
}

// Get unique test names for dropdown
$test_query = "SELECT DISTINCT test_name FROM medical_tests ORDER BY test_name";
$test_result = $conn->query($test_query);
$available_tests = [];
while ($test_row = $test_result->fetch_assoc()) {
    $available_tests[] = $test_row['test_name'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Test Results</title>
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
    <?php 
    if (isset($_SESSION['success_message'])): 
    ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($_SESSION['success_message']) ?></span>
        </div>
    <?php 
        unset($_SESSION['success_message']);
    endif; 
    ?>

    <!-- Error Messages -->
    <?php 
    if (isset($_SESSION['error_messages'])) {
        foreach ($_SESSION['error_messages'] as $error):
    ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline"><?= htmlspecialchars($error) ?></span>
        </div>
    <?php 
        endforeach;
        // Clear the errors after displaying
        unset($_SESSION['error_messages']);
    }
    ?>

<div class="flex flex-col sm:flex-row justify-between items-center mb-6">
    <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 w-full">
        <form method="GET" class="flex-grow flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Search by patient name" 
                value="<?= htmlspecialchars($search_term) ?>"
                class="flex-grow p-2 border rounded"
            >
            
            <select name="test" class="p-2 border rounded">
                <option value="">All Tests</option>
                <?php foreach ($available_tests as $test): ?>
                    <option value="<?= htmlspecialchars($test) ?>" 
                        <?= $search_test === $test ? 'selected' : '' ?>>
                        <?= htmlspecialchars($test) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <select name="status" class="p-2 border rounded">
                <option value="all">All Statuses</option>
                <option value="Pending" <?= $search_status === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Completed" <?= $search_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
            </select>
            
            <div class="flex space-x-2">
                <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    Search
                </button>
                
                <?php if (!empty($search_term) || !empty($search_test) || !empty($search_status)): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Header and Save Button Container -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Manage Test Results</h1>
    <button type="submit" 
            form="results-form"
            class="px-4 py-2 rounded text-sm bg-black text-white hover:bg-gray-700 transition w-auto min-w-[100px] text-center">
        Save Changes
    </button>
</div>

    <!-- Form -->
    <form id="results-form" method="POST">
        <div class="overflow-x-auto">
            <table class="min-w-full table-auto border-collapse">
                <thead>
                    <tr class="bg-gray-200">
                        <th class="p-4 text-left">Patient Name</th>
                        <th class="p-4 text-center">CBC</th>
                        <th class="p-4 text-center">Chest X-Ray</th>
                        <th class="p-4 text-center">Urinalysis</th>
                        <th class="p-4 text-center">Fecalysis</th>
                        <th class="p-4 text-center">Physical Exam</th>
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <?php
                    $allTests = ['CBC', 'Chest X-Ray', 'Urinalysis', 'Fecalysis', 'Physical Exam'];

                    if (empty($patients)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">No test results found.</td>
                        </tr>
                    <?php 
                    else:
                        foreach ($patients as $patient_id => $patient): 
                    ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4"><?= htmlspecialchars($patient['name']) ?></td>

                            <!-- Iterate through all tests to ensure consistent columns -->
                            <?php foreach ($allTests as $test_name): ?>
                                <td class="p-4 text-center">
                                    <?php 
                                    $status = $patient['tests'][$test_name] ?? "---";
                                    ?>

                                    <?php if ($status === "---"): ?>
                                        <span class="text-gray-500 italic"><?= $status ?></span>
                                    <?php else: ?>
                                        <select name="status[<?= $patient_id ?>][<?= $test_name ?>]" 
                                                onchange="toggleFileUpload(this)" 
                                                class="w-full p-2 border rounded focus:ring focus:ring-blue-300 transition">
                                            <option value="Pending" <?= $status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Completed" <?= $status == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>

                                        <input type="file" 
                                            name="file[<?= $patient_id ?>][<?= $test_name ?>]" 
                                            class="w-full mt-2 p-2 border rounded <?= $status == 'Pending' ? 'opacity-50 cursor-not-allowed' : '' ?>" 
                                            <?= $status == 'Pending' ? 'disabled' : '' ?>>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php 
                        endforeach; 
                    endif; 
                    ?>
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