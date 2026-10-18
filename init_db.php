<?php

declare(strict_types=1);

require __DIR__ . '/src/database.php';

if (!is_dir(DB_DIR)) {
    mkdir(DB_DIR, 0775, true);
}

function init_books(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS books (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        author TEXT NOT NULL,
        publication_year INTEGER NOT NULL,
        available INTEGER NOT NULL DEFAULT 1
    )');

    $count = (int)$pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO books (title, author, publication_year, available) VALUES (?, ?, ?, ?)');
        $dummy = [
            ['The Hobbit', 'J.R.R. Tolkien', 1937, 1],
            ['1984', 'George Orwell', 1949, 1],
            ['To Kill a Mockingbird', 'Harper Lee', 1960, 0],
            ['Clean Code', 'Robert C. Martin', 2008, 1],
        ];
        foreach ($dummy as $row) {
            $stmt->execute($row);
        }
        echo "Seeded books.db with dummy data.\n";
    }
}

function init_members(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS members (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        membership_date TEXT NOT NULL
    )');

    $count = (int)$pdo->query('SELECT COUNT(*) FROM members')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO members (name, email, membership_date) VALUES (?, ?, ?)');
        $dummy = [
            ['Alice Johnson', 'alice@example.com', '2023-01-15'],
            ['Bob Smith', 'bob@example.com', '2023-03-22'],
            ['Carla Mendes', 'carla@example.com', '2024-06-10'],
        ];
        foreach ($dummy as $row) {
            $stmt->execute($row);
        }
        echo "Seeded members.db with dummy data.\n";
    }
}

function init_loans(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS loans (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        book_id INTEGER NOT NULL,
        member_id INTEGER NOT NULL,
        loan_date TEXT NOT NULL,
        returned INTEGER NOT NULL DEFAULT 0
    )');

    $count = (int)$pdo->query('SELECT COUNT(*) FROM loans')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO loans (book_id, member_id, loan_date, returned) VALUES (?, ?, ?, ?)');
        $dummy = [
            [1, 1, '2024-05-01', 0],
            [2, 2, '2024-05-10', 1],
            [3, 3, '2024-06-01', 0],
        ];
        foreach ($dummy as $row) {
            $stmt->execute($row);
        }
        echo "Seeded loans.db with dummy data.\n";
    }
}

init_books(get_pdo('books'));
init_members(get_pdo('members'));
init_loans(get_pdo('loans'));

echo "Database initialization complete.\n";
