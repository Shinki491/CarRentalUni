<?php
session_start();
require_once 'config.php';
require_once 'storage.php';

// Initialize the storage
$carStorage = new Storage(new JsonIO('./db/cars.json'));

// Get the car ID from the query string
$id = $_GET['id'] ?? null;

// Fetch the car by ID
$car = $carStorage->findById($id - 1);

if (!$car) {
    // Redirect to index.php if the car is not found
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>IKarRental - <?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></title>
</head>
<body>
    <header>
        <h1>IKarRentals</h1>
        <a href="index.php">Back to Listings</a>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="profile.php">Profile</a>
            <a href="auth.php?action=logout">Logout</a>
        <?php else: ?>
            <a href="auth.php?action=login">Login</a>
            <a href="auth.php?action=register">Register</a>
        <?php endif; ?>
    </header>

    <main>
        <section id="car-details">
            <div class="car-image">
                <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
            </div>
            <div class="car-info">
                <h2><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h2>
                <p><strong>Year:</strong> <?= htmlspecialchars($car['year']) ?></p>
                <p><strong>Transmission:</strong> <?= htmlspecialchars($car['transmission']) ?></p>
                <p><strong>Fuel Type:</strong> <?= htmlspecialchars($car['fuel_type']) ?></p>
                <p><strong>Passengers:</strong> <?= htmlspecialchars($car['passengers']) ?></p>
                <p><strong>Daily Price:</strong> <?= htmlspecialchars($car['daily_price_huf']) ?> HUF</p>
            </div>
        </section>
    </main>

    <style>
        /* Details page specific styles */
        #car-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }

        .car-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .car-info {
            flex: 1;
            max-width: 400px;
        }

        .car-info h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .car-info p {
            margin: 10px 0;
            font-size: 1rem;
            color: #555;
        }

        header a {
            margin: 0 10px;
            text-decoration: none;
            color: #333;
        }

        header a:hover {
            text-decoration: underline;
        }
    </style>
</body>
</html>
