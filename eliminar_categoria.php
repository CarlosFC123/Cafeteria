<?php
require 'db_connection.php';

// Verificar si se pasó el ID de categoría
if (isset($_POST['id'])) {
    $idCategoria = $_POST['id'];

    // Eliminar la categoría de la base de datos
    $delete = $pdo->prepare("DELETE FROM categorias WHERE idCategoria = :idCategoria");
    $delete->bindParam(':idCategoria', $idCategoria, PDO::PARAM_INT);
    $delete->execute();

    // Responder con un estado exitoso
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se proporcionó un ID de categoría.']);
}
?>