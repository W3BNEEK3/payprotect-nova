<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications - NovaTrust</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/user-styles.css">
</head>
<body>

<div class="notification-container">
    <h2 class="notification-title">📩 Notifications</h2>

    <?php if (empty($notifications)): ?>
        <div class="no-notifications">You have no notifications yet.</div>
    <?php else: ?>
        <?php foreach ($notifications as $n): ?>
            <div class="notification <?php echo htmlspecialchars($n['type']); ?>">
                <div class="notification-title"><?php echo htmlspecialchars($n['title']); ?></div>
                <div class="notification-message"><?php echo htmlspecialchars($n['message']); ?></div>
                <div class="notification-timestamp"><?php echo date('M j, Y • h:i A', strtotime($n['created_at'])); ?></div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
