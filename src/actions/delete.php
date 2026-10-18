<?php

declare(strict_types=1);

require __DIR__ . '/../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

$db = $_POST['db'] ?? '';
$id = (int)($_POST['id'] ?? 0);

if (!in_array($db, ['books', 'members', 'loans'], true) || $id <= 0) {
    http_response_code(400);
    exit('Invalid request.');
}

if ($db === 'loans') {
    $loanStmt = get_pdo('loans')->prepare('SELECT book_id, returned FROM loans WHERE id = ?');
    $loanStmt->execute([$id]);
    $loan = $loanStmt->fetch(PDO::FETCH_ASSOC);
    if ($loan && !$loan['returned']) {
        get_pdo('books')->prepare('UPDATE books SET available = 1 WHERE id = ?')->execute([$loan['book_id']]);
    }
}

$stmt = get_pdo($db)->prepare("DELETE FROM $db WHERE id = ?");
$stmt->execute([$id]);

header('Location: ../pages/' . $db . '.php');
exit;
