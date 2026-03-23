<?php
session_start();
require_once "../database/db_connection.php";

$error = ""; // Variable to store error message

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $user_id = trim($_POST["user_id"]);
    $password = trim($_POST["password"]);

    // Prepare statement to prevent SQL Injection
    $stmt = $conn->prepare("
        SELECT users.id, users.password, users.role, admins.first_name 
        FROM users 
        INNER JOIN admins ON users.id = admins.user_id 
        WHERE users.id = ?
    ");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $role, $name);
        $stmt->fetch();

        // Check password
        if ($password === $hashed_password) {
            $_SESSION["admin_id"] = $id;
            $_SESSION["user_id"] = $user_id;
            $_SESSION["role"] = $role;
            $_SESSION["name"] = $name;

            // Redirect to admin dashboard if login successful
            header("Location: admin_dashboard.php");
            exit();
        } else {
            $error = "Invalid user ID or password.";
        }
    } else {
        $error = "User not found.";
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
  <title>Admin Login</title>
  <link rel="stylesheet" href="admin_login_styles.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>

<body>

<div class="wrapper">
  <form id="loginForm" method="POST">
    <h1>Med<span class="pass_green">Pass</span> Admin Login</h1>

    <?php if ($error): ?>
      <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <div class="p_input">
      <input type="text" id="user_id" name="user_id" placeholder="User ID" required>
    </div>

    <div class="p_input">
      <input type="password" id="password" name="password" placeholder="Password" required>
    </div>

    <button type="submit" class="login_btn">Login</button>
  </form>
</div>

</body>
</html>
