<?php
session_start();
require_once "../database/db_connection.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

$user_id = $_SESSION['user_id']; // Get user ID from session

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $uploadDirectory = 'uploads/';  // Directory to store the uploaded files
    $fileName = basename($_FILES['file']['name']);
    $filePath = $uploadDirectory . $fileName;

    // Validate file type (allow PDFs and image files)
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    if (in_array($_FILES['file']['type'], $allowedTypes)) {
        // Move the uploaded file to the uploads directory
        if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
            // Store the file path in the database
            $test_id = $_POST['test_id']; // Get test ID from the form (adjust as needed)
            $status = $_POST['status'];   // Get status from the form (adjust as needed)
            $test_date = $_POST['test_date']; // Get test date from the form (adjust as needed)
            $test_expiry = $_POST['test_expiry']; // Get test expiry from the form (adjust as needed)

            $sql = "INSERT INTO medical_clearance (patient_id, test_id, status, test_date, test_expiry, file_path)
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $user_id, $test_id, $status, $test_date, $test_expiry, $filePath);
            $stmt->execute();
            $stmt->close();

            echo "File uploaded successfully!";
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "Invalid file type.";
    }
}
?>
u