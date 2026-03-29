<?php
session_start();

$host = '127.0.0.1';           // Your database host (127.0.0.1 for most shared hosting)
$db   = 'your_database_name';  // Your database name
$user = 'your_database_user';  // Your database username
$pass = 'your_database_pass';  // Your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // DO NOT output raw errors in production, but helpful for local testing
    die("Database connection failed: " . $e->getMessage());
}

// Helper to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}
?>
