<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT idPreventa, nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalpreventa, metodoPago, tipoComida, estado_pv, hora_compra 
            FROM preventa 
            WHERE estado_pv = 'Pendiente' 
            ORDER BY hora_compra DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($solicitudes);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
