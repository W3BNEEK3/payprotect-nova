<!-- forgot_password.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - NovaTrust</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="auth-body">
    <div class="auth-container">
        <h2 class="auth-title">Forgot Password</h2>
        <form action="send_reset.php" method="post" class="auth-form">
            <input type="email" name="email" placeholder="Enter your registered email" required>
            <button type="submit">Send Reset Link</button>
            <div class="auth-links">
                <a href="login.php">Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>
