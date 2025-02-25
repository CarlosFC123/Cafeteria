<?php
header('Content-Type: application/json');

// Conectar a la base de datos
require 'db_connection.php';

// Obtener la fecha actual
$fechaActual = date('Y-m-d');

// Consulta para obtener los desayunos de la fecha actual
$query = "
    SELECT 
        d.idDesayuno,
        d.imgDesayuno,
        d.nombreProducto,
        d.precioDesayuno,
        d.cantidadDesayuno,
        d.precioTotalDesayuno,
        d.descripcionDesayuno,
        d.fecha
    FROM desayuno d
    WHERE d.fecha LIKE '%$fechaActual%'
    ORDER BY d.idDesayuno ASC
";

$resultado = $pdo->query($query);
$desayunos = $resultado->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($desayunos); // Devolver los datos en formato JSON
?>