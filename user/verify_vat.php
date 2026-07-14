<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// ✅ Skip if VAT already verified
$checkStmt = $conn->prepare("SELECT is_vat_verified FROM users WHERE id = ?");
$checkStmt->execute([$user_id]);
$checkRow = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (isset($checkRow['is_vat_verified']) && $checkRow['is_vat_verified'] == 1) {
    header("Location: verify_withdrawal_code.php");
    exit();
}

// ✅ Continue with VAT code check
$stmt = $conn->prepare("SELECT vat_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$correct_code = $row['vat_code'] ?? '';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_code = trim($_POST['vat_code']);

    if (empty($correct_code)) {
        $error = "No VAT code has been assigned to your account. Please contact Customer Service.";
    } elseif ($entered_code !== $correct_code) {
        $error = "Invalid VAT code. Please contact Customer Service.";
    } else {
        $success = true;

        // ✅ Mark VAT as verified
        $update = $conn->prepare("UPDATE users SET is_vat_verified = 1 WHERE id = ?");
        $update->execute([$user_id]);

        // ✅ Redirect to next stage
        header("refresh:2;url=verify_withdrawal_code.php");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>VAT Code Verification</title>
    <style>
        body {
            background-color: #f5f5f5;
            font-family: Arial, sans-serif;
            padding: 40px;
        }
        .container {
            max-width: 500px;
            background: #fff;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            margin-bottom: 10px;
            color: #333;
        }
        p.description {
            color: #555;
            font-size: 15px;
            margin-bottom: 25px;
        }
        input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }
        .btn {
            background-color: #0057a3;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn:hover {
            background-color: #004080;
        }
        .error {
            color: red;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .contact {
            text-align: center;
            margin-top: 15px;
        }
        .contact a {
            color: #0057a3;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>VAT Code Verification</h2>
        <p class="description">
            To ensure compliance with national tax policies and digital financial regulations, a Value Added Tax (VAT) code is required before processing your funds. This helps prevent illegal tax evasion and ensures proper documentation.
        </p>

        <?php if ($success): ?>
            <p class="success">✅ VAT Code Verified. Redirecting...</p>
        <?php else: ?>
            <?php if ($error): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>
            <form method="POST">
                <input type="text" name="vat_code" placeholder="Enter VAT Code" required>
                <button class="btn" type="submit">Verify VAT Code</button>
            </form>
            <div class="contact">
                <p>Need help? <a href="customer_service.php">Contact Customer Service</a></p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
