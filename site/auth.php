<?php

session_start();
require_once 'config.php';
require_once 'storage.php';


$userStorage = new Storage(new JsonIO('db/users.json'));


$action = $_GET['action'] ?? 'login'; 
$errors = [];

if ($action === 'logout') {
    session_unset();
    session_destroy();
    header('Location: auth.php'); 
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
   
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $user = $userStorage->findOne(['email' => $email]);
        if ($user && password_verify($password, $user['password'])) {
          
            session_start();
            echo "session started";
            $_SESSION['user'] = $user;
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Invalid email or password.';
        }
    } elseif ($action === 'register') {
       
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password) || empty($name)) {
            $errors[] = 'All fields are required.';
        } elseif ($userStorage->findOne(['email' => $email])) {
            $errors[] = 'Email already exists.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password too weak, add more characters.';
        } else {
            
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userStorage->add(['name' => $name, 'email' => $email, 'password' => $hashedPassword, 'authority' => 'user']);
            header('Location: auth.php?action=login'); 
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
        <h1 >IKarRental - <?= ucfirst($action) ?></h1>
        <a href="index.php">Home</a>
        <?php if ($action === 'register'): ?>
        <a href="auth.php?action=login">Login</a>
        <?php elseif ($action === 'login'): ?>
        <a href="auth.php?action=register">Register</a>
        <?php endif; ?>
    </header>

    <main>
        <?php if (!empty($errors)): ?>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= ($error) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <form method="POST" action="auth.php?action=<?= ($action) ?>">
            <?php if ($action === 'register'): ?>
                <label for="name">Full name:</label>
                <input type="text" name="name" id="name" required>
            <?php endif; ?>
            <label for="email">Email:</label>
            <input type="text" name="email" id="email" required>

            <label for="password">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit"><?= $action === 'login' ? 'Login' : 'Register' ?></button>
        </form>
    </main>
</body>
</html>
