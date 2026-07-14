<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch user data
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$firstname = $user['firstname'];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Customer Service | NovaTrust</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body class="customer-service-page">

<div class="service-header">
    NovaTrust Customer Service
</div>

<div class="service-container">
    <h2>Hi <?php echo htmlspecialchars($firstname); ?>, how can we assist you?</h2>
    <p class="support-info">Our live support agent is available below using chat.</p>
    
    <div class="chat-box">
        <p><strong>Live Chat:</strong> Click the chat bubble at the bottom-right of the screen to begin chatting with support.</p>
    </div>

    <p class="note">Note: Live chat is only available during working hours. You may leave a message if we're offline.</p>
</div>

<script src="//code.tidio.co/egy2jj6ihpe6ltp2760itz5shteyzupk.js" async></script>
</body>
</html>
