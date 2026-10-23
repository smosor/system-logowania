<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: panel.php');
    exit();
}

$komunikat = '';
$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $blad = 'Wypełnij wszystkie pola.';
    } else {
        // Sprawdź, czy login jest już zajęty
        $stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ?');
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $blad = 'Użytkownik o podanym loginie już istnieje.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($conn, 'INSERT INTO users (username, password_hash) VALUES (?, ?)');
            mysqli_stmt_bind_param($insert, 'ss', $username, $hash);

            if (mysqli_stmt_execute($insert)) {
                $komunikat = 'Konto zostało pomyślnie utworzone! Możesz się teraz zalogować.';
            } else {
                $blad = 'Wystąpił błąd podczas rejestracji.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Rejestracja</title>
</head>
<body>
    <h1>Rejestracja</h1>

    <?php if ($blad): ?>
        <p style="color: red;"><?= htmlspecialchars($blad) ?></p>
    <?php endif; ?>

    <?php if ($komunikat): ?>
        <p style="color: green;"><?= htmlspecialchars($komunikat) ?></p>
        <p><a href="login.php">Przejdź do logowania</a></p>
    <?php else: ?>
        <form method="POST">
            Login: <input type="text" name="username" required><br><br>
            Hasło: <input type="password" name="password" required><br><br>
            <button type="submit">Zarejestruj się</button>
        </form>
        <p>Masz już konto? <a href="login.php">Zaloguj się</a></p>
    <?php endif; ?>
</body>
</html>
