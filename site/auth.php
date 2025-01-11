<?php
// auth.php

require_once 'config.php';
require_once 'storage.php';

// Initialize user storage (JSON)
$userStorage = new Storage(new JsonIO('db/users.json'));

// Determine action
$action = $_GET['action'] ?? 'login'; // Default to 'login'
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        // Login Logic
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $userStorage->findOne(['username' => $username]);
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            session_start();
            echo "session started";
            $_SESSION['user'] = $user;
            header('Location: index.php'); // Redirect to main page
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    } elseif ($action === 'register') {
        // Registration Logic
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($username) || empty($password) || empty($confirmPassword)) {
            $errors[] = 'All fields are required.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } elseif ($userStorage->findOne(['username' => $username])) {
            $errors[] = 'Username already exists.';
        } else {
            // Save user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userStorage->add(['username' => $username, 'password' => $hashedPassword]);
            header('Location: auth.php?action=login'); // Redirect to login page
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
    <title><?= ucfirst($action) ?> - Car Rental Service</title>
</head>
<body>
    <header>
        <h1><?= $action === 'login' ? 'Login' : 'Register' ?></h1>
        <a href="auth.php?action=login">Login</a> | 
        <a href="auth.php?action=register">Register</a>
    </header>

    <main>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li style="color: red;"><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="auth.php?action=<?= htmlspecialchars($action) ?>">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <?php if ($action === 'register'): ?>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
            <?php endif; ?>

            <button type="submit"><?= $action === 'login' ? 'Login' : 'Register' ?></button>
        </form>
    </main>
</body>
</html>
