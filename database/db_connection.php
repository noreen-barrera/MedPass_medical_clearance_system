<?php
$host = "localhost";  // Host (localhost for XAMPP)
$username = "root";   // Default XAMPP username
$password = "";        // No password for XAMPP
$database = "medpass_db";  // Database name

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//echo "Connected successfully"; // Uncomment to check connection
?>
