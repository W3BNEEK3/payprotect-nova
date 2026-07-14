<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $user_id = $_POST['user_id'];

  // Generate card details
  $card_number = '4567' . rand(100000000000, 999999999999);
  $expiry_date = date('m/y', strtotime('+3 years'));
  $cvv = rand(100, 999);
  $balance = 50.00; // You can change this starting balance

  // Insert into database
  $stmt = $conn->prepare("INSERT INTO virtual_cards (user_id, card_number, expiry_date, cvv, balance, status) VALUES (?, ?, ?, ?, ?, 'Approved')");
  $stmt->execute([$user_id, $card_number, $expiry_date, $cvv, $balance]);

  // Redirect back to admin card list
  header("Location: admin_virtual_cards.php");
  exit();
}
?>
