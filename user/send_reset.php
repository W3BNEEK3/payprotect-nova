<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $token = bin2hex(random_bytes(50)); // Generate secure token

    // Check if user exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Save token
        $stmt = $conn->prepare("UPDATE users SET reset_token = ? WHERE email = ?");
        $stmt->execute([$token, $email]);

        // Simulate email sending by showing the reset link
        echo "<p style='text-align:center;'>Reset link: <a href='reset_password.php?token=$token'>Click here to reset password</a></p>";
    } else {
        echo "Email not found.";
    }
}
?>
