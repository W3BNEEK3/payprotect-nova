<?php
require_once '../config/config.php';

if (!isset($_GET['user_id'])) {
    die('User ID missing');
}
$user_id = intval($_GET['user_id']);

/* does a card row already exist? */
$check = $conn->prepare("SELECT id FROM virtual_cards WHERE user_id = ?");
$check->execute([$user_id]);
$card = $check->fetch(PDO::FETCH_ASSOC);

if ($card) {
    /* row exists → just mark approved */
    $conn->prepare("
        UPDATE virtual_cards
        SET is_virtual_card_approved = 1
        WHERE user_id = ?
    ")->execute([$user_id]);
} else {
    /* no row yet → create an approved card with fresh data */
    $card_number     = '4' . rand(1000,9999).' '.rand(1000,9999).' '.rand(1000,9999).' '.rand(1000,9999);
    $expiry_date     = str_pad(rand(1,12),2,'0',STR_PAD_LEFT) . '/' . (date('y')+3);
    $cvv             = rand(100,999);
    $holder_stmt     = $conn->prepare("SELECT fullname, firstname, lastname FROM users WHERE id = ?");
    $holder_stmt->execute([$user_id]);
    $holder_row = $holder_stmt->fetch(PDO::FETCH_ASSOC);
    $holder = $holder_row['fullname'] ?: trim(($holder_row['firstname'] ?? '') . ' ' . ($holder_row['lastname'] ?? ''));
    if (!$holder) $holder = 'Cardholder';

    $conn->prepare("
        INSERT INTO virtual_cards
            (user_id, card_number, expiry_date, cvv, cardholder_name, is_virtual_card_approved)
        VALUES (?,?,?,?,?,1)
    ")->execute([$user_id,$card_number,$expiry_date,$cvv,$holder]);
}

/* optional – flag user table for flow control */
$conn->prepare("
    UPDATE users
    SET is_virtual_card_cleared = 1
    WHERE id = ?
")->execute([$user_id]);

header("Location: admin_virtual_cards.php?success=1");
exit();
?>
