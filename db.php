<?php

try {
    $conn = mysqli_connect('localhost', 'root', '', 'logowanie_db');
    mysqli_set_charset($conn, 'utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log('Błąd bazy: ' . $e->getMessage());
    die('Strona jest chwilowo niedostępna (błąd bazy danych).');
}

if (!$conn) {
    die('Strona jest chwilowo niedostępna (błąd bazy danych).');
}