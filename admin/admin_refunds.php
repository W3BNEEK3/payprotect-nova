<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$userInfo = null;

// Lookup if account number is submitted
if (isset($_POST['lookup_account'])) {
    $account_number = $_POST['account_number'];
    $stmt = $conn->prepare("SELECT id, firstname, lastname, currency, refunded_balance FROM users WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$userInfo) {
        $message = "User with account number $account_number not found.";
    }
}

// Process refund
if (isset($_POST['process_refund'])) {
    $account_number = $_POST['account_number'];
    $amount = $_POST['amount'];
    $reason = $_POST['reason'];
    $refunded_by = $_SESSION['admin_name'];

    // Get user details
    $stmt = $conn->prepare("SELECT id, currency FROM users WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $user_id = $user['id'];

        // Update balance and refunded balance
        $conn->prepare("UPDATE users SET refunded_balance = refunded_balance + ?, balance = balance + ? WHERE id = ?")
              ->execute([$amount, $amount, $user_id]);

        // Insert refund log
        $conn->prepare("INSERT INTO refunds (user_id, account_number, amount, reason, refunded_by) VALUES (?, ?, ?, ?, ?)")
              ->execute([$user_id, $account_number, $amount, $reason, $refunded_by]);

        $message = "Refund of {$user['currency']}{$amount} to account $account_number was successful.";
    } else {
        $message = "Account not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Refund a User</title>
    <style>
        body {
            font-family: Arial;
            background: #f3f3f3;
            padding: 40px;
        }

        .container {
            max-width: 500px;
            background: white;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0,0,0,0.2);
        }

        input, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            font-size: 16px;
        }

        button {
            margin-top: 15px;
            padding: 10px 20px;
            background: steelblue;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .info-box {
            margin-top: 15px;
            padding: 10px;
            background: #e7f5ff;
            border-left: 4px solid steelblue;
        }

        .message {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            margin-top: 15px;
            border-left: 5px solid #ffeeba;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Refund a User</h2>

    <form method="POST">
        <label>Enter Account Number</label>
        <input type="text" name="account_number" value="<?php echo $_POST['account_number'] ?? ''; ?>" required>
        <button type="submit" name="lookup_account">Lookup</button>
    </form>

    <?php if ($userInfo): ?>
        <div class="info-box">
            <strong>User Found:</strong><br>
            Name: <?php echo htmlspecialchars($userInfo['firstname'] . ' ' . $userInfo['lastname']); ?><br>
            Currency: <?php echo htmlspecialchars($userInfo['currency']); ?><br>
            Refunded Balance: <?php echo $userInfo['currency'] . number_format($userInfo['refunded_balance'], 2); ?>
        </div>

        <form method="POST">
            <input type="hidden" name="account_number" value="<?php echo $_POST['account_number']; ?>">

            <label>Refund Amount</label>
            <input type="number" name="amount" step="0.01" required>

            <label>Reason</label>
            <textarea name="reason" rows="3" required></textarea>

            <button type="submit" name="process_refund">Send Refund</button>
        </form>
    <?php endif; ?>

    <?php if ($message): ?>
        <div class="message"><?php echo $message; ?></div>
    <?php endif; ?>
</div>

</body>
</html>
