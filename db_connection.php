<?php
$host = getenv('DB_HOST') ?: "localhost";
$dbname = getenv('DB_NAME') ?: "cafeteria";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASSWORD') ?: "";
//mysql://root:QqsqcnIwVSSruFsbxwJUnCBNXRZCIeEq@crossover.proxy.rlwy.net:31168/railway
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>