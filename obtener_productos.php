<?php
session_start();
require 'db_connection.php';

// Consulta para obtener todos los productos
$query = "
    SELECT 
        p.idProducto,
        c.nbCategoria,
        pr.nbProveedor,
        p.nbProducto,
        p.desProducto,
        p.precioProducto,
        p.imgProducto,
        t.nbTipo
    FROM productos p
    INNER JOIN categorias c ON p.idCategoria = c.idCategoria
    INNER JOIN proveedores pr ON p.idProveedor = pr.idProveedor
    INNER JOIN tipos_categorias t ON p.idTipo = t.idTipo
    ORDER BY p.idProducto ASC
";

$resultado = $pdo->query($query);
$productos = $resultado->fetchAll(PDO::FETCH_ASSOC);

// Devolver los productos en formato JSON
header('Content-Type: application/json');
echo json_encode($productos);
?>