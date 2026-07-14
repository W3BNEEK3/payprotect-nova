<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check account status
$stmt = $conn->prepare("SELECT account_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && $user['account_status'] === 'active') {
    // Proceed to next stage (account upgrade)
    header("Refresh: 5; URL=verify_upgrade.php");
} else {
    // Redirect back to suspension page
    header("Location: verify_suspension.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Activated - NovaTrust</title>
    <style>
        body {
            background-color: #e6ffee;
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .container {
            max-width: 480px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            color: green;
        }
        .message {
            margin-top: 20px;
            font-size: 16px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>✅ Congratulations!</h2>
    <p class="message">
        Your NovaTrust account has been successfully activated.<br><br>
        Redirecting you to the next step...
    </p>
</div>

</body>
</html>
