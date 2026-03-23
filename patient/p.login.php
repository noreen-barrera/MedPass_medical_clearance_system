<?php
session_start();
require_once "../database/db_connection.php"; 

// Initialize error message variable
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Prepare statement to prevent SQL Injection
    $stmt = $conn->prepare("
        SELECT users.id, users.password, users.role, patients.first_name, patients.last_name 
        FROM users 
        LEFT JOIN patients ON users.id = patients.user_id 
        WHERE users.email = ?"
    );
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role, $first_name, $last_name);
        $stmt->fetch();

        if ($password === $hashed_password) {
            $_SESSION["user_id"] = $id;
            $_SESSION["email"] = $email;
            $_SESSION["role"] = $role;
            $_SESSION["first_name"] = $first_name;
            $_SESSION["last_name"] = $last_name;

            if ($role === "patient") {
                $_SESSION['message'] = "✅ Login successful! Redirecting to your dashboard...";
                header("Location: ../patient/p.dashboard.php");
                exit();
            }
        } else {
            $_SESSION['message'] = "❌ Incorrect password. Please try again.";
        }
    } else {
        $_SESSION['message'] = "❌ Email not found. Please check your email.";
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
    <title>MedPass - Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Style for the notification bar */
        .notification-bar {
            background-color: #f44336;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 16px;
            display: none;
            width: 100%;
            position: absolute;
            top: 60px; /* Adjust this value to position the bar below the MedPass logo */
            z-index: 1000;
        }

        .notification-bar.success {
            background-color: #4CAF50;
        }

        .notification-bar.error {
            background-color: #f44336;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <span class="logo-black">Med</span><span class="logo-green">Pass</span>
            </div>
        </div>
        
        <!-- Notification Bar -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="notification-bar <?php echo (strpos($_SESSION['message'], '❌') !== false) ? 'error' : 'success'; ?>">
                <?php echo $_SESSION['message']; ?>
            </div>
            <?php unset($_SESSION['message']); ?> <!-- Clear the message after displaying it -->
        <?php endif; ?>

        <div class="login-container">
            <div class="main-logo">
                <span class="logo-black">Med</span><span class="logo-green">Pass</span>
            </div>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" name="email" placeholder = "Email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" name="password" id="password"  placeholder = "Password" required>
                    <input type="checkbox" id="show-password" onclick="togglePassword()"> Show Password
                </div>

                <div class="forgot-password-container">
                    <a href="p.forgotpass.php" class="forgot-password">Forgot Password?</a>
                </div>

                <button type="submit" class="btn-login">Login</button>
            </form>
            
            <div class="contact-section">
                <p>Contact Us: medpass@gmail.com | 09123456789</p>
            </div>
        </div>
    </div>

    <script>
        // Display the notification bar if a message exists
        if (document.querySelector('.notification-bar')) {
            document.querySelector('.notification-bar').style.display = 'block';
            setTimeout(function() {
                document.querySelector('.notification-bar').style.display = 'none';
            }, 5000); // Hide after 5 seconds
        }

        function togglePassword() {
            var passwordField = document.getElementById("password");
            passwordField.type = passwordField.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
