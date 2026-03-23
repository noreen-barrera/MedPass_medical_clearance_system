<?php
// Insert php code
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" href="../assets/style.css"> <!-- Link external CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
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

    <!-- Main Admin Dashboard -->
    <main class="admin-dashboard px-8 py-12 mt-16">
        <!-- Header -->
        <div class="dashboard-header text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-800">Welcome to the Admin Dashboard</h1>
            <p class="mt-4 text-lg text-gray-600">
                <strong class="text-gray-700">For administrators only:</strong> Manage patients, add new records, and oversee test results.
            </p>
        </div>

        <!-- Feature Container -->
        <div class="feature-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <!-- View Patients -->
            <div class="feature-card bg-white rounded-lg shadow-lg p-8 transition hover:shadow-xl">
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                    👩‍⚕️ <span class="ml-2">View Patients</span>
                </h2>
                <p class="mt-4 text-gray-600">Access the list of all registered patients. You can view their medical status, personal details, and history.</p>
                <a href="view_patient.php" class="btn inline-block mt-6 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                    Go to Patients
                </a>
            </div>

            <!-- Add New Patient -->
            <div class="feature-card bg-white rounded-lg shadow-lg p-8 transition hover:shadow-xl">
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                    ➕ <span class="ml-2">Add New Patient</span>
                </h2>
                <p class="mt-4 text-gray-600">Register a new patient into the system by entering their full personal details and other relevant data accurately.</p>
                <a href="add_patient.php" class="btn inline-block mt-6 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700  transition">
                    Add Patient
                </a>
            </div>

            <!-- Manage Test Results -->
            <div class="feature-card bg-white rounded-lg shadow-lg p-8 transition hover:shadow-xl">
                <h2 class="text-2xl font-semibold text-gray-800 flex items-center">
                    📝 <span class="ml-2">Manage Test Results</span>
                </h2>
                <p class="mt-4 text-gray-600">Review, edit, and update patient test results. Ensure the records are accurate and up-to-date.</p>
                <a href="manage_results.php" class="btn inline-block mt-6 bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700  transition">
                    Manage Results
                </a>
            </div>

        </div>
    </main>

</body>
</html>

