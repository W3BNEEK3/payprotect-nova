<?php
require_once '../config/config.php';

if (!isset($_GET['card_id'])) {
    die('Card ID missing');
}
$card_id = intval($_GET['card_id']);

// Get card and user
$stmt = $conn->prepare("SELECT user_id FROM virtual_cards WHERE id = ?");
$stmt->execute([$card_id]);
$card = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$card) {
    die('Card not found');
}
$user_id = $card['user_id'];

// Block the card
$conn->prepare("UPDATE virtual_cards SET status = 'Blocked', is_virtual_card_approved = 0 WHERE id = ?")->execute([$card_id]);

// Notify the user
$conn->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)")
    ->execute([
        $user_id,
        'Virtual Card Blocked',
        'Your virtual card has been blocked due to defaulting company policy.',
        'warning'
    ]);

header('Location: admin_virtual_cards.php?blocked=1');
exit(); 