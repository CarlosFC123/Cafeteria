<?php
require 'db_connection.php';

// Verificar si se pasó el ID de producto
if (isset($_POST['id'])) {
    $idProducto = $_POST['id'];

    // Eliminar el producto de la base de datos
    $delete = $pdo->prepare("DELETE FROM productos WHERE idProducto = :idProducto");
    $delete->bindParam(':idProducto', $idProducto, PDO::PARAM_INT);
    $delete->execute();

    // Responder con un estado exitoso
    echo "Producto eliminado exitosamente.";
} else {
    echo "No se proporcionó un ID de producto.";
}
?>
