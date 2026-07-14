<?php
session_start();
require_once '../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$flow = $_GET['flow'] ?? ''; // withdrawal or empty

// ✅ Fetch virtual card
$stmt = $conn->prepare("SELECT * FROM virtual_cards WHERE user_id = ?");
$stmt->execute([$user_id]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);

// ✅ Check approval
$isApproved = $card && $card['is_virtual_card_approved'] == 1;

// ✅ Set flag if it should redirect (but only once)
if ($isApproved && $flow === 'withdrawal') {
    // Mark that virtual card step is cleared for this user
    $update = $conn->prepare("UPDATE users SET is_virtual_card_cleared = 1 WHERE id = ?");
    $update->execute([$user_id]);

    // Redirect to KYC next
    header("Location: verify_kyc.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your NovaTrust Virtual Card</title>
    <link rel="stylesheet" href="../assets/css/virtual_card_style.css">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f9f9f9; padding: 40px; }
        .card-container {
            max-width: 500px; margin: auto; background: white;
            padding: 30px; border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .virtual-card {
            background: linear-gradient(135deg, #0074D9, #001f3f);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            font-family: monospace;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .bank-name {
            font-size: 20px;
            font-weight: bold;
        }
        .chip {
            height: 30px;
        }
        .card-details {
            margin-top: 15px;
            text-align: left;
        }
        .card-row {
            margin-bottom: 12px;
        }
        .card-row label {
            font-size: 12px;
            display: block;
            color: #ccc;
        }
        .card-row div {
            font-size: 18px;
            letter-spacing: 1px;
        }
        .card-row.bottom {
            display: flex;
            justify-content: space-between;
        }

        .card-reason {
            text-align: left;
            margin-top: 25px;
        }
        .card-reason h4 {
            margin-bottom: 10px;
        }
        .card-reason ul {
            list-style: none;
            padding-left: 0;
        }
        .card-reason ul li {
            margin-bottom: 10px;
        }

        .btn-request {
            display: inline-block;
            margin-top: 15px;
            background-color: #0074D9;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
        }

    </style>
</head>
<body>
<div class="card-container">
    <h2>Your NovaTrust Virtual Card</h2>

    <div class="card-warning" style="background:#fff3cd;color:#856404;padding:12px 18px;border-radius:8px;margin-bottom:18px;border:1px solid #ffeeba;font-size:15px;">
        <strong>Warning:</strong> Failure to abide by NovaTrust company policy may result in your virtual card being blocked. Please use your card responsibly and in accordance with our terms.
    </div>

    <div class="virtual-card">
        <div class="card-header">
            <span class="bank-name">NovaTrust</span>
            <img src="../assets/icons/icons/virtual_card_chip_only.png" alt="chip" class="chip">
        </div>
        <div class="card-details">
            <div class="card-row">
                <label>Card Number</label>
                <div><?= $isApproved ? $card['card_number'] : '•••• •••• •••• ••••' ?></div>
            </div>
            <div class="card-row">
                <label>Cardholder Name</label>
                <div><?= $isApproved ? $card['cardholder_name'] : '••••••••••••' ?></div>
            </div>
            <div class="card-row bottom">
                <div>
                    <label>Expiry</label>
                    <div><?= $isApproved ? $card['expiry_date'] : 'MM/YY' ?></div>
                </div>
                <div>
                    <label>CVV</label>
                    <div><?= $isApproved ? $card['cvv'] : '•••' ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card-reason">
        <h4>Why you need a Virtual Card</h4>
        <ul>
            <li>✔️ Required for secure international withdrawals</li>
            <li>✔️ Protects your transactions with encrypted processing</li>
            <li>✔️ Mandatory for merchant compliance</li>
        </ul>

        <?php if (!$isApproved): ?>
            <a href="customer_service.php" class="btn-request">Request Virtual Card</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
