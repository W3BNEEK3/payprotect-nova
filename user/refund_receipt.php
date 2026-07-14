<?php
include '../database/db.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: login.php");
    exit();
}

$tx_id = $_GET['id'];

$stmt = $conn->prepare("SELECT t.*, 
                        u1.firstname AS sender_name, 
                        u2.firstname AS recipient_name, 
                        u2.account_number 
                        FROM transactions t 
                        JOIN users u1 ON t.sender_id = u1.id 
                        JOIN users u2 ON t.recipient_id = u2.id 
                        WHERE t.id = ?");
$stmt->execute([$tx_id]);
$tx = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tx) {
    echo "Transaction not found.";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Refund Receipt</title>
    <style>
        body { font-family: Arial; background: #f8f8f8; padding: 30px; }
        .receipt { background: white; max-width: 600px; margin: auto; padding: 30px; border-radius: 10px; box-shadow: 0 1px 6px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: green; }
        .row { margin: 15px 0; }
        .label { font-weight: bold; }
        .value { float: right; }
        .btn { margin-top: 30px; display: block; width: 100%; padding: 12px; text-align: center; background: #4CAF50; color: white; border: none; border-radius: 5px; text-decoration: none; }
    </style>
</head>
<body>

<div class="receipt">
    <h2>✅ Refund Receipt</h2>

    <div class="row"><span class="label">Transaction ID:</span> <span class="value"><?php echo $tx['id']; ?></span></div>
    <div class="row"><span class="label">Refunded To:</span> <span class="value"><?php echo $tx['recipient_name']; ?></span></div>
    <div class="row"><span class="label">Account Number:</span> <span class="value"><?php echo $tx['account_number']; ?></span></div>
    <div class="row"><span class="label">Amount:</span> <span class="value"><?php echo $tx['currency'] . number_format($tx['amount'], 2); ?></span></div>
    <div class="row"><span class="label">Note:</span> <span class="value"><?php echo $tx['note']; ?></span></div>
    <div class="row"><span class="label">Date:</span> <span class="value"><?php echo $tx['timestamp']; ?></span></div>

    <a href="dashboard.php" class="btn">🔙 Back to Dashboard</a>
</div>

</body>
</html>
