<?php
session_start();
require 'db_connection.php';

// Consulta para obtener todas las solicitudes pendientes (estado_pv = 'Pendiente')
$query = "SELECT idPreventa, nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalpreventa, metodoPago, tipoComida, estado_pv, hora_compra 
          FROM preventa 
          WHERE estado_pv = 'Pendiente' 
          ORDER BY hora_compra DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($solicitudes);
?>