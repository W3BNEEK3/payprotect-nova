<?php
session_start();
require_once "../database/db.php";


$user_id = $_SESSION["user_id"] ?? null;

if (!$user_id) {
    header("Location: login.php");
    exit();
}

// ✅ Skip if already verified
$check = $conn->prepare("SELECT is_kyc_verified FROM users WHERE id = ?");
$check->execute([$user_id]);
$status = $check->fetch(PDO::FETCH_ASSOC);

if ($status && $status['is_kyc_verified'] == 1) {
    header("Location: verify_suspension.php");
    exit();
}

// ✅ Fetch KYC code
$stmt = $conn->prepare("SELECT kyc_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);
$stored_kyc = $row['kyc_code'] ?? null;

$error = '';
$success = false;

// ✅ When user submits form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kyc_code = trim($_POST["kyc_code"]);

    if ($kyc_code === $stored_kyc) {
        // ✅ Mark KYC as verified
        $update = $conn->prepare("UPDATE users SET is_kyc_verified = 1, account_status = 'suspended' WHERE id = ?");
        $update->execute([$user_id]);

        $success = true;

        // ✅ Redirect to suspension page
        header("refresh:2;url=verify_suspension.php");
    } else {
        $error = "Invalid KYC code. Please contact customer service.";
    }
}
?>

<!-- Frontend -->
<!DOCTYPE html>
<html>
<head>
    <title>KYC Verification</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body>
    <div class="verification-container">
        <h2 class="verification-title">KYC Verification</h2>
        <p>Please enter the KYC verification code sent to you. This is required to verify your identity and continue banking securely.</p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post" class="verification-form">
            <input type="text" 
                   name="kyc_code" 
                   placeholder="Enter KYC Code" 
                   required 
                   class="verification-input">
            <button type="submit" class="verification-button">Verify</button>
        </form>
        
        <p style="margin-top: 20px; text-align: center;">
            <a href="customer_service.php">Contact Customer Service</a>
        </p>
    </div>
</body>
</html>
