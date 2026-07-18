<?php
require_once __DIR__ . '/../bootstrap/env.php';
loadEnv(__DIR__ . '/../.env');

$host    = env('DB_HOST');
$db      = env('DB_NAME');
$user    = env('DB_USER');
$pass    = env('DB_PASS');
$charset = env('DB_CHARSET', 'utf8mb4');

$dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Connection failed: ' . $e->getMessage());
}
?>
