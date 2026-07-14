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
    <title>Withdraw via Skrill - NovaTrust</title>
    <style>
        body {
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #aa076b;
        }
        label {
            display: block;
            margin-top: 15px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #aa076b;
            color: #fff;
            border: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        button:hover {
            background-color: #8c064e;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Skrill Withdrawal</h2>
    <form action="process_withdraw.php" method="POST">
        <input type="hidden" name="method" value="skrill">
        <label>Skrill Email Address</label>
        <input type="email" name="skrill_email" required>
        <label>Amount</label>
        <input type="number" name="amount" step="0.01" required>
        <button type="submit">Submit Withdrawal</button>
    </form>
</div>
</body>
</html>