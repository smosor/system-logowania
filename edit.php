<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id <= 0) {
    header('Location: panel.php');
    exit();
}

// Pobranie danych edytowanego użytkownika
$stmt = mysqli_prepare($conn, 'SELECT id, username FROM users WHERE id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$user) {
    header('Location: panel.php');
    exit();
}

$komunikat = '';
$blad = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = trim($_POST['username'] ?? '');
    $new_password = $_POST['password'] ?? '';

    if ($new_username === '') {
        $blad = 'Nazwa użytkownika nie może być pusta.';
    } else {
        // Sprawdź, czy nowy login nie jest już zajęty przez innego użytkownika
        $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ? AND id != ?');
        mysqli_stmt_bind_param($check, 'si', $new_username, $id);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $blad = 'Ten login jest już zajęty przez innego użytkownika.';
        } else {
            if ($new_password !== '') {
                // Zmiana loginu i hasła
                $hash = password_hash($new_password, PASSWORD_DEFAULT);
                $update = mysqli_prepare($conn, 'UPDATE users SET username = ?, password_hash = ? WHERE id = ?');
                mysqli_stmt_bind_param($update, 'ssi', $new_username, $hash, $id);
            } else {
                // Tylko zmiana loginu
                $update = mysqli_prepare($conn, 'UPDATE users SET username = ? WHERE id = ?');
                mysqli_stmt_bind_param($update, 'si', $new_username, $id);
            }

            if (mysqli_stmt_execute($update)) {
                $komunikat = 'Dane konta zostały pomyślnie zaktualizowane.';
                $user['username'] = $new_username;

                // Jeżeli edytujemy aktualnie zalogowanego użytkownika, aktualizujemy też sesję
                if ($_SESSION['user_id'] === $id) {
                    $_SESSION['username'] = $new_username;
                }
            } else {
                $blad = 'Wystąpił błąd podczas zapisywania zmian.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edycja konta</title>
</head>
<body>
    <h1>Edycja konta</h1>

    <?php if ($blad): ?>
        <p style="color: red;"><?= htmlspecialchars($blad) ?></p>
    <?php endif; ?>

    <?php if ($komunikat): ?>
        <p style="color: green;"><?= htmlspecialchars($komunikat) ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="hidden" name="id" value="<?= $user['id'] ?>">

        <label>
            Login:<br>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </label>
        <br><br>

        <label>
            Nowe hasło (pozostaw puste, aby nie zmieniać):<br>
            <input type="password" name="password" placeholder="Wpisz nowe hasło">
        </label>
        <br><br>

        <button type="submit">Zapisz zmiany</button>
    </form>

    <br>
    <a href="panel.php">Powrót do panelu</a>
</body>
</html>
