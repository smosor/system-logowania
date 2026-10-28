
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require 'db.php';

$blad = '';
$komunikat = '';
$editId = (int)($_GET['edit'] ?? 0);

// Obsługa formularzy (dodawanie / edycja)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';

    if (isset($_POST['add_user'])) {
        if ($u !== '' && $p !== '') {
            try {
                $hash = password_hash($p, PASSWORD_DEFAULT);

                $stmt = $conn->prepare(
                    'INSERT INTO users (username, password_hash) VALUES (?, ?)'
                );
                $stmt->bind_param('ss', $u, $hash);
                $stmt->execute();
                $stmt->close();

                $komunikat = 'Pomyślnie dodano użytkownika ' . $u . '!';
            } catch (mysqli_sql_exception $e) {
                $blad = ($e->getCode() === 1062)
                    ? 'Użytkownik o takim loginie już istnieje.'
                    : 'Błąd bazy danych.';
            }
        } else {
            $blad = 'Wypełnij login i hasło.';
        }

    } elseif (isset($_POST['edit_user'])) {
        $id = (int)($_POST['id'] ?? 0);

        if ($u !== '' && $id > 0) {
            try {
                if ($p !== '') {
                    $hash = password_hash($p, PASSWORD_DEFAULT);

                    $stmt = $conn->prepare(
                        'UPDATE users SET username = ?, password_hash = ? WHERE id = ?'
                    );
                    $stmt->bind_param('ssi', $u, $hash, $id);
                } else {
                    $stmt = $conn->prepare(
                        'UPDATE users SET username = ? WHERE id = ?'
                    );
                    $stmt->bind_param('si', $u, $id);
                }

                $stmt->execute();
                $stmt->close();

                if ((int)$_SESSION['user_id'] === $id) {
                    $_SESSION['username'] = $u;
                }

                $komunikat = 'Pomyślnie zaktualizowano dane!';
                $editId = 0;

            } catch (mysqli_sql_exception $e) {
                $blad = ($e->getCode() === 1062)
                    ? 'Ten login jest już zajęty.'
                    : 'Błąd bazy danych.';
            }
        } else {
            $blad = 'Login nie może być pusty.';
        }
    }
}

// Pobranie użytkownika do edycji
$editUser = null;

if ($editId > 0) {
    $stmt = $conn->prepare(
        'SELECT id, username FROM users WHERE id = ?'
    );
    $stmt->bind_param('i', $editId);
    $stmt->execute();

    $result = $stmt->get_result();
    $editUser = $result->fetch_assoc();

    $stmt->close();
}

// Pobranie wszystkich użytkowników
$result = $conn->query(
    'SELECT id, username FROM users ORDER BY id ASC'
);
$users = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Panel użytkownika</title>
</head>
<body>
    <h1>Witaj, <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>!</h1>

    <p><a href="logout.php">Wyloguj się</a></p>
    <hr>

    <?php if ($blad): ?>
        <p style="color: red;">
            <?= htmlspecialchars($blad, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($komunikat): ?>
        <p style="color: green;">
            <?= htmlspecialchars($komunikat, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <?php if ($editUser): ?>
        <h2>
            Edycja użytkownika:
            <?= htmlspecialchars($editUser['username'], ENT_QUOTES, 'UTF-8') ?>
        </h2>

        <form method="POST">
            <input type="hidden" name="id" value="<?= (int)$editUser['id'] ?>">

            <label>
                Login:
                <input
                    type="text"
                    name="username"
                    value="<?= htmlspecialchars($editUser['username'], ENT_QUOTES, 'UTF-8') ?>"
                    required
                >
            </label>

            <br><br>

            <label>
                Nowe hasło (opcjonalnie):
                <input
                    type="password"
                    name="password"
                    placeholder="Zostaw puste, aby nie zmieniać"
                >
            </label>

            <br><br>

            <button type="submit" name="edit_user">Zapisz</button>
            <a href="panel.php">Anuluj</a>
        </form>

    <?php elseif ($editId === 0): ?>
        <h2>Dodaj nowego użytkownika</h2>

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

            <button type="submit" name="add_user">Dodaj użytkownika</button>
        </form>
    <?php endif; ?>

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
                    <td><?= (int)$u['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>

                        <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                            <strong>(Twoje konto)</strong>
                        <?php endif; ?>
                    </td>

                    <td>
                        <a href="panel.php?edit=<?= (int)$u['id'] ?>">Edytuj</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
