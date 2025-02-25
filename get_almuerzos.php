<?php
require 'db_connection.php';

// Obtener la fecha actual en formato YYYY-MM-DD
$fecha_actual = date('Y-m-d');

// Consulta para obtener los almuerzos que incluyen la fecha actual
$query = "SELECT * FROM almuerzo WHERE FIND_IN_SET(:fecha_actual, fecha) > 0 ORDER BY idAlmuerzo ASC";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':fecha_actual', $fecha_actual);
$stmt->execute();

$almuerzos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($almuerzos);
?>