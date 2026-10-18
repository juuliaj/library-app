<?php

declare(strict_types=1);

require __DIR__ . '/../database.php';
require __DIR__ . '/../includes/helpers.php';

$error = $_GET['error'] ?? null;

$pdo = get_pdo('books');
$books = $pdo->query('SELECT * FROM books ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Books - Library App</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<p><a href="../index.php">&larr; Back to home</a></p>
<h1>Books</h1>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Title</th>
        <th>Author</th>
        <th>Year</th>
        <th>Available</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($books)): ?>
        <tr><td colspan="6">No books yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($books as $book): ?>
        <tr>
            <td><?= (int)$book['id'] ?></td>
            <td><?= h($book['title']) ?></td>
            <td><?= h($book['author']) ?></td>
            <td><?= (int)$book['publication_year'] ?></td>
            <td><?= $book['available'] ? 'Yes' : 'No' ?></td>
            <td>
                <a class="btn-edit" href="../actions/edit.php?db=books&id=<?= (int)$book['id'] ?>">Edit</a>
                <form method="post" action="../actions/delete.php" style="display:inline;"
                      onsubmit="return confirm('Delete this book?');">
                    <input type="hidden" name="db" value="books">
                    <input type="hidden" name="id" value="<?= (int)$book['id'] ?>">
                    <button class="btn-delete" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Add a book</h2>
<form method="post" action="../actions/add.php">
    <input type="hidden" name="db" value="books">
    <label>Title: <input type="text" name="title" required></label>
    <label>Author: <input type="text" name="author" required></label>
    <label>Publication Year: <input type="number" name="publication_year" required></label>
    <label>Available: <input type="checkbox" name="available" value="1" checked></label>
    <button type="submit">Add</button>
</form>
</body>
</html>
