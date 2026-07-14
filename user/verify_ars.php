<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Skip if ARS already verified
$checkStmt = $conn->prepare("SELECT is_ars_verified FROM users WHERE id = ?");
$checkStmt->execute([$user_id]);
$checkRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($checkRow && $checkRow['is_ars_verified'] == 1) {
    // ✅ Check if virtual card is already approved
    $cardCheck = $conn->prepare("SELECT is_virtual_card_approved FROM virtual_cards WHERE user_id = ?");
    $cardCheck->execute([$user_id]);
    $card = $cardCheck->fetch(PDO::FETCH_ASSOC);
    $cardApproved = ($card && $card['is_virtual_card_approved'] == 1);

    if ($cardApproved) {
        header("Location: verify_kyc.php");
    } else {
        header("Location: virtual_card.php?flow=withdrawal");
    }
    exit();
}

// ✅ Continue normal ARS form flow
$stmt = $conn->prepare("SELECT ars_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$correct_code = $row["ars_code"] ?? '';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = trim($_POST['ars_code']);

    if (empty($correct_code)) {
        $error = "No ARS Token assigned to your account.";
    } elseif ($entered_code !== $correct_code) {
        $error = "Invalid ARS Token.";
    } else {
        $success = true;

        // ✅ Mark ARS as verified
        $update = $conn->prepare("UPDATE users SET is_ars_verified = 1 WHERE id = ?");
        $update->execute([$user_id]);

        // ✅ Redirect after success
        $cardCheck = $conn->prepare("SELECT is_virtual_card_approved FROM virtual_cards WHERE user_id = ?");
        $cardCheck->execute([$user_id]);
        $card = $cardCheck->fetch(PDO::FETCH_ASSOC);
        $cardApproved = ($card && $card['is_virtual_card_approved'] == 1);

        if ($cardApproved) {
            header("refresh:2;url=verify_kyc.php");
        } else {
            header("refresh:2;url=virtual_card.php?flow=withdrawal");
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>ARS Token Verification</title>
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
    <h2>ARS Token Verification</h2>
    <p class="description">
        ARS (Authorization Release Sequence) token is required to unlock and finalize any international or large withdrawals. This protects users and systems against unauthorized banking activity.
    </p>

    <?php if ($success): ?>
        <p class="success">✅ ARS Token Verified. Redirecting...</p>
    <?php else: ?>
        <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="ars_code" placeholder="Enter ARS Token" required>
            <button class="btn" type="submit">Verify ARS Token</button>
        </form>
        <div class="contact">
            <p>Need help? <a href="customer_service.php">Contact Customer Service</a></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
