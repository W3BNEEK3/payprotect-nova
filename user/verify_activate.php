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
    <title>Reactivate Your Account - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #eef2f7;
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
            color: darkgreen;
        }
        p {
            font-size: 16px;
            color: #444;
        }
        .contact-btn {
            display: inline-block;
            margin-top: 20px;
            background-color: green;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
        }
        .contact-btn:hover {
            background-color: darkgreen;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🔓 Reactivate Your Account</h2>
    <p>Your request to reactivate your account is being processed. For full reactivation, a compliance fee may be required.</p>
    <p>Our Customer Service will guide you through the next step securely.</p>
    
    <a href="customer_service.php" class="contact-btn">Contact Customer Service</a>
</div>
</body>
</html>
