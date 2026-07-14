
<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['ars_verified']) {
    header("Location: verify_ars.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM virtual_cards WHERE user_id = ?");
$stmt->execute([$user_id]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Virtual Card Required - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 80px auto;
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        .card-box {
            background: #f9f9f9;
            padding: 20px;
            border-left: 5px solid #007bff;
            margin-top: 20px;
            border-radius: 5px;
        }
        .button {
            display: inline-block;
            margin-top: 30px;
            background-color: maroon;
            color: white;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .button:hover {
            background-color: darkred;
        }
        .info {
            margin-top: 15px;
            line-height: 1.6;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Virtual Card Required</h2>
    <p class="info">
        To ensure secure disbursement of your withdrawal and protect your account from unauthorized transactions, a verified virtual card is required.
    </p>

    <div class="card-box">
        <strong>Why you need a NovaTrust Virtual Card:</strong>
        <ul>
            <li>🔒 Enhances international transaction security</li>
            <li>💳 Required for automatic funds disbursement</li>
            <li>🌍 Ideal for online purchases and e-verifications</li>
            <li>🛡️ Prevents fraudulent withdrawals</li>
            <li>💼 Ensures compliance with banking regulations</li>
        </ul>
    </div>

    <p class="info">
        Kindly contact our Customer Service to purchase your virtual card. Your card will be issued once payment is confirmed and approved by our admin.
    </p>

    <a href="customer_service.php" class="button">Contact Customer Service</a>
</div>

</body>
</html>
