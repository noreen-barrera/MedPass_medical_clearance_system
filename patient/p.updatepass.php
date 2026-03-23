<?php
require_once "../database/db_connection.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["token"])) {
    $token = $_POST["token"];
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Validate token in DB
    $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_expires > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("Invalid or expired token.");
    }

    // Fetch associated email
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    // Hash new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password in database
    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
    $stmt->bind_param("ss", $hashed_password, $email);
    if ($stmt->execute()) {
        echo "Password successfully reset. Redirecting...";
        header("Refresh: 2; URL=p.dashboard.php");
    } else {
        echo "Error updating password.";
    }
} else {
    die("Unauthorized access.");
}

?>
