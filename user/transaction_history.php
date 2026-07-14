<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user transactions
$stmt = $conn->prepare("SELECT amount, type, method, reference, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <style>
        body {
            font-family: Arial;
            padding: 20px;
            background: #f4f4f4;
        }
        h2 {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            background: white;
        }
        table th, table td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: left;
        }
        table th {
            background-color: #eee;
        }
    </style>
</head>
<body>

    <h2>🧾 Transaction History</h2>

    <?php if (count($transactions) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Type</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $tx): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($tx['amount']); ?></td>
                        <td><?php echo htmlspecialchars($tx['type']); ?></td>
                        <td><?php echo htmlspecialchars($tx['method']); ?></td>
                        <td><?php echo htmlspecialchars($tx['reference']); ?></td>
                        <td><?php echo htmlspecialchars($tx['created_at']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No transactions found.</p>
    <?php endif; ?>

</body>
</html>
