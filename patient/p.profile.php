<?php
session_start();
require_once "../database/db_connection.php";

$currentPage = 'profile';

// Check if file_id is provided for download
if (isset($_GET['file_id'])) {
    $file_id = $_GET['file_id'];

    // Query to get the file path and name
    $sql = "SELECT file_path, file_name FROM medical_clearance WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $file_path = $row['file_path'];
        $file_name = $row['file_name'];

        // Ensure the file exists
        if (file_exists($file_path)) {
            // Force the download
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
            header('Content-Length: ' . filesize($file_path));
            readfile($file_path);
            exit;
        } else {
            echo "File not found.";
        }
    } else {
        echo "Invalid file ID.";
    }
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get user ID from session

// Fetch the patient ID using user_id
$sql = "SELECT p.id AS patient_id, p.first_name, p.last_name, p.dob, p.contact_number 
        FROM patients p
        JOIN users u ON p.user_id = u.id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo "User not found!<br>";
    exit();
}

// Fetch medical clearance records for the patient_id
$medical_sql = "SELECT mt.test_name, mc.status, mc.test_date, mc.test_expiry, mc.file_path, mc.file_name, mc.id AS file_id
                FROM medical_tests mt
                JOIN medical_clearance mc ON mt.id = mc.test_id
                WHERE mc.patient_id = ?";
$medical_stmt = $conn->prepare($medical_sql);
$medical_stmt->bind_param("i", $user['patient_id']);
$medical_stmt->execute();
$medical_result = $medical_stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedPass - Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <div class="header">
            <a href="p.dashboard.php" class="logo">
                <span class="logo-black">Med</span><span class="logo-green">Pass</span>
            </a>
            <nav>
                <a href="p.dashboard.php" class="<?= ($currentPage == 'dashboard') ? 'active' : ''; ?>">Dashboard</a>
                <a href="p.profile.php" class="<?= ($currentPage == 'profile') ? 'active' : ''; ?>">Profile</a>
                <button class="logout-btn" onclick="window.location.href='p.logout.php'">Logout</button>
            </nav>
        </div>
    </header>

    <main>
        <section class="profile-info">
            <h1>Profile Information</h1>
            <p><span class="label">Name:</span> <?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></p>
            <p><span class="label">Birthdate:</span> <?php echo htmlspecialchars($user['dob']); ?></p>
            <p><span class="label">Contact:</span> <?php echo htmlspecialchars($user['contact_number']); ?></p>
        </section>

        <section class="change-password">
            <button onclick="window.location.href='p.changepass.php'">Change Password</button>
        </section>

        <section class="medical-clearance">
            <h2>Medical Clearance Status</h2>
            <table>
                <thead>
                    <tr>
                        <th>Test Name</th>
                        <th>Status</th>
                        <th>Test Date</th>
                        <th>Test Expiry</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $medical_result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['test_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars($row['test_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['test_expiry']); ?></td>
                            <td>
                                <?php if (!empty($row['file_path'])) { ?>
                                    <a href="p.profile.php?file_id=<?php echo $row['file_id']; ?>" download>Download</a>
                                <?php } else { ?>
                                    No File
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </section>
    </main>
</body>
</html>

<?php
$medical_stmt->close();
$conn->close();
?>
