<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $subject = $_POST['subject'];
    $content = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO support (user_id, subject, message) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $subject, $content]);

    $message = "Message sent successfully!";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Support - NovaTrust</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body class="support-page">
<div class="support-box">
    <h2>Contact Support</h2>
    <?php if ($message): ?>
        <div class="support-success"><?php echo $message; ?></div>
    <?php endif; ?>
    <form method="post" class="support-form">
        <input type="text" name="subject" placeholder="Subject" required>
        <textarea name="message" rows="5" placeholder="Your message here..." required></textarea>
        <button type="submit" class="support-button">Send Message</button>
    </form>
</div>
<script src="//code.tidio.co/qp9l90ajcrfdgztr6oydtvtt6qldij7e.js" async></script>
</body>
</html>
