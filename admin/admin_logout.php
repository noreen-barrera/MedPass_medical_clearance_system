<?php
// Only execute logout if confirmed
if (isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    session_start();
    session_destroy(); 
    header("Location: admin_login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
    <script>
        // Execute when page loads
        window.onload = function() {
            if (confirm("Are you sure you want to log out?")) {
                // User clicked "OK", redirect to this same page with confirmation parameter
                window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?confirm=yes";
            } else {
                // User clicked "Cancel", redirect back to previous page or dashboard
                window.history.back();
                // Alternative: Redirect to dashboard
                // window.location.href = "dashboard.php";
            }
        };
    </script>
</head>
<body>
</body>
</html>
