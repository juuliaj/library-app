<?php

declare(strict_types=1);

require __DIR__ . '/../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

function fail(string $db, string $message): never
{
    header('Location: ../pages/' . $db . '.php?error=' . urlencode($message));
    exit;
}

$db = $_POST['db'] ?? '';

if ($db === 'books') {
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $year = trim($_POST['publication_year'] ?? '');
    $available = isset($_POST['available']) ? 1 : 0;

    if ($title === '' || $author === '' || $year === '') {
        fail($db, 'Title, author and publication year are required.');
    }
    if (!ctype_digit($year)) {
        fail($db, 'Publication year must be a whole number.');
    }

    $stmt = get_pdo('books')->prepare(
        'INSERT INTO books (title, author, publication_year, available) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$title, $author, (int)$year, $available]);
} elseif ($db === 'members') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $membershipDate = trim($_POST['membership_date'] ?? '');

    if ($name === '' || $email === '' || $membershipDate === '') {
        fail($db, 'Name, email and membership date are required.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail($db, 'Email must be a valid email address.');
    }

    $stmt = get_pdo('members')->prepare(
        'INSERT INTO members (name, email, membership_date) VALUES (?, ?, ?)'
    );
    $stmt->execute([$name, $email, $membershipDate]);
} elseif ($db === 'loans') {
    $bookId = trim($_POST['book_id'] ?? '');
    $memberId = trim($_POST['member_id'] ?? '');
    $loanDate = trim($_POST['loan_date'] ?? '');
    $returned = isset($_POST['returned']) ? 1 : 0;

    if ($bookId === '' || $memberId === '' || $loanDate === '') {
        fail($db, 'Book, member and loan date are required.');
    }
    if (!ctype_digit($bookId) || !ctype_digit($memberId)) {
        fail($db, 'Book and member must be selected from the list.');
    }

    $booksPdo = get_pdo('books');
    $bookStmt = $booksPdo->prepare('SELECT available FROM books WHERE id = ?');
    $bookStmt->execute([(int)$bookId]);
    $book = $bookStmt->fetch(PDO::FETCH_ASSOC);

    if (!$book) {
        fail($db, 'Selected book does not exist.');
    }
    if (!$returned && !$book['available']) {
        fail($db, 'This book is not available for loan.');
    }

    $stmt = get_pdo('loans')->prepare(
        'INSERT INTO loans (book_id, member_id, loan_date, returned) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([(int)$bookId, (int)$memberId, $loanDate, $returned]);

    if (!$returned) {
        $booksPdo->prepare('UPDATE books SET available = 0 WHERE id = ?')->execute([(int)$bookId]);
    }
} else {
    http_response_code(400);
    exit('Unknown database.');
}

header('Location: ../pages/' . $db . '.php');
exit;
