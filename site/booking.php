<?php
session_start();
require_once 'config.php';
require_once 'storage.php';

$carStorage = new Storage(new JsonIO('./db/cars.json'));
$id = $_GET['id'] ?? null;
$car = $carStorage->findById($id - 1);

$status = $_GET['status'] ?? 'failure';

$message = ($status === 'success') 
    ? "Booking " . $car['brand'] . ' ' . $car['model'] . " from " . $_GET['start'] . ' till ' . $_GET['end'] . " was successful! See the booking on your profile."  
    : "Booking " . $car['brand'] . ' ' . $car['model'] . " from " . $_GET['start'] . ' till ' . $_GET['end'] . " failed! Try again with different dates.";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Booking Status</title>
</head>
<body>
    <header>
        <h1>IKarRentals</h1>
        <a href="index.php">Back to Listings</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="profile.php">Profile</a>
            <a href="auth.php?action=logout">Logout</a>
        <?php endif; ?>
    </header>

    <main>
        <div class="status-message">
            <h2><?= ($message) ?></h2>
            <?php if ($status === 'success'): ?>
                <p>Your booking details are now recorded. You can manage your bookings in your profile.</p>
            <?php else: ?>
                <p>There was an issue with your booking. Please try again or contact support.</p>
            <?php endif; ?>
            <a href="index.php" class="btn">Return to Listings</a>
        </div>
    </main>
</body>
</html>
