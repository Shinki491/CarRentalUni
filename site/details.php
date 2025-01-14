<?php
session_start();
require_once 'config.php';
require_once 'storage.php';


$carStorage = new Storage(new JsonIO('./db/cars.json'));
$bookingStorage = new Storage(new JsonIO('db/bookings.json'));

$user = $_SESSION['user'];
$authority = $user['authority'] ?? '';

$id = $_GET['id'] ?? null;
$car = $carStorage->findById($id - 1);
$bookings = $bookingStorage->findAll();
$action = $_GET['action'] ?? '';
$errors = [];

if (isset($_GET['action'])){
    if ($_GET['action'] === 'update_car' && $authority === 'admin'){
        $updCar = [
            'id' => $id,
            'brand' => $_GET['brand'] ?? '',
            'model' => $_GET['model'] ?? '',
            'year' => $_GET['year'] ?? '',
            'transmission' => $_GET['transmission'] ?? '',
            'fuel_type' => $_GET['fuel_type'] ?? '',
            'passengers' => $_GET['passengers'] ?? '',
            'daily_price_huf' => $_GET['daily_price_huf'] ?? '',
            'image' => $_GET['image'] ?? ''
        ];
    
        foreach ($updCar as $key => $value){
            if ($value === '' && !empty($errors)){
                $errors[] = 'All fields must be filled!';
            }
        }
    
        if ($updCar['year'] > 2025 || $updCar['year'] < 1900){
            $errors[] = 'Set a valid Year!';
        }
    
        if ($updCar['passengers'] < 1){
            $errors[] = 'Set a valid number of passengers!';
        }
    
        if (empty($errors)){
            $carStorage->update($id - 1,$updCar);
            $goTo = 'Location: details.php?id=' . $id;
            header($goTo);
        }
    } elseif ($action === 'book_car'){
        $lastID = 0;
        foreach ($bookings as $booking){
            $lastID = $booking['id'];
        }

        $newBooking = [
            'id' => $lastID,
            'start_date' => $_GET['start_date'] ?? '',
            'end_date' => $_GET['end_date'] ?? '',
            'email' => $_SESSION['user']['email'] ?? '',
            'car_id' => $_GET['id'] ?? ''
        ];
        echo "i was here";
        if (empty($newBooking['start_date']) || empty($newBooking['end_date']) || empty($newBooking['email']) || empty($newBooking['car_id'])) {
            $errors[] = 'Something wrong with the data.';
        } elseif ($newBooking['start_date'] > $newBooking['end_date']) {
            $errors[] = 'End date must be after start date.';
        }
        
        foreach ($bookings as $booking) {
            if ($booking['car_id'] == $newBooking['car_id']) {
                $existingStart = $booking['start_date'];
                $existingEnd = $booking['end_date'];
                $newStart = $newBooking['start_date'];
                $newEnd = $newBooking['end_date'];
    
                if (
                    ($newStart <= $existingEnd && $newStart >= $existingStart) ||  
                    ($newEnd <= $existingEnd && $newEnd >= $existingStart) ||    
                    ($newStart <= $existingStart && $newEnd >= $existingEnd)    
                ) {
                    $errors[] = 'This car is already booked for the selected dates.';
                    break;
                }
            }
        }

        if (empty($errors)) {
           
            $bookingStorage->add($newBooking);
            header('Location: booking.php?status=success&id=' . $_GET['id'] . "&start=" . $_GET['start_date'] . "&end=" . $_GET['end_date']);
            exit;
        } else {
           
            header('Location: booking.php?status=failure&id=' . $_GET['id'] . "&start=" . $_GET['start_date'] . "&end=" . $_GET['end_date']);
            exit;
        }
        
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>IKarRental - <?= ($car['brand'] . ' ' . $car['model']) ?></title>
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
        <?php if ($action === ''): ?>
            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p class="error"><?= ($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <div>
                <section id="car-details">
                    <div class="car-image">
                        <img src="<?= $car['image'] ?>" alt="<?= ($car['brand'] . ' ' . $car['model']) ?>">
                    </div>
                    <div class="car-info">
                        <h2><?= ($car['brand'] . ' ' . $car['model']) ?></h2>
                        <p><strong>Year:</strong> <?= ($car['year']) ?></p>
                        <p><strong>Transmission:</strong> <?= $car['transmission'] ?></p>
                        <p><strong>Fuel Type:</strong> <?= $car['fuel_type'] ?></p>
                        <p><strong>Passengers:</strong> <?= $car['passengers'] ?></p>
                        <p><strong>Daily Price:</strong> <?= $car['daily_price_huf'] ?> HUF</p>
                    </div>
                </section>

                <?php if ($authority === 'user'): ?>
                <div class="form-container">
                    <form method="GET">
                        <input type="hidden" name="id" value="<?= $car['id'] ?>">
                        
                        <label for="start_date">Start Date:</label>
                        <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" required>

                        <label for="end_date">End Date:</label>
                        <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>" required>
                        
                        <button type="submit" id="book_car" name="action" value="book_car">Book</button>
                    </form>
                </div>
                <?php endif; ?>

            </div>
        <?php if ($authority === 'admin'): ?>
        <a href="details.php?action=update&id=<?= $_GET['id'] ?>" class="btn btn-update">Update</a>
        <?php endif; ?>
        <?php endif; ?>
        
        <?php if ($action === 'update' && $authority === 'admin'): ?>
            <div class="form-container">
                <form method="GET">
                    <div>
                        <input type="hidden" name="id" value="<?= $car['id'] ?>">
                        <label for="brand">Brand:</label>
                        <input type="text" name="brand" id="brand" value="<?= $car['brand'] ?>">

                        <label for="model">Model:</label>
                        <input type="text" name="model" id="model" value="<?= $car['model'] ?>">

                        <label for="year">Year:</label>
                        <input type="number" name="year" id="year" value="<?= $car['year'] ?>">

                        <label for="transmission">Transmission:</label>
                        <select name="transmission" id="transmission">
                            <option value="Automatic" <?= ($car['transmission'] === "Automatic") ? 'selected' : '' ?> >Automatic</option>
                            <option value="Manual" <?= ($car['transmission'] === "Manual") ? 'selected' : '' ?>>Manual</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="fuel_type">Fuel type:</label>
                        <select name="fuel_type" id="fuel_type">
                            <option value="Petrol" <?= ($car['fuel_type'] === "Petrol") ? 'selected' : '' ?>>Petrol</option>
                            <option value="Electric" <?= ($car['fuel_type'] === "Electric") ? 'selected' : '' ?>>Electric</option>
                        </select>

                        <label for="passengers">Passengers:</label>
                        <input type="number" name="passengers" id="passengers" min="1" value="<?= $car['passengers'] ?>">

                        <label for="daily_price_huf">Daily Price (HUF):</label>
                        <input type="number" name="daily_price_huf" id="daily_price_huf" min="0" value="<?= $car['daily_price_huf'] ?>">

                        <label for="image">Image Link:</label>
                        <input type="text" name="image" id="image" value="<?= $car['image'] ?>">
                    </div>
                
                    <button type="submit" id="update_car" name="action" value="update_car">Update Car</button>
                    <button type="reset" id="reset" name="reset" value="reset">Reset Fields</button>
                    
                </form>
            </div>
        <?php endif; ?>
    
    </main>
</body>
</html>
