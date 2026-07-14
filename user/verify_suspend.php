<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Suspension Notice - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
        }
        .container {
            background: white;
            max-width: 600px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
            text-align: center;
        }
        h2 {
            color: darkred;
            margin-bottom: 15px;
        }
        p {
            font-size: 16px;
            color: #444;
        }
        .contact-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: maroon;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
        }
        .contact-btn:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🚨 Account Suspension Notice</h2>
    <p>Your account has been temporarily suspended due to unusual activity. To reactivate your account and resume banking services, a security reactivation fee is required.</p>
    <p>For your safety, please contact our Customer Service to process this.</p>
    
    <a href="customer_service.php" class="contact-btn">Contact Customer Service</a>
</div>
</body>
</html>
