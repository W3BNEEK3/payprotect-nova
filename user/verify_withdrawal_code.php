<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Skip if already verified
$checkStmt = $conn->prepare("SELECT is_withdrawal_verified FROM users WHERE id = ?");
$checkStmt->execute([$user_id]);
$checkRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (isset($checkRow['is_withdrawal_verified']) && $checkRow['is_withdrawal_verified'] == 1) {
    header("Location: verify_ars.php");
    exit();
}

// ✅ Get correct withdrawal code from database
$stmt = $conn->prepare("SELECT withdrawal_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$correct_code = $row['withdrawal_code'] ?? '';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = trim($_POST['withdrawal_code']);

    if (empty($correct_code)) {
        $error = "No withdrawal code has been assigned to your account. Please contact Customer Service.";
    } elseif ($entered_code !== $correct_code) {
        $error = "Invalid withdrawal code.";
    } else {
        $success = true;

        // ✅ Mark withdrawal code as verified
        $update = $conn->prepare("UPDATE users SET is_withdrawal_verified = 1 WHERE id = ?");
        $update->execute([$user_id]);

        // ✅ Go to next stage
        header("refresh:2;url=verify_ars.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Withdrawal Code Verification</title>
    <style>
        body { background-color: #f5f5f5; font-family: Arial; padding: 40px; }
        .container {
            max-width: 500px; background: #fff; margin: auto; padding: 30px;
            border-radius: 10px; box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        h2 { color: #333; }
        p.description { color: #555; font-size: 15px; margin-bottom: 25px; }
        input[type="text"] {
            width: 100%; padding: 12px; margin-bottom: 15px;
            border: 1px solid #ccc; border-radius: 6px; font-size: 16px;
        }
        .btn {
            background-color: #0057a3; color: white; padding: 12px 20px;
            border: none; border-radius: 6px; cursor: pointer;
            width: 100%; font-size: 16px;
        }
        .btn:hover { background-color: #004080; }
        .error { color: red; margin-bottom: 10px; font-weight: bold; }
        .success { color: green; font-weight: bold; }
        .contact { text-align: center; margin-top: 15px; }
        .contact a { color: #0057a3; text-decoration: none; font-weight: bold; }
    </style>
</head>
<body>
<div class="container">
    <h2>Withdrawal Code Verification</h2>
    <p class="description">
        This code is issued for security validation before releasing withdrawals. It helps prevent unauthorized fund access and confirms account legitimacy.
    </p>

    <?php if ($success): ?>
        <p class="success">✅ Withdrawal Code Verified. Redirecting...</p>
    <?php else: ?>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="withdrawal_code" placeholder="Enter Withdrawal Code" required>
            <button class="btn" type="submit">Verify Withdrawal Code</button>
        </form>
        <div class="contact">
            <p>Need help? <a href="customer_service.php">Contact Customer Service</a></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
