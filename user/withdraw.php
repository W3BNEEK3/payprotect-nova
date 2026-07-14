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
    is_virtual_card_cleared, is_kyc_verified, is_upgraded, is_upgrade_verified
    FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch compliance status
$totalComplianceStmt = $conn->prepare("SELECT COUNT(*) FROM user_compliance_codes WHERE user_id = ?");
$totalComplianceStmt->execute([$user_id]);
$totalComplianceAssignments = (int)$totalComplianceStmt->fetchColumn();

$pendingComplianceStmt = $conn->prepare("SELECT COUNT(*) FROM user_compliance_codes WHERE user_id = ? AND is_cleared = 0");
$pendingComplianceStmt->execute([$user_id]);
$pendingComplianceAssignments = (int)$pendingComplianceStmt->fetchColumn();

$balance = floatval($user['balance']);
$success = $error = '';
if (!empty($_SESSION['compliance_success'])) {
 $success = $_SESSION['compliance_success'];
 unset($_SESSION['compliance_success']);
}

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
        $allCleared =
            ($user['is_virtual_card_cleared'] ?? 0) &&
            ($user['is_kyc_verified'] ?? 0) &&
            ($user['is_upgraded'] ?? 0) &&
            ($user['is_upgrade_verified'] ?? 0);

        $hasCompliance = $totalComplianceAssignments > 0;
        $complianceCleared = $hasCompliance && $pendingComplianceAssignments === 0;

        if (!$hasCompliance || $pendingComplianceAssignments > 0) {
            $_SESSION['withdraw_amount'] = $amount;
            $_SESSION['withdraw_method_selected'] = $_POST['method'];
            $_SESSION['withdraw_return_url'] = 'withdraw.php';
            header("Location: process_withdraw.php");
            exit();
        }

        if ($allCleared && $complianceCleared) {
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
            // ✅ Store amount and continue with the next verification stages
            $_SESSION['withdraw_amount'] = $amount;
            $_SESSION['withdraw_method_selected'] = $_POST['method'];
            $_SESSION['withdraw_return_url'] = 'withdraw.php';
            header("Location: virtual_card.php?flow=withdrawal");
            exit();
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<?php if ($success): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (window.NTToast && NTToast.show) {
                NTToast.show('success', 'Success', <?php echo json_encode($success); ?>);
            }
        });
    </script>
<?php endif; ?>

<?php if ($error): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            if (window.NTToast && NTToast.show) {
                NTToast.show('error', 'Error', <?php echo json_encode($error); ?>);
            }
        });
    </script>
<?php endif; ?>

<div class="content-page">
    <div class="content-card">
        <div class="page-header">
            <h2>Withdraw Funds</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
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
            
            <button type="submit" class="btn btn-primary">
                Proceed with Withdrawal
            </button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
