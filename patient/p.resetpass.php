<?php
require_once "../database/db_connection.php";

// Check if the token is passed via URL (GET request)
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Query the database to check if the token exists and is not expired
    $stmt = $conn->prepare("SELECT email, reset_expires FROM users WHERE reset_token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    // Check if the token exists in the database
    if ($stmt->num_rows == 0) {
        die("Invalid or expired token.");
    }

    // Fetch the associated email and reset expiration time
    $stmt->bind_result($email, $reset_expires);
    $stmt->fetch();
    
    // Check if the token has expired
    if (strtotime($reset_expires) < time()) {
        die("The token has expired.");
    }

    // If everything is valid, show the reset password form
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the new password and confirm password from the form
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        // Debugging: Check the entered passwords
        var_dump($new_password, $confirm_password);
        
        // Validate the new password and confirm password match
        if ($new_password !== $confirm_password) {
            die("Passwords do not match.");
        }

        // Hash the new password
        //$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Debugging: Check if the password is being hashed
        //var_dump($hashed_password);

        // Update the password in the database
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
        $stmt->bind_param("ss", $new_password, $email); //change new to hashed
        $stmt->execute();

        // Debugging: Check if the update was successful
        if ($stmt->affected_rows > 0) {
            echo "Password updated successfully. Redirecting to login...";
            header("Location: p.dashboard.php"); // Redirect to login page
            exit();
        } else {
            echo "Error: Password not updated.";
        }
    }
} else {
    die("No token provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedPass - Reset Password</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="header">
        <div class="logo">
            <a href="p.login.php">
                <span class="logo-black">Med</span><span class="logo-green">Pass</span>
            </a>
        </div>
        <a href="p.login.php" class="btn-login-header">Login</a>
    </div>

    <div class="login-container">
        <div class="main-logo">
            <span class="logo-black">Med</span><span class="logo-green">Pass</span>
        </div>
        <div class="forgot-password-container">
            <h2>Reset Password</h2>
        </div>
        <p class="subtitle small-text">Enter your new password.</p>

        <form action="p.resetpass.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" name="new_password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
            </div>
            <button type="submit" class="btn-login">Reset Password</button>
        </form>

    </div>
</body>
</html>
