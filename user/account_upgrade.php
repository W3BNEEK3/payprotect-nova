<?php
session_start();
require_once "../config/config.php";

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Check if this is the withdrawal flow
$flow = $_GET['flow'] ?? '';

// Fetch user upgrade status
$stmt = $conn->prepare("SELECT account_type, is_upgrade_verified FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$account_type = $user['account_type'] ?? '';
$is_verified = $user['is_upgrade_verified'] ?? 0;

// ✅ If upgraded and not marked as verified, show congratulations ONCE
if ($account_type === 'upgraded' && !$is_verified) {
    // Mark upgrade as verified so this message doesn't show again
    $update = $conn->prepare("UPDATE users SET is_upgrade_verified = 1 WHERE id = ?");
    $update->execute([$user_id]);

    echo "
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e6f2ff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .box {
            background: white;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        h1 {
            color: green;
            font-size: 24px;
        }
    </style>
    <div class='box'>
        <h1>🎉 Congratulations!</h1>
        <p>Your account has been successfully upgraded. You now have full access to all NovaTrust banking features including:</p>
        <ul style='text-align:left;'>
            <li>✅ International transactions</li>
            <li>✅ Large withdrawal limits</li>
            <li>✅ Priority transaction processing</li>
            <li>✅ Enhanced fraud protection</li>
        </ul>
        <p>You will now be redirected to your dashboard.</p>
    </div>
    <script>
        setTimeout(() => {
            window.location.href = 'dashboard.php';
        }, 5000);
    </script>
    ";
    exit();
}

// ✅ If already upgraded and already verified → just redirect directly to dashboard
if ($account_type === 'upgraded' && $is_verified == 1) {
    header("Location: dashboard.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Upgrade Required</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 40px;
        }
        .container {
            max-width: 600px;
            background-color: white;
            padding: 30px;
            margin: auto;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            text-align: center;
        }
        ul {
            margin-top: 20px;
            line-height: 1.6;
        }
        .notice {
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            padding: 15px;
            border-radius: 5px;
            color: #856404;
            margin-top: 20px;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🔒 Account Upgrade Required</h2>
    <p>To continue enjoying uninterrupted banking services, your account needs to be upgraded. This is a mandatory security and compliance process required by international financial regulations.</p>

    <strong>Why do I need to upgrade?</strong>
    <ul>
        <li>✔ To unlock high-limit withdrawals</li>
        <li>✔ To send and receive international payments</li>
        <li>✔ To access advanced NovaTrust features</li>
        <li>✔ To comply with updated global banking security protocols</li>
    </ul>

    <div class="notice">
        🚨 Your account upgrade is pending approval by our compliance team. Kindly <strong>contact Customer Service</strong> to finalize your upgrade.
    </div>

    <a href="customer_service.php" class="btn">Contact Customer Service</a>
</div>
</body>
</html>
