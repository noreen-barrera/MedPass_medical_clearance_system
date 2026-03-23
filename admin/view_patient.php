<?php
include('../database/db_connection.php');

// Start session for displaying messages
session_start();

// Handle Delete Request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $patient_id = (int)$_GET['id'];
    
    // Begin transaction for safer deletion
    $conn->begin_transaction();
    
    try {
        // First, get the user_id from the patient record
        $query = "SELECT user_id FROM patients WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $patient_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user_id = $row['user_id'];

            // First delete any medical_clearance records for this patient
            $query = "DELETE FROM medical_clearance WHERE patient_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            
            // Delete from patients table (this will cascade delete from test_results due to FK constraint)
            $query = "DELETE FROM patients WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $patient_id);
            $stmt->execute();
            
            // Delete from users table
            $query = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success_message'] = "Patient deleted successfully.";
        } else {
            // Patient not found
            $_SESSION['error_messages'] = ["Patient not found."];
            $conn->rollback();
        }
    } catch (Exception $e) {
        // Something went wrong
        $_SESSION['error_messages'] = ["Error deleting patient: " . $e->getMessage()];
        $conn->rollback();
    }
    
    // Redirect to prevent resubmission
    header("Location: view_patient.php");
    exit();
}

// Initialize search parameter
$search_term = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Query to fetch patients with search functionality
// Join with users table to get email information
$query = "
SELECT 
    p.id,
    p.first_name,
    p.last_name,
    u.email
FROM patients p
JOIN users u ON p.user_id = u.id
WHERE 1=1
";

// Add search condition if search term is provided
if (!empty($search_term)) {
    $query .= " AND (p.first_name LIKE '%$search_term%' OR p.last_name LIKE '%$search_term%' OR u.email LIKE '%$search_term%')";
}

$query .= " ORDER BY p.last_name, p.first_name";

$result = $conn->query($query);

$patients = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Patients</title>
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

    <!-- Search Form -->
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6">
        <form method="GET" class="w-full flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
            <input 
                type="text" 
                name="search" 
                placeholder="Search patient by name/email" 
                value="<?= htmlspecialchars($search_term) ?>"
                class="flex-grow p-2 border rounded"
            >
            
            <div class="flex space-x-2">
                <button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-700 transition">
                    Search
                </button>
                
                <?php if (!empty($search_term)): ?>
                    <a href="<?= $_SERVER['PHP_SELF'] ?>" class="bg-gray-200 text-gray-800 px-4 py-2 rounded hover:bg-gray-300 transition">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Patient List Section -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Patient List</h1>
        <a href="add_patient.php" class="px-4 py-2 rounded text-sm bg-black text-white hover:bg-gray-700 transition">
            Add New Patient
        </a>
    </div>

    <!-- Patients Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full table-auto border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="p-4 text-left">Patient Name</th>
                    <th class="p-4 text-left">Email</th>
                    <th class="p-4 text-center">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white">
                <?php if (empty($patients)): ?>
                    <tr>
                        <td colspan="3" class="text-center py-4">No patients found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $patient): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-4">
                                <?= htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']) ?>
                            </td>
                            <td class="p-4">
                                <?= htmlspecialchars($patient['email']) ?>
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <a href="view_patientInfo.php?id=<?= $patient['id'] ?>" 
                                       class="bg-black text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition">
                                        View Details
                                    </a>
                                    <a href="?action=delete&id=<?= $patient['id'] ?>" 
                                       class="bg-black text-white px-3 py-1 rounded text-sm hover:bg-gray-700 transition"
                                       onclick="return confirm('Are you sure you want to delete this patient?');">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="../assets/script.js"></script>
</body>
</html>
<?php 
// Close the database connection
$conn->close();
?>