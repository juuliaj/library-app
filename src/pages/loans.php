<?php

declare(strict_types=1);

require __DIR__ . '/../database.php';
require __DIR__ . '/../includes/helpers.php';

$error = $_GET['error'] ?? null;

$pdo = get_pdo('loans');
$loans = $pdo->query('SELECT * FROM loans ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);

$books = get_pdo('books')->query('SELECT id, title FROM books')->fetchAll(PDO::FETCH_KEY_PAIR);
$availableBooks = get_pdo('books')->query('SELECT id, title FROM books WHERE available = 1')->fetchAll(PDO::FETCH_KEY_PAIR);
$members = get_pdo('members')->query('SELECT id, name FROM members')->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Loans - Library App</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<p><a href="../index.php">&larr; Back to home</a></p>
<h1>Loans</h1>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Book</th>
        <th>Member</th>
        <th>Loan Date</th>
        <th>Returned</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($loans)): ?>
        <tr><td colspan="6">No loans yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($loans as $loan): ?>
        <tr>
            <td><?= (int)$loan['id'] ?></td>
            <td><?= h($books[$loan['book_id']] ?? ('Unknown (#' . $loan['book_id'] . ')')) ?></td>
            <td><?= h($members[$loan['member_id']] ?? ('Unknown (#' . $loan['member_id'] . ')')) ?></td>
            <td><?= h($loan['loan_date']) ?></td>
            <td><?= $loan['returned'] ? 'Yes' : 'No' ?></td>
            <td>
                <a class="btn-edit" href="../actions/edit.php?db=loans&id=<?= (int)$loan['id'] ?>">Edit</a>
                <form method="post" action="../actions/delete.php" style="display:inline;"
                      onsubmit="return confirm('Delete this loan?');">
                    <input type="hidden" name="db" value="loans">
                    <input type="hidden" name="id" value="<?= (int)$loan['id'] ?>">
                    <button class="btn-delete" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Add a loan</h2>
<form method="post" action="../actions/add.php">
    <input type="hidden" name="db" value="loans">
    <label>Book:
        <select name="book_id" required>
            <option value="">-- select --</option>
            <?php foreach ($availableBooks as $bookId => $title): ?>
                <option value="<?= (int)$bookId ?>"><?= h($title) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Member:
        <select name="member_id" required>
            <option value="">-- select --</option>
            <?php foreach ($members as $memberId => $name): ?>
                <option value="<?= (int)$memberId ?>"><?= h($name) ?></option>
            <?php endforeach; ?>
        </select>
    </label>
    <label>Loan Date: <input type="date" name="loan_date" required></label>
    <label>Returned: <input type="checkbox" name="returned" value="1"></label>
    <button type="submit">Add</button>
</form>
</body>
</html>
