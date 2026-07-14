<?php
require 'db.php';

if (isset($_POST['account_number'])) {
    $account = $_POST['account_number'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE account_number = ?");
    $stmt->execute([$account]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode($user ?: []);
}
