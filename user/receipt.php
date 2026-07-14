<?php
session_start();
include '../database/db.php';

if (!isset($_GET['transaction_id'])) {
    echo "No transaction ID provided.";
    exit();
}

$transaction_id = $_GET['transaction_id'];

// Fetch transaction and user info
$stmt = $conn->prepare("SELECT t.*, u.fullname, u.account_number FROM transactions t JOIN users u ON t.user_id = u.id WHERE t.id = ?");
$stmt->execute([$transaction_id]);
$txn = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$txn) {
    echo "Transaction not found.";
    exit();
}

// Set display values
$txnType = 'Credit'; // always credit
$txnColor = 'credit';
$accountName = 'NovaTrust'; // fixed account name
$date = date("F j, Y", strtotime($txn['created_at']));
$time = date("g:i A", strtotime($txn['created_at']));
?>

<!DOCTYPE html>
<html>
<head>
    <title>Transaction Receipt - NovaTrust</title>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: 'Segoe UI', sans-serif;
            padding: 30px;
        }

        .receipt-box {
            max-width: 500px;
            margin: auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 30px 35px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        h2 {
            text-align: center;
            color: #0d6efd;
            margin-bottom: 25px;
        }

        .label {
            font-weight: bold;
            margin-top: 10px;
            color: #222;
        }

        .value {
            margin-bottom: 15px;
            color: #444;
        }

        .credit {
            color: green;
        }

        .divider {
            border-top: 1px solid #ccc;
            margin: 25px 0;
        }
    </style>
</head>
<body>

<div class="receipt-box">
    <h2>NovaTrust Receipt</h2>

    <div class="label">Transaction ID:</div>
    <div class="value"><?php echo htmlspecialchars($txn['id']); ?></div>

    <div class="label">Account Name:</div>
    <div class="value"><?php echo htmlspecialchars($accountName); ?></div>

    <div class="label">Account Number:</div>
    <div class="value"><?php echo htmlspecialchars($txn['account_number']); ?></div>

    <div class="label">Transaction Type:</div>
    <div class="value <?php echo $txnColor; ?>"><?php echo $txnType; ?></div>

    <div class="label">Amount:</div>
    <div class="value"><strong><?php echo htmlspecialchars($txn['currency'] . ' ' . number_format($txn['amount'], 2)); ?></strong></div>

    <div class="label">Description:</div>
    <div class="value"><?php echo htmlspecialchars($txn['message']); ?></div>

    <div class="label">Date:</div>
    <div class="value"><?php echo $date; ?></div>

    <div class="label">Time:</div>
    <div class="value"><?php echo $time; ?></div>

    <div class="divider"></div>
</div>

</body>
</html>
