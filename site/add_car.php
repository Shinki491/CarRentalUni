<?php
// index.php
session_start();
// Include configuration and storage
require_once 'config.php';
require_once 'storage.php';

// Initialize the storage
$carStorage = new Storage(new JsonIO('./db/cars.json'), true); 
$authority = $_SESSION['user']['authority'] ?? '';
$cars = $carStorage->findAll();
$errors = [];

$action = $_GET['action'] ?? '';

if ($authority !== 'admin'){
    header('Location: index.php');
}

if ( $action === 'add'){   
    foreach ($cars as $car){
        $lastID = $car['id'];
    }

    $newCar = [
        'id' => $lastID,
        'brand' => $_GET['brand'] ?? '',
        'model' => $_GET['model'] ?? '',
        'year' => $_GET['year'] ?? '',
        'transmission' => $_GET['transmission'] ?? '',
        'fuel_type' => $_GET['fuel_type'] ?? '',
        'passengers' => $_GET['passengers'] ?? '',
        'daily_price_huf' => $_GET['daily_price_huf'] ?? '',
        'image' => $_GET['image'] ?? ''
    ];

    foreach ($newCar as $key => $value){
        if ($value === '' && !empty($errors)){
            $errors[] = 'All fields must be filled!';
        }
    }

    if ($newCar['year'] > 2025 || $newCar['year'] < 1900){
        $errors[] = 'Set a valid Year!';
    }

    if ($newCar['passengers'] < 1){
        $errors[] = 'Set a valid number of passengers!';
    }

    if (empty($errors)){
        $carStorage->add($newCar);
    }
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
        <h1>Add a car.</h1>
        <?php if (isset($_SESSION['user'])): ?>
            <a href="index.php">Home</a>
            <a href="profile.php">Profile</a>
            <a href="auth.php?action=logout">Logout</a>
        <?php else: ?>
            <a href="auth.php?action=login">Login</a>
            <a href="auth.php?action=register">Register</a>
        <?php endif; ?>
    </header>

    <main>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <section id="filters">
            <h2>New Car Information</h2>
            <div class="form-container">
                <form method="GET">
                    <div>
                        <label for="brand">Brand:</label>
                        <input type="text" name="brand" id="brand">

                        <label for="model">Model:</label>
                        <input type="text" name="model" id="model">

                        <label for="year">Year:</label>
                        <input type="number" name="year" id="year">

                        <label for="transmission">Transmission:</label>
                        <select name="transmission" id="transmission">
                            <option value="Automatic">Automatic</option>
                            <option value="Manual">Manual</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="fuel_type">Fuel type:</label>
                        <select name="fuel_type" id="fuel_type">
                            <option value="Petrol">Petrol</option>
                            <option value="Electric">Electric</option>
                        </select>

                        <label for="passengers">Passengers:</label>
                        <input type="number" name="passengers" id="passengers" min="1">

                        <label for="daily_price_huf">Daily Price (HUF):</label>
                        <input type="number" name="daily_price_huf" id="daily_price_huf" min="0">

                        <label for="image">Image Link:</label>
                        <input type="text" name="image" id="image">
                    </div>
                
                    <button type="submit" id="add_car" name="action" value="add">Add Car</button>
                    <button type="reset" id="reset" name="action" value="reset">Reset Fields</button>
                    
                </form>
            </div>
        </section>

    </main>
</body>
</html>
