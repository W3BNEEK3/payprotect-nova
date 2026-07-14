<?php
session_start();
require_once "../database/db.php";

$user_id = $_SESSION["user_id"] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Check account status only
$stmt = $conn->prepare("SELECT account_status FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$status = $user['account_status'] ?? '';

// If account is active, show congratulations and redirect
if ($status === 'active') {
    echo '
    <html>
    <head>
        <title>Account Activated</title>
        <meta http-equiv="refresh" content="5;url=account_upgrade.php" />
        <style>
            body { background: #f4f6f9; font-family: Arial, sans-serif; text-align: center; padding-top: 100px; }
            .box { background: white; display: inline-block; padding: 40px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); animation: fadeIn 1s ease-in-out; }
            h1 { color: #2ecc71; font-size: 32px; margin-bottom: 20px; }
            p { font-size: 18px; color: #555; }
            @keyframes fadeIn { from {opacity: 0;} to {opacity: 1;} }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>🎉 Congratulations!</h1>
            <p>Your NovaTrust account has been successfully activated.</p>
            <p>Redirecting to Account Upgrade...</p>
        </div>
    </body>
    </html>';
    exit();
}
// If still suspended
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account Suspended</title>
    <style>
        body { background-color: #f8f8f8; font-family: 'Segoe UI', sans-serif; padding: 60px; text-align: center; }
        .container { background: white; display: inline-block; padding: 40px; border-radius: 10px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        h2 { color: red; margin-bottom: 20px; }
        p { color: #555; font-size: 18px; }
        a { margin-top: 20px; display: inline-block; text-decoration: none; background: #0066cc; color: white; padding: 10px 20px; border-radius: 5px; }
        a:hover { background: #004da8; }
    </style>
</head>
<body>
    <div class="container">
        <h2>🚫 Account Suspended</h2>
        <p>Your account has been temporarily suspended for security verification.</p>
        <p>Please contact NovaTrust Customer Service to activate your account.</p>
        <a href="customer_service.php">Contact Customer Service</a>
    </div>
</body>
</html>
