<?php
require 'db_connection.php';

$fecha = $_GET['fecha']; // Obtener la fecha desde la solicitud AJAX

// Consulta para obtener los desayunos del día actual
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
    WHERE d.fecha LIKE '%$fecha%'
    ORDER BY d.idDesayuno ASC
";

$resultado = $pdo->query($query);
$desayunos = $resultado->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($desayunos); // Devolver los datos en formato JSON
?>