<?php
session_start();
require 'db.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

$success = $error = "";
$userInfo = null;

// If account number submitted (to fetch user info)
if (isset($_POST['fetch_user'])) {
    $account_number = $_POST['account_number'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        $error = "No user found with that account number.";
    }
}

// If refund is submitted
if (isset($_POST['refund_user'])) {
    $account_number = $_POST['account_number'];
    $amount = floatval($_POST['amount']);
    $reason = $_POST['reason'];

    // Find user again
    $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $new_refund_balance = $user['refund_balance'] + $amount;
        $new_total_balance = $user['balance'] + $amount;

        // Update balances
        $update = $conn->prepare("UPDATE users SET refund_balance = ?, balance = ? WHERE id = ?");
        $update->execute([$new_refund_balance, $new_total_balance, $user['id']]);

        // Log refund
        $log = $conn->prepare("INSERT INTO refunds (user_id, account_number, amount, reason, refunded_by) VALUES (?, ?, ?, ?, ?)");
        $log->execute([$user['id'], $account_number, $amount, $reason, $_SESSION['admin_name']]);

        // Notify user (can expand this to real notification/email)
        $success = "Refund of {$user['currency']} $amount was successfully credited to {$user['firstname']}.";

        // Optional: redirect to receipt or display button to view it
        $userInfo = $user;
    } else {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Refund User - Admin | NovaTrust</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef6ff;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 650px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.08);
        }
        h2 {
            text-align: center;
            color: #1a75ff;
        }
        input, textarea, button {
            width: 100%;
            padding: 12px;
            margin-top: 12px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background-color: #1a75ff;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #0047b3;
        }
        .success { color: green; text-align: center; }
        .error { color: red; text-align: center; }
        .user-info {
            background: #f0f8ff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 15px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>🔁 Refund User (Admin Panel)</h2>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php elseif ($error): ?>
        <p class="error"><?= $error ?></p>
    <?php endif; ?>

    <!-- Step 1: Enter Account Number -->
    <form method="post">
        <label><b>Enter User Account Number</b></label>
        <input type="text" name="account_number" required value="<?= isset($_POST['account_number']) ? htmlspecialchars($_POST['account_number']) : '' ?>">
        <button type="submit" name="fetch_user">Fetch User Info</button>
    </form>

    <!-- Show User Info if Found -->
    <?php if ($userInfo): ?>
        <div class="user-info">
            <b>Name:</b> <?= htmlspecialchars($userInfo['firstname'] . ' ' . $userInfo['lastname']) ?><br>
            <b>Email:</b> <?= htmlspecialchars($userInfo['email']) ?><br>
            <b>Currency:</b> <?= $userInfo['currency'] ?><br>
            <b>Current Refund Balance:</b> <?= $userInfo['currency'] . ' ' . number_format($userInfo['refund_balance'], 2) ?><br>
            <b>Total Balance:</b> <?= $userInfo['currency'] . ' ' . number_format($userInfo['balance'], 2) ?>
        </div>

        <!-- Refund Form -->
        <form method="post">
            <input type="hidden" name="account_number" value="<?= htmlspecialchars($userInfo['account_number']) ?>">
            <label><b>Refund Amount (<?= $userInfo['currency'] ?>)</b></label>
            <input type="number" name="amount" step="0.01" required>

            <label><b>Reason for Refund</b></label>
            <textarea name="reason" required placeholder="e.g., Failed transaction, service delay..."></textarea>

            <button type="submit" name="refund_user">💰 Process Refund</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
