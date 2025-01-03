<?php
// index.php

// Include configuration and storage
require_once 'config.php';
require_once 'storage.php';

// Initialize the storage
$carStorage = new Storage($db); // Assuming $db is the database connection from config.php

// Fetch filters from GET parameters
$filters = [
    'transmission' => $_GET['transmission'] ?? null,
    'passengers' => $_GET['passengers'] ?? null,
    'price_min' => $_GET['price_min'] ?? null,
    'price_max' => $_GET['price_max'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date' => $_GET['end_date'] ?? null,
];

// Fetch cars with filters
$cars = $carStorage->findCarsByFilter($filters); // Define this method in storage.php

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Car Rental Service</title>
</head>
<body>
    <header>
        <h1>Welcome to the Car Rental Service</h1>
        <a href="auth.php?action=login">Login</a>
        <a href="auth.php?action=register">Register</a>
    </header>

    <main>
        <section id="filters">
            <h2>Filter Cars</h2>
            <form method="GET">
                <label for="transmission">Transmission:</label>
                <select name="transmission" id="transmission">
                    <option value="">Any</option>
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>

                <label for="passengers">Passengers:</label>
                <input type="number" name="passengers" id="passengers" min="1">

                <label for="price_min">Min Price:</label>
                <input type="number" name="price_min" id="price_min" min="0">

                <label for="price_max">Max Price:</label>
                <input type="number" name="price_max" id="price_max" min="0">

                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" id="start_date">

                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" id="end_date">

                <button type="submit">Apply Filters</button>
            </form>
        </section>

        <section id="car-list">
            <h2>Available Cars</h2>
            <?php if (empty($cars)): ?>
                <p>No cars found. Try adjusting your filters.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($cars as $car): ?>
                        <li>
                            <img src="<?= htmlspecialchars($car['image']) ?>" alt="<?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?>">
                            <h3><?= htmlspecialchars($car['brand'] . ' ' . $car['model']) ?></h3>
                            <p>Passengers: <?= htmlspecialchars($car['passengers']) ?></p>
                            <p>Transmission: <?= htmlspecialchars($car['transmission']) ?></p>
                            <p>Daily Price: <?= htmlspecialchars($car['daily_price_huf']) ?> HUF</p>
                            <a href="details.php?id=<?= htmlspecialchars($car['id']) ?>">View Details</a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
