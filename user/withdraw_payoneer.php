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
    <title>Payoneer Withdrawal</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f3f4f6;
        }
        .container {
            max-width: 500px;
            margin: 70px auto;
            background: white;
            padding: 35px 40px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            color: #2d3436;
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 6px;
            font-weight: 600;
            color: #2f3542;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 12px;
            font-size: 15px;
        }
        button {
            width: 100%;
            padding: 14px;
            background-color: #ff3c3c;
            border: none;
            color: white;
            font-weight: bold;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #e84118;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Payoneer Withdrawal</h2>
    <form action="process_withdraw.php" method="POST">
        <input type="hidden" name="withdraw_method" value="payoneer">

        <label>Payoneer Email</label>
        <input type="email" name="payoneer_email" required>

        <label>Full Name</label>
        <input type="text" name="full_name" required>

        <label>Amount</label>
        <input type="number" name="amount" required>

        <button type="submit" name="submit_withdrawal">Submit Withdrawal</button>
    </form>
</div>
</body>
</html>