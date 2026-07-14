<?php
session_start();
include '../database/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all transactions
$stmt = $conn->query("SELECT t.id, t.amount, t.currency, t.type, t.message, t.created_at, 
                             u.firstname, u.lastname, u.account_number 
                      FROM transactions t 
                      JOIN users u ON t.user_id = u.id 
                      ORDER BY t.created_at DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Transactions - Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 30px;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .credit {
            color: green;
            font-weight: bold;
        }

        .debit {
            color: red;
            font-weight: bold;
        }

        .receipt-link {
            color: #007bff;
            text-decoration: none;
        }

        .receipt-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>All Transactions (Admin View)</h2>

<table>
    <tr>
        <th>Date</th>
        <th>Txn ID</th>
        <th>User</th>
        <th>Account No</th>
        <th>Type</th>
        <th>Amount</th>
        <th>Message</th>
        <th>Receipt</th>
    </tr>
    <?php foreach ($transactions as $txn): ?>
    <tr>
        <td><?php echo htmlspecialchars($txn['created_at']); ?></td>
        <td>TXN<?php echo str_pad($txn['id'], 6, '0', STR_PAD_LEFT); ?></td>
        <td><?php echo htmlspecialchars($txn['firstname'] . ' ' . $txn['lastname']); ?></td>
        <td><?php echo htmlspecialchars($txn['account_number']); ?></td>
        <td class="<?php echo $txn['type'] === 'credit' ? 'credit' : 'debit'; ?>">
            <?php echo ucfirst($txn['type']); ?>
        </td>
        <td><?php echo $txn['currency'] . ' ' . number_format($txn['amount'], 2); ?></td>
        <td><?php echo htmlspecialchars($txn['message']); ?></td>
        <td>
            <a class="receipt-link" href="receipt.php?transaction_id=<?php echo urlencode($txn['id']); ?>">View</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>

</body>
</html>
