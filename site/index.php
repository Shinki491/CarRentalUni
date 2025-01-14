<?php

session_start();

require_once 'config.php';
require_once 'storage.php';


$bookingStorage = new Storage(new JsonIO('db/bookings.json'));
$carStorage = new Storage(new JsonIO('./db/cars.json'), true); 
$authority = $_SESSION['user']['authority'] ?? '';
$delete = $_GET['action'] ?? '';
$deleteID = $_GET['id'] ?? '';

$filters = [
    'transmission' => $_GET['transmission'] ?? null,
    'fuel_type' => $_GET['fuel_type'] ?? null,
    'passengers' => $_GET['passengers'] ?? null,
    'price_min' => $_GET['price_min'] ?? null,
    'price_max' => $_GET['price_max'] ?? null,
    'start_date' => $_GET['start_date'] ?? null,
    'end_date' => $_GET['end_date'] ?? null,
];


$cars = $carStorage->findCarsByFilter($filters, $bookingStorage); 

if ($delete === 'delete' && $authority === 'admin'){
    $carStorage->delete($deleteID - 1);
    header('Location: /');
}

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
        <h1>Welcome to IKarRental, the #1 Car Rental Service</h1>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="profile.php">Profile</a>
            <a href="auth.php?action=logout">Logout</a>
        <?php else: ?>
            <a href="auth.php?action=login">Login</a>
            <a href="auth.php?action=register">Register</a>
        <?php endif; ?>
    </header>

    <main>
        <section id="filters">
            <h2>Filter Cars</h2>
            <div class="form-container">
                <form method="GET">
                    <label for="transmission">Transmission:</label>
                    <select name="transmission" id="transmission">
                        <option value="">Any</option>
                        <option value="Automatic">Automatic</option>
                        <option value="Manual">Manual</option>
                    </select>

                    <label for="fuel_type">Fuel type:</label>
                    <select name="fuel_type" id="fuel_type">
                        <option value="">Any</option>
                        <option value="Petrol">Petrol</option>
                        <option value="Electric">Electric</option>
                    </select>

                    <label for="passengers">Passengers:</label>
                    <input type="number" name="passengers" id="passengers" min="1">

                    <label for="price_min">Min Price:</label>
                    <input type="number" name="price_min" id="price_min" min="0">

                    <label for="price_max">Max Price:</label>
                    <input type="number" name="price_max" id="price_max" min="0">

                    <label for="start_date">Start Date:</label>
                    <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>">

                    <label for="end_date">End Date:</label>
                    <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>">

                    <button type="submit">Apply Filters</button>
                </form>

                <?php if ($authority === 'admin'): ?>
                    <form method="GET" action="add_car.php">
                        <button type="submit" id="admin-action" class="admin-btn">Add Car</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <section id="car-list">
            <h2>Available Cars</h2>
            <?php if (empty($cars)): ?>
                <p>No cars found. Try adjusting your filters.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($cars as $car): ?>
                        <li>
                            <a href="details.php?id=<?= ($car['id']) ?>">
                                <img src="<?= ($car['image']) ?>" alt="<?= ($car['brand'] . ' ' . $car['model']) ?>">
                            </a>
                            <a href="details.php?id=<?= ($car['id']) ?>">
                                <h3><?= ($car['brand'] . ' ' . $car['model']) ?></h3>
                            </a>
                            <p>Passengers: <?= ($car['passengers']) ?></p>
                            <p>Transmission: <?= ($car['transmission']) ?></p>
                            <p>Daily Price: <?= ($car['daily_price_huf']) ?> HUF</p>

                            <?php if ($authority === 'admin'): ?>
                            <a href="details.php?action=update&id=<?= $car['id'] ?>" class="btn btn-update">Update</a>
                            <a href="index.php?action=delete&id=<?= $car['id'] ?>" class="btn btn-delete">Delete</a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
