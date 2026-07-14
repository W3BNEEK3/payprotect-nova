<?php
session_start();
require_once "../database/db.php";

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all users
$stmt = $conn->query("SELECT id, firstname, lastname, email, phone, country, currency, balance, account_status FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>View All Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 30px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        th {
            background: #007bff;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .status-active {
            color: green;
            font-weight: bold;
        }
        .status-suspended {
            color: red;
            font-weight: bold;
        }
    </style>
</head>
<body>

    <h2>👥 All Registered Users</h2>

    <table>
        <thead>
            <tr>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Country</th>
                <th>Currency</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['phone']) ?></td>
                    <td><?= htmlspecialchars($user['country']) ?></td>
                    <td><?= htmlspecialchars($user['currency']) ?></td>
                    <td><?= $user['currency'] . ' ' . number_format($user['balance'], 2) ?></td>
                    <td class="<?= $user['account_status'] === 'active' ? 'status-active' : 'status-suspended' ?>">
                        <?= ucfirst($user['account_status']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

</body>
</html>
