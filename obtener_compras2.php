<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria";  

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT nombrePagos, canTotalP, fePago, metodoPago FROM pagos ORDER BY fePago DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $compras = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($compras);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
