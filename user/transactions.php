<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user currency
$stmt = $conn->prepare("SELECT currency FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$currency = $user['currency'] ?? '$';

// Fetch transactions
$stmt = $conn->prepare("SELECT id, type, amount, message, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #333;
            color: white;
        }
        tr:hover {
            background: #f1f1f1;
        }
        .credit {
            color: green;
        }
        .debit {
            color: red;
        }
    </style>
</head>
<body>

<h2>Transaction History</h2>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Transaction ID</th>
            <th>Type</th>
            <th>Description</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($transactions as $txn): ?>
        <tr>
            <td><?php echo htmlspecialchars($txn['created_at']); ?></td>
            <td>
                <a href="receipt.php?transaction_id=<?php echo urlencode($txn['id']); ?>">
                    TXN<?php echo str_pad($txn['id'], 6, '0', STR_PAD_LEFT); ?>
                </a>
            </td>
            <td class="<?php echo $txn['type']; ?>"><?php echo ucfirst($txn['type']); ?></td>
            <td><?php echo htmlspecialchars($txn['message']); ?></td>
            <td><?php echo $currency . ' ' . number_format($txn['amount'], 2); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
