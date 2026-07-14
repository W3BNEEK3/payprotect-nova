<?php
session_start();
require_once "../config/config.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];

    // Check if a request already exists
    $stmt = $conn->prepare("SELECT * FROM virtual_cards WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['message'] = "You’ve already requested a virtual card.";
        header("Location: virtual_card.php");
        exit();
    }

    // Insert request
    $stmt = $conn->prepare("INSERT INTO virtual_cards (user_id, is_approved) VALUES (?, 0)");
    $stmt->execute([$user_id]);

    $_SESSION['message'] = "Card request sent. Please wait for admin approval.";
    header("Location: virtual_card.php");
    exit();
}
?>
