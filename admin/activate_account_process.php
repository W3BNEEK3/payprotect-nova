<?php
session_start();
require_once "../database/db.php";

// Only admin
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: admin_login.php");
    exit();
}

if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];

    // Activate user
    $stmt = $conn->prepare("UPDATE users SET account_status = 'active' WHERE id = ?");
    $stmt->execute([$user_id]);

    $_SESSION['message'] = "Account activated successfully.";
}

header("Location: admin_suspend.php");
exit();
