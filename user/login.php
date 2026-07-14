<?php
session_start();
include '../database/db.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>NovaTrust Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <div class="form-container">
            <h2>NovaTrust Login</h2>
            <form action="login_process.php" method="POST">
                <input type="email" name="email" placeholder="Email address" required class="form-input">
                <input type="password" name="password" placeholder="Password" required class="form-input">
                <button type="submit" class="btn-primary">Login</button>
            </form>
            <div class="extra-links">
                <a href="forgot_password.php">Forgot Password?</a>
                <a href="register.php">Create New Account</a>
            </div>
        </div>
    </div>
 <script src="//code.tidio.co/egy2jj6ihpe6ltp2760itz5shteyzupk.js" async></script>
</body>
</html>
