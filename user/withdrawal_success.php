<?php
session_start();
$amount = $_SESSION['withdraw_success'] ?? null;

if (!$amount) {
    header("Location: dashboard.php");
    exit();
}

unset($_SESSION['withdraw_success']);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Withdrawal Successful</title>
    <style>
        body { font-family: Arial; background: #eaf6ea; padding: 50px; }
        .box {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }
        h1 { color: #28a745; }
        p { font-size: 18px; color: #333; margin-top: 20px; }
        a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 20px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        a:hover { background-color: #218838; }
    </style>
</head>
<body>
<div class="box">
    <h1>✅ Withdrawal Successful</h1>
    <p>You have successfully withdrawn <strong>$<?= number_format($amount, 2) ?></strong>.</p>
    <a href="dashboard.php">Return to Dashboard</a>
</div>
</body>
</html>
