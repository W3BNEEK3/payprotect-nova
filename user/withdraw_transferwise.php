<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Withdraw via TransferWise - NovaTrust</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 550px;
            margin: 50px auto;
            background-color: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #333;
        }
        label {
            margin-top: 10px;
            display: block;
        }
        input, select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background-color: #00b2a9;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }
        button:hover {
            background-color: #008f87;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Withdraw via TransferWise</h2>
    <form action="process_withdraw.php" method="POST">
        <input type="hidden" name="method" value="transferwise">
        <label>Recipient Full Name</label>
        <input type="text" name="recipient_name" required>
        <label>TransferWise Email</label>
        <input type="email" name="tw_email" required>
        <label>Country</label>
        <input type="text" name="country" required>
        <label>Amount</label>
        <input type="number" name="amount" step="0.01" required>
        <button type="submit">Submit Withdrawal</button>
    </form>
</div>
</body>
</html>