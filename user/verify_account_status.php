<?php
session_start();
if (!isset($_SESSION['mail_verified'])) {
    header("Location: verify_automated_mail.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['code'] === 'UNLOCK000') {
        $_SESSION['account_status_verified'] = true;
        header("refresh:2;url=verify_upgrade.php");
        exit();
    } else {
        $error = "Invalid Unlock Code.";
    }
}
?>
<!DOCTYPE html>
<html>
<head><title>Account Status</title><link rel="stylesheet" href="../assets/css/style.css"></head>
<body>
<div class="verification-container">
    <h2>Account Activation Required</h2>
    <p>Your account was temporarily suspended. Enter the unlock code to activate it.</p>
    <form method="post">
        <input type="text" name="code" placeholder="Enter Unlock Code" required>
        <button type="submit">Verify</button>
    </form>
    <p class="error"><?php echo $error ?? ''; ?></p>
    <p>Need help? <a href="customer_service.php">Contact Customer Service</a></p>
</div>
</body>
</html>