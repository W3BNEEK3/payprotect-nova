<?php
include '../database/db.php';

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Show reset form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Reset Password</title>
            <link rel="stylesheet" href="../assets/css/style.css">
        </head>
        <body class="auth-body">
            <div class="auth-container">
                <h2 class="auth-title">Reset Your Password</h2>
                <form action="reset_password.php" method="post" class="auth-form">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    <button type="submit">Update Password</button>
                </form>
            </div>
        </body>
        </html>
        <?php
    } else {
        echo "Invalid or expired token.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST["token"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    if ($new_password !== $confirm_password) {
        die("Passwords do not match.");
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?");
    $stmt->execute([$hashed_password, $token]);

    echo "<p>Password updated successfully. <a href='login.php'>Login here</a></p>";
}
?>
