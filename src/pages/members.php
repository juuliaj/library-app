<?php

declare(strict_types=1);

require __DIR__ . '/../database.php';
require __DIR__ . '/../includes/helpers.php';

$error = $_GET['error'] ?? null;

$pdo = get_pdo('members');
$members = $pdo->query('SELECT * FROM members ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Members - Library App</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<p><a href="../index.php">&larr; Back to home</a></p>
<h1>Members</h1>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p>
<?php endif; ?>

<table>
    <thead>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Membership Date</th>
        <th>Actions</th>
    </tr>
    </thead>
    <tbody>
    <?php if (empty($members)): ?>
        <tr><td colspan="5">No members yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($members as $member): ?>
        <tr>
            <td><?= (int)$member['id'] ?></td>
            <td><?= h($member['name']) ?></td>
            <td><?= h($member['email']) ?></td>
            <td><?= h($member['membership_date']) ?></td>
            <td>
                <a class="btn-edit" href="../actions/edit.php?db=members&id=<?= (int)$member['id'] ?>">Edit</a>
                <form method="post" action="../actions/delete.php" style="display:inline;"
                      onsubmit="return confirm('Delete this member?');">
                    <input type="hidden" name="db" value="members">
                    <input type="hidden" name="id" value="<?= (int)$member['id'] ?>">
                    <button class="btn-delete" type="submit">Delete</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h2>Add a member</h2>
<form method="post" action="../actions/add.php">
    <input type="hidden" name="db" value="members">
    <label>Name: <input type="text" name="name" required></label>
    <label>Email: <input type="email" name="email" required></label>
    <label>Membership Date: <input type="date" name="membership_date" required></label>
    <button type="submit">Add</button>
</form>
</body>
</html>
