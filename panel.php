<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }

require 'db.php';

$blad = '';
$komunikat = '';
$editId = (int)($_GET['edit'] ?? 0);

// Obsługa formularzy (dodawanie / edycja)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if (isset($_POST['add_user'])) {
        if ($u && $p) {
            try {
                mysqli_execute_query($conn, 'INSERT INTO users (username, password_hash) VALUES (?, ?)', [$u, password_hash($p, PASSWORD_DEFAULT)]);
                $komunikat = "Pomyślnie dodano użytkownika $u!";
            } catch (mysqli_sql_exception $e) {
                $blad = ($e->getCode() === 1062) ? 'Użytkownik o takim loginie już istnieje.' : 'Błąd bazy danych.';
            }
        } else {
            $blad = 'Wypełnij login i hasło.';
        }
    } elseif (isset($_POST['edit_user'])) {
        $id = (int)$_POST['id'];
        if ($u) {
            try {
                if ($p) {
                    mysqli_execute_query($conn, 'UPDATE users SET username = ?, password_hash = ? WHERE id = ?', [$u, password_hash($p, PASSWORD_DEFAULT), $id]);
                } else {
                    mysqli_execute_query($conn, 'UPDATE users SET username = ? WHERE id = ?', [$u, $id]);
                }
                if ($_SESSION['user_id'] === $id) $_SESSION['username'] = $u;
                $komunikat = 'Pomyślnie zaktualizowano dane!';
                $editId = 0; // wyjście z trybu edycji
            } catch (mysqli_sql_exception $e) {
                $blad = ($e->getCode() === 1062) ? 'Ten login jest już zajęty.' : 'Błąd bazy danych.';
            }
        } else {
            $blad = 'Login nie może być pusty.';
        }
    }
}

// Pobranie danych do edycji (jeśli wybrano)
$editUser = $editId ? mysqli_execute_query($conn, 'SELECT id, username FROM users WHERE id = ?', [$editId])->fetch_assoc() : null;

// Pobranie wszystkich użytkowników
$users = mysqli_execute_query($conn, 'SELECT id, username FROM users ORDER BY id ASC')->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head><meta charset="UTF-8"><title>Panel użytkownika</title></head>
<body>
    <h1>Witaj, <?= htmlspecialchars($_SESSION['username']) ?>!</h1>
    <p><a href="logout.php">Wyloguj się</a></p>
    <hr>

    <?php if ($blad): ?><p style="color: red;"><?= htmlspecialchars($blad) ?></p><?php endif; ?>
    <?php if ($komunikat): ?><p style="color: green;"><?= htmlspecialchars($komunikat) ?></p><?php endif; ?>

    <?php if ($editUser): ?>
        <h2>Edycja użytkownika: <?= htmlspecialchars($editUser['username']) ?></h2>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
            Login: <input type="text" name="username" value="<?= htmlspecialchars($editUser['username']) ?>" required>
            Nowe hasło (opcjonalnie): <input type="password" name="password" placeholder="Zostaw puste, aby nie zmieniać">
            <button type="submit" name="edit_user">Zapisz</button>
            <a href="panel.php">Anuluj</a>
        </form>
    <?php else: ?>
        <h2>Dodaj nowego użytkownika</h2>
        <form method="POST">
            Login: <input type="text" name="username" required>
            Hasło: <input type="password" name="password" required>
            <button type="submit" name="add_user">Dodaj użytkownika</button>
        </form>
    <?php endif; ?>

    <hr>
    <h2>Lista kont w systemie</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr><th>ID</th><th>Login</th><th>Akcje</th></tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $u['id'] ?></td>
                    <td>
                        <?= htmlspecialchars($u['username']) ?>
                        <?php if ($u['id'] == $_SESSION['user_id']): ?><strong>(Twoje konto)</strong><?php endif; ?>
                    </td>
                    <td><a href="panel.php?edit=<?= $u['id'] ?>">Edytuj</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>