<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get refunded balance
$stmt = $conn->prepare("SELECT currency, refunded_balance FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$currency = $user['currency'];
$refunded_balance = number_format($user['refunded_balance'], 2);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Refund Balance - NovaTrust</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body>

<div class="refund-container">
    <h2 class="refund-title">Refunded Balance</h2>
    <div class="refund-balance"><?php echo $currency . $refunded_balance; ?></div>
    <a href="dashboard.php" class="refund-back-link">← Back to Dashboard</a>
</div>

</body>
</html>
