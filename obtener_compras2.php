<?php
session_start();
require 'db_connection.php';

// Consulta para obtener todos los datos de la tabla pagos
$query = "SELECT nombrePagos, canTotalP, fePago, metodoPago, 'Completado' FROM pagos ORDER BY fePago DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($compras);
?>