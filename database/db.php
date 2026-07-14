<?php
$host = 'localhost';
$db   = 'harmony1_novar_DB'; // this must match what you see in phpMyAdmin
$user = 'harmony1_novar_DB';
$pass = 'Fatu8aMt2NFmtEeR8QRa';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die('Connection failed:' . $e->getMessage());
}
?>
