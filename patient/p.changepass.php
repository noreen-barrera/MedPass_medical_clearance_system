<?php
session_start();
require_once "../database/db_connection.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_SESSION['user_id']; 
    $current_password = $_POST['current-password'];
    $new_password = $_POST['new-password'];
    $confirm_password = $_POST['confirm-password'];

    // Fetch the user's current password from the database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        
        // Verify current password
        if ($current_password !== $stored_password) {
            echo "<script>alert('Incorrect current password.'); window.history.back();</script>";
            exit();
        }

        // Verify new password matches re-entered password
        if ($new_password !== $confirm_password) {
            echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
            exit();
        }

        // Check if new password is the same as the current password
        if ($new_password === $stored_password) {
            echo "<script>alert('You cannot use the same password.'); window.history.back();</script>";
            exit();
        }

        // Update the password
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_password, $user_id);

        if ($update_stmt->execute()) {
            echo "<script>alert('Password changed successfully!'); window.location.href = 'p.profile.php';</script>";
        } else {
            echo "<script>alert('Error updating password: " . $update_stmt->error . "'); window.history.back();</script>";
        }        

        $update_stmt->close();
    } else {
        echo "<script>alert('User not found.'); window.history.back();</script>";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedPass - Change Password</title>
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
    <div class="changepass-page">        
        <main>
            <h1>Change Password</h1>
            <p class="subtitle">Protect your account with a unique password at least 8 characters long.</p>
            
            <form action="p.changepass.php" method="POST" onsubmit="return validatePasswords();">
                <div class="form-group">
                    <input type="password" id="current-password" name="current-password" placeholder="Current Password">
                </div>
                
                <div class="form-group">
                    <input type="password" id="new-password" name="new-password" placeholder="New Password" required>
                </div>
                
                <div class="form-group">
                    <input type="password" id="confirm-password" name="confirm-password" placeholder="Re-enter New Password" required>
                </div>
                
                <div class="buttons">
                    <button type="submit" class="save-btn">Save</button>
                    <button type="button" class="cancel-btn" onclick="confirmCancel()">Cancel</button>
                </div>
            </form>
        </main>
    </div>
    <script>
    function validatePasswords() {
        let newPass = document.getElementById('new-password').value;
        let confirmPass = document.getElementById('confirm-password').value;

        if (newPass !== confirmPass) {
            alert('Passwords do not match!');
            return false;
        }

        return true;
    }
    function confirmCancel() {
        if (confirm('Are you sure you want to cancel? Your changes will not be saved.')) {
            window.location.href = 'p.dashboard.php';
        }
    }
    </script>
</body>

</html>
