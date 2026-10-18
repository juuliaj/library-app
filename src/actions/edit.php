<?php

declare(strict_types=1);

require __DIR__ . '/../database.php';
require __DIR__ . '/../includes/helpers.php';

function fail(string $db, string $message): never
{
    header('Location: ../pages/' . $db . '.php?error=' . urlencode($message));
    exit;
}

$db = $_GET['db'] ?? $_POST['db'] ?? '';
if (!in_array($db, ['books', 'members', 'loans'], true)) {
    http_response_code(400);
    exit('Unknown database.');
}

$pdo = get_pdo($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        fail($db, 'Invalid record ID.');
    }

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

        $stmt = $pdo->prepare(
            'UPDATE books SET title = ?, author = ?, publication_year = ?, available = ? WHERE id = ?'
        );
        $stmt->execute([$title, $author, (int)$year, $available, $id]);
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

        $stmt = $pdo->prepare(
            'UPDATE members SET name = ?, email = ?, membership_date = ? WHERE id = ?'
        );
        $stmt->execute([$name, $email, $membershipDate, $id]);
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

        $stmt = $pdo->prepare(
            'UPDATE loans SET book_id = ?, member_id = ?, loan_date = ?, returned = ? WHERE id = ?'
        );
        $stmt->execute([(int)$bookId, (int)$memberId, $loanDate, $returned, $id]);

        $booksPdo = get_pdo('books');
        $booksPdo->prepare('UPDATE books SET available = ? WHERE id = ?')->execute([$returned ? 1 : 0, (int)$bookId]);
    }

    header('Location: ../pages/' . $db . '.php');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM $db WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    exit('Record not found.');
}

if ($db === 'loans') {
    $books = get_pdo('books')->query('SELECT id, title FROM books')->fetchAll(PDO::FETCH_KEY_PAIR);
    $members = get_pdo('members')->query('SELECT id, name FROM members')->fetchAll(PDO::FETCH_KEY_PAIR);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit record - Library App</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<p><a href="../pages/<?= h($db) ?>.php">&larr; Back</a></p>
<h1>Edit #<?= (int)$row['id'] ?></h1>
<form method="post" action="edit.php">
    <input type="hidden" name="db" value="<?= h($db) ?>">
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">

    <?php if ($db === 'books'): ?>
        <label>Title: <input type="text" name="title" value="<?= h($row['title']) ?>" required></label>
        <label>Author: <input type="text" name="author" value="<?= h($row['author']) ?>" required></label>
        <label>Publication Year:
            <input type="number" name="publication_year" value="<?= (int)$row['publication_year'] ?>" required>
        </label>
        <label>Available:
            <input type="checkbox" name="available" value="1" <?= $row['available'] ? 'checked' : '' ?>>
        </label>
    <?php elseif ($db === 'members'): ?>
        <label>Name: <input type="text" name="name" value="<?= h($row['name']) ?>" required></label>
        <label>Email: <input type="email" name="email" value="<?= h($row['email']) ?>" required></label>
        <label>Membership Date:
            <input type="date" name="membership_date" value="<?= h($row['membership_date']) ?>" required>
        </label>
    <?php elseif ($db === 'loans'): ?>
        <label>Book:
            <select name="book_id" required>
                <?php foreach ($books as $bookId => $title): ?>
                    <option value="<?= (int)$bookId ?>" <?= $bookId == $row['book_id'] ? 'selected' : '' ?>>
                        <?= h($title) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Member:
            <select name="member_id" required>
                <?php foreach ($members as $memberId => $name): ?>
                    <option value="<?= (int)$memberId ?>" <?= $memberId == $row['member_id'] ? 'selected' : '' ?>>
                        <?= h($name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </label>
        <label>Loan Date: <input type="date" name="loan_date" value="<?= h($row['loan_date']) ?>" required></label>
        <label>Returned:
            <input type="checkbox" name="returned" value="1" <?= $row['returned'] ? 'checked' : '' ?>>
        </label>
    <?php endif; ?>

    <button type="submit">Save</button>
</form>
</body>
</html>
