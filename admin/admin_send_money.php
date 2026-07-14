
<?php
session_start();
include '../database/db.php';

// Only allow admin access
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

$success = "";
$error = "";
$user = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fetch_user'])) {
        $account_number = $_POST['account_number'];

        // Fetch user by account number
        $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ?");
        $stmt->execute([$account_number]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "User not found.";
        }
    }

    if (isset($_POST['send_money'])) {
        $account_number = $_POST['account_number'];
        $amount = $_POST['amount'];
        $reason = $_POST['reason'];

        // Fetch user
        $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ?");
        $stmt->execute([$account_number]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $error = "User not found.";
        } else {
            $user_id = $user['id'];
            $currency = $user['currency'];

            // Update user's balance
            $stmt = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$amount, $user_id]);

            // Save transaction
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, amount, currency, type, message, receiver_id) VALUES (?, ?, ?, 'credit', ?, ?)");
            $stmt->execute([$user_id, $amount, $currency, $reason, $user_id]);

            $transaction_id = $conn->lastInsertId();

            // Save notification
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, 'alert')");
            $stmt->execute([
                $user_id,
                "💰 Credit Alert",
                "You have received " . $currency . number_format($amount, 2) . " from NovaTrust Bank. Reason: $reason",
            ]);

            header("Location: receipt.php?transaction_id=$transaction_id");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Money - Admin</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f3f3f3; padding: 20px; }
        .container { max-width: 600px; margin: auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: bold; margin-bottom: 5px; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .user-info { background: #eef; padding: 10px; border-radius: 5px; margin-top: 10px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>

<div class="container">
    <h2>Send Money</h2>

    <?php if ($error): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <div class="form-group">
            <label>Enter Account Number:</label>
            <input type="text" name="account_number" required>
        </div>
        <button type="submit" name="fetch_user">Fetch User</button>
    </form>

    <?php if ($user): ?>
        <div class="user-info">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Currency:</strong> <?php echo htmlspecialchars($user['currency']); ?></p>
        </div>

        <form method="post" style="margin-top: 20px;">
            <input type="hidden" name="account_number" value="<?php echo htmlspecialchars($user['account_number']); ?>">
            <div class="form-group">
                <label>Amount:</label>
                <input type="number" name="amount" step="0.01" required>
            </div>
            <div class="form-group">
                <label>Reason:</label>
                <input type="text" name="reason" required>
            </div>
            <button type="submit" name="send_money">Send Money</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
