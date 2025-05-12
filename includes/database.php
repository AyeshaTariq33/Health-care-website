<?php
// Simplified database connection
$host = 'localhost';
$db   = 'healthcare-db';
$user = 'root';      // Your database username
$pass = '';          // Your database password
$charset = 'utf8mb4';

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Basic error handling
    die("Database connection failed: " . $e->getMessage());
}