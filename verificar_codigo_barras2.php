<?php
require 'db_connection.php';

if (isset($_GET['codigo_barras'])) {
    $codigoBarras = $_GET['codigo_barras'];

    // Verificar si el código de barras ya existe (excluyendo el producto actual)
    $idProducto = isset($_GET['idProducto']) ? $_GET['idProducto'] : 0;
    $stmt = $pdo->prepare("SELECT idProducto FROM productos WHERE codigo_barras = ? AND idProducto != ?");
    $stmt->execute([$codigoBarras, $idProducto]);
    $productoExistente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($productoExistente) {
        echo "existe"; // El código de barras ya existe
    } else {
        echo "no_existe"; // El código de barras no existe
    }
}
?>