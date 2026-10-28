
<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: panel.php');
    exit;
}

$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'db.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$username || !$password) {
        $blad = 'Wypełnij wszystkie pola.';
    } else {
        try {
            $stmt = $conn->prepare(
                'SELECT id, password_hash FROM users WHERE username = ?'
            );

            $stmt->bind_param('s', $username);
            $stmt->execute();

            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            $stmt->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $username;

                header('Location: panel.php');
                exit;
            }

            $blad = 'Błędny login lub hasło.';

        } catch (mysqli_sql_exception $e) {
            $blad = 'Wystąpił błąd podczas logowania.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Logowanie</title>
</head>
<body>
    <h1>Logowanie</h1>

    <?php if ($blad): ?>
        <p style="color: red;">
            <?= htmlspecialchars($blad, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label>
            Login:
            <input type="text" name="username" required>
        </label>
        <br><br>

        <label>
            Hasło:
            <input type="password" name="password" required>
        </label>
        <br><br>

        <button type="submit">Zaloguj</button>
    </form>

    <p>Nie masz konta? <a href="register.php">Zarejestruj się</a></p>
</body>
</html>
