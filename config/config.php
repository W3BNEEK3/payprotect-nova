<?php
$host = 'localhost';
$db   = 'harmony1_novar_DB'; // your database name
$user = 'harmony1_novar_DB';
$pass = 'Fatu8aMt2NFmtEeR8QRa'; // or your MySQL password if you set one
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
