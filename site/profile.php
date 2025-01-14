<?php
// profile.php

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: auth.php?action=login'); // Redirect to login page if not logged in
    exit;
}

// Retrieve user information from session
$user = $_SESSION['user'];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Profile - Car Rental Service</title>
</head>
<body>
    <header>
        <h1>Your Profile</h1>
        <a href="index.php">Home</a>
        <a href="logout.php">Logout</a>
    </header>

    <main>
        <section id="profile-info">
            <h2>Profile Details</h2>
            <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
            <!-- Add more fields as necessary -->
        </section>
    </main>
</body>
</html>
