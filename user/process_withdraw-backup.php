<?php
session_start();
require('../database/db.php');

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $method = $_POST['withdraw_method'];

    // Save form data to session (for verification phase)
    $_SESSION['withdraw_method'] = $method;

    // Optional: Save specific form data (like PayPal email, crypto address)
    if ($method === 'paypal') {
        $_SESSION['paypal_email'] = $_POST['paypal_email'];
    } elseif ($method === 'crypto') {
        $_SESSION['crypto_coin'] = $_POST['crypto_coin'];
        $_SESSION['crypto_network'] = $_POST['crypto_network'];
        $_SESSION['wallet_address'] = $_POST['wallet_address'];
    }
    // Add other methods here as needed...

    // Now begin code verification chain
    header("Location: verify_imf.php");
    exit();
} else {
    echo "Invalid request.";
    exit();
}
