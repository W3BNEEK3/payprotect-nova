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
    <title>Western Union Withdrawal</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f1f1f1; }
        .container {
            max-width: 500px; margin: 40px auto;
            background: #fff; padding: 25px;
            border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #333; }
        label { display: block; margin-top: 15px; color: #333; }
        input[type="text"], input[type="number"] {
            width: 100%; padding: 10px; margin-top: 5px;
            border-radius: 5px; border: 1px solid #ccc;
        }
        button {
            width: 100%; margin-top: 20px; padding: 12px;
            background-color: #28a745; color: white;
            border: none; border-radius: 5px;
            font-size: 16px; cursor: pointer;
        }
        button:hover { background-color: #218838; }
    </style>
</head>
<body>
<div class="container">
    <h2>Western Union Withdrawal</h2>
    <form action="process_withdraw.php" method="post">
        <input type="hidden" name="method" value="westernunion">

        <label for="receiver_name">Receiver's Full Name:</label>
        <input type="text" name="receiver_name" required>

        <label for="receiver_country">Receiver's Country:</label>
        <input type="text" name="receiver_country" required>

        <label for="amount">Amount:</label>
        <input type="number" name="amount" step="0.01" required>

        <button type="submit">Submit Withdrawal</button>
    </form>
</div>
</body>
</html>
