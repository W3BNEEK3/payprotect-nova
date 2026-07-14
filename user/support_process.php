<?php
include '../database/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $date_sent = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO support_requests (email, subject, message, date_sent) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $subject, $message, $date_sent]);

    header("Location: support.php?sent=1");
    exit();
} else {
    header("Location: support.php");
    exit();
}
