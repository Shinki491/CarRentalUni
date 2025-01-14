<?php

require_once 'storage.php';
session_start();


if (!isset($_SESSION['user'])) {
    header('Location: auth.php?action=login'); 
    exit;
}

$user = $_SESSION['user'];

$carStorage = new Storage(new JsonIO('./db/cars.json'), true); 
$bookingStorage = new Storage(new JsonIO('db/bookings.json'));
$bookings = $bookingStorage->findAll();
$user_bookings = [];
$delete = $_GET['action'] ?? '';
$deleteID = $_GET['id'] ?? '';

foreach ($bookings as $booking){
    if ($booking['email'] === $user['email']){
        $user_bookings[] = $booking;
    }
}

if ($delete === 'delete' && $user['authority'] === 'admin'){
    $bookingStorage->delete($deleteID - 1);
    header('Location: profile.php');
}

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
        <a href="auth.php?action=logout">Logout</a>
    </header>

    <main>
        <section id="profile-info">
            <h2>Profile Details</h2>
            <p><strong>Name:</strong> <?= ($user['name']) ?></p>
            <p><strong>Email:</strong> <?= ($user['email']) ?></p>
            
            <?php if ($user['authority'] === 'user'): ?>
                <section id="car-list">
                    <h2>Your bookings</h2>
                    <?php if (empty($user_bookings)): ?>
                        <p>No bookings found.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($user_bookings as $booking): ?>
                                <li>
                                    <?php $car = $carStorage->findById($booking['car_id'] - 1); ?>
                                    <a href="details.php?id=<?= $car['id'] ?>">
                                        <img src="<?= ($car['image']) ?>" alt="<?= ($car['brand'] . ' ' . $car['model']) ?>">
                                    </a>
                                    <a href="details.php?id=<?= ($car['id']) ?>">
                                        <h3><?= ($car['brand'] . ' ' . $car['model']) ?></h3>
                                    </a>
                                    <p>From: <?= ($booking['start_date']) ?> till <?= ($booking['end_date']) ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

            <?php if ($user['authority'] === 'admin'): ?>
                <section id="car-list">
                    <h2>All bookings</h2>
                    <?php if (empty($bookings)): ?>
                        <p>No bookings found.</p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($bookings as $booking): ?>
                                <li>
                                    <?php $car = $carStorage->findById($booking['car_id'] - 1); ?>
                                    <a href="details.php?id=<?= $car['id'] ?>">
                                        <img src="<?= ($car['image']) ?>" alt="<?= ($car['brand'] . ' ' . $car['model']) ?>">
                                    </a>
                                    <a href="details.php?id=<?= ($car['id']) ?>">
                                        <h3><?= ($car['brand'] . ' ' . $car['model']) ?></h3>
                                    </a>
                                    <p>From: <?= ($booking['start_date']) ?> till <?= ($booking['end_date']) ?></p>
                                    <a href="profile.php?action=delete&id=<?= $booking['id'] ?>" class="btn btn-delete">Delete</a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </section>
            <?php endif; ?>

        </section>
    </main>
</body>
</html>
