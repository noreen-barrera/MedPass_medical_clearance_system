<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Adjust path if needed

session_start(); // Start the session to store success messages
require_once "../database/db_connection.php";

date_default_timezone_set('Asia/Manila');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Check if email exists in the database
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Generate a unique token
        $token = bin2hex(random_bytes(32));
        //$expires = date("Y-m-d H:i:s", strtotime("+20 minutes")); // Token expires in 1 hour
        $expires = date("Y-m-d H:i:s", strtotime("+24 hours"));
        // Store the token in the database
        $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
        $stmt->bind_param("sss", $token, $expires, $email);
        $stmt->execute();

        // Create the password reset link
        $reset_link = "http://localhost:3000/patient/p.resetpass.php?token=$token";

        // Send email using PHPMailer
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'bjrn.png@gmail.com';
            $mail->Password = 'medh dxth effk rosv';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Email content
            $mail->setFrom('bjrn.png@gmail.com', 'MedPass');
            $mail->addAddress($email);
            $mail->Subject = 'Reset Your MedPass Password';
            $mail->Body = "Click the link below to reset your password. This link will expire in 1 hour.\n\n$reset_link";

            if ($mail->send()) {
                // Store the success message in session to display on the form page
                $_SESSION['reset_message'] = "<span class='reset-message'>Password reset link sent to your email.</span>";
            } else {
                $_SESSION['reset_message'] = "<span class='reset-message'>Failed to send email.</span>";
            } 
            } catch (Exception $e) {
                $_SESSION['reset_message'] = "<span class='reset-message'>Email error: {$mail->ErrorInfo}</span>";
            }
            } else {
                $_SESSION['reset_message'] = "<span class='reset-message'>Email address not found.</span>";
            }
            
            
    // Redirect back to the forgot password page to display the message
    header("Location: p.forgotpass.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedPass - Forgot Password</title>
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
            <h2>Forgot Password</h2>
        </div>
        <p class="subtitle small-text">Enter your email to reset your password.</p>

        <!-- Display success/error message -->
        <?php
        if (isset($_SESSION['reset_message'])) {
            echo "<p class='message'>" . $_SESSION['reset_message'] . "</p>";
            unset($_SESSION['reset_message']); // Clear the message after displaying it
        }
        ?>

        <form action="p.forgotpass.php" method="POST">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <button type="submit" class="btn-login">Send Password Reset Link</button>
        </form>

    </div>
</body>
</html>
