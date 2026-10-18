<?php

declare(strict_types=1);

const DB_DIR = '/var/www/data';

const DB_FILES = [
    'books' => 'books.db',
    'members' => 'members.db',
    'loans' => 'loans.db',
];

function get_pdo(string $dbName): PDO
{
    if (!isset(DB_FILES[$dbName])) {
        throw new InvalidArgumentException("Unknown database: $dbName");
    }

    $pdo = new PDO('sqlite:' . DB_DIR . '/' . DB_FILES[$dbName]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $pdo;
}
