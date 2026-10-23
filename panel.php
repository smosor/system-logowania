<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require 'db.php';

$blad = '';
$komunikat = '';

// Obsługa dodawania użytkownika bezpośrednio z panelu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $blad = 'Wypełnij wszystkie pola dodawania użytkownika.';
    } else {
        $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE username = ?');
        mysqli_stmt_bind_param($check, 's', $username);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {
            $blad = 'Użytkownik o takim loginie już istnieje.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($conn, 'INSERT INTO users (username, password_hash) VALUES (?, ?)');
            mysqli_stmt_bind_param($insert, 'ss', $username, $hash);

            if (mysqli_stmt_execute($insert)) {
                $komunikat = 'Pomyślnie dodano użytkownika ' . htmlspecialchars($username) . '!';
            } else {
                $blad = 'Błąd podczas dodawania użytkownika.';
            }
        }
    }
}

// Pobranie listy wszystkich użytkowników
$result = mysqli_query($conn, 'SELECT id, username FROM users ORDER BY id ASC');
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
</head>
<body>
    <h1>Witaj, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p><a href="logout.php">Wyloguj się</a></p>

    <hr>

    <h2>Dodaj nowego użytkownika</h2>
    <?php if ($blad): ?>
        <p style="color: red;"><?= htmlspecialchars($blad) ?></p>
    <?php endif; ?>
    <?php if ($komunikat): ?>
        <p style="color: green;"><?= htmlspecialchars($komunikat) ?></p>
    <?php endif; ?>

    <form method="POST">
        Login: <input type="text" name="username" required>
        Hasło: <input type="password" name="password" required>
        <button type="submit" name="add_user">Dodaj użytkownika</button>
    </form>

    <hr>

    <h2>Lista kont w systemie</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Login</th>
                <th>Akcje</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($u['username']) ?>
                        <?php if ($u['id'] == $_SESSION['user_id']): ?>
                            <strong>(Twoje konto)</strong>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit.php?id=<?= $u['id'] ?>">Edytuj</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>