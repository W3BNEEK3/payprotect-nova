<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['kyc_verified'])) {
    header("Location: verify_kyc.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Automated Mail Fee - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="verification-body">
    <div class="verification-container">
        <h2 class="verification-title">Automated Mail Notification Fee</h2>
        <p class="verification-text">Your account must settle the automated debit/credit mail alert fee to receive real-time bank alerts for every transaction.</p>
        <p class="verification-text">Please contact Customer Service to resolve this charge and proceed.</p>
        <a href="customer_service.php" class="verification-button">📞 Contact Customer Service</a>
    </div>
</body>
</html>