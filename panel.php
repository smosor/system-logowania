<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel</title>
</head>
<body>
    <h1>Witaj, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <a href="logout.php">Wyloguj się</a>
</body>
</html>