<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "patient") {
    header("Location: p.login.php"); // Redirect to login if not a patient
    exit();
}

$currentPage = 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MedPass - Dashboard</title>
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
        <h1>Welcome, <?= htmlspecialchars($_SESSION["first_name"]); ?>!</h1>
        <p class="subtitle">Easily track and download your medical clearance results</p>
        <a href="p.profile.php" class="view-profile-btn">View Profile</a>
        
        <div class="illustration">
            <img src="../assets/dashboard.png" alt="Medical professionals and patients" width="100%">
        </div>
    </main>
</body>
</html>
