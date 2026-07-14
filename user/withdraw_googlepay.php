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
    <title>Withdraw via Google Pay - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        input, select, button {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        button {
            background-color: #4285F4;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #3367D6;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Withdraw via Google Pay</h2>
    <form action="process_withdraw.php" method="POST">
        <input type="hidden" name="method" value="googlepay">
        <label>Google Pay Email Address</label>
        <input type="email" name="gpay_email" required>
        <label>Amount</label>
        <input type="number" name="amount" step="0.01" required>
        <button type="submit">Submit Withdrawal</button>
    </form>
</div>
</body>
</html>