<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$stmt = $conn->prepare("SELECT id, firstname, lastname, email, country, currency, balance, account_number, status FROM users");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Users - Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f4f4f4; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: left; }
        th { background: #e0e0e0; }
    </style>
</head>
<body>
    <h2>All Registered Users</h2>
    <table>
        <tr>
            <th>ID</th><th>Name</th><th>Email</th><th>Country</th><th>Currency</th><th>Balance</th><th>Account No.</th><th>Status</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= $user['firstname'] . " " . $user['lastname'] ?></td>
            <td><?= $user['email'] ?></td>
            <td><?= $user['country'] ?></td>
            <td><?= $user['currency'] ?></td>
            <td><?= $user['currency'] . number_format($user['balance'], 2) ?></td>
            <td><?= $user['account_number'] ?></td>
            <td><?= $user['status'] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
