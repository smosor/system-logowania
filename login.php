<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: panel.php'); exit(); }

$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';
    
    $stmt = mysqli_prepare($conn, 'SELECT id, password_hash FROM users WHERE username = ?');
    mysqli_stmt_bind_param($stmt, 's', $_POST['username']);
    mysqli_stmt_execute($stmt);
    $user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

    if ($user && password_verify($_POST['password'], $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $_POST['username'];
        header('Location: panel.php');
        exit();
    }
    $blad = 'Błędny login lub hasło.';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Logowanie</title></head>
<body>
    <h1>Logowanie</h1>
    <?php if ($blad): ?><p style="color: red;"><?= htmlspecialchars($blad) ?></p><?php endif; ?>

    <form method="POST">
        Login: <input type="text" name="username" required><br><br>
        Hasło: <input type="password" name="password" required><br><br>
        <button type="submit">Zaloguj</button>
    </form>

    <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
</body>
</html>