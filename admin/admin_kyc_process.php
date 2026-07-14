<?php
session_start();
include '../database/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $kyc_code = $_POST['kyc_code'];

    $stmt = $conn->prepare("UPDATE users SET kyc_code = ? WHERE id = ?");
    $stmt->execute([$kyc_code, $user_id]);

    header("Location: admin_kyc_requests.php?approved=1");
    exit();
}
?>
