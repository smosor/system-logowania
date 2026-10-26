<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: panel.php'); exit; }

$blad = '';
$komunikat = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $blad = 'Wypełnij wszystkie pola.';
    } else {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            mysqli_execute_query($conn, 'INSERT INTO users (username, password_hash) VALUES (?, ?)', [$username, $hash]);
            $komunikat = 'Konto utworzone pomyślnie!';
        } catch (mysqli_sql_exception $e) {
            $blad = ($e->getCode() === 1062) ? 'Użytkownik o takim loginie już istnieje.' : 'Błąd rejestracji.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Rejestracja</title></head>
<body>
    <h1>Rejestracja</h1>

    <?php if ($blad): ?><p style="color: red;"><?= htmlspecialchars($blad) ?></p><?php endif; ?>
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
