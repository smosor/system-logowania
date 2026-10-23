-- setup.sql – tworzy bazę i tabelę users z licznikiem błędnych prób
-- UWAGA: DROP TABLE usuwa istniejących użytkowników (uruchamiaj przy konfiguracji)

CREATE DATABASE IF NOT EXISTS logowanie_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE logowanie_db;

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    username        VARCHAR(50)  NOT NULL UNIQUE,
    password_hash   VARCHAR(255) NOT NULL,
    failed_attempts INT          NOT NULL DEFAULT 0,  -- ile razy z rzędu błędne hasło
    locked_until    DATETIME     NULL                 -- do kiedy konto jest zablokowane
);

-- Testowy użytkownik: login = jan, hasło = Haslo123
INSERT INTO users (username, password_hash)
VALUES ('jan', '$2y$10$wFZ3P84XrHhd7asxpwdy0OH7QgZRhEcp3LfJnEC4PTjQqrgXb7w7e');