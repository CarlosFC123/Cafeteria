<?php
require 'db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idTipo = intval($_POST['id']);

    try {
        // Preparar y ejecutar la consulta para eliminar el tipo de categoría
        $stmt = $pdo->prepare("DELETE FROM tipos_categorias WHERE idTipo = :idTipo");
        $stmt->bindParam(':idTipo', $idTipo, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Responder con éxito si la eliminación fue exitosa
            echo json_encode(['success' => true]);
        } else {
            // Responder con error si ocurrió un problema
            echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el tipo.']);
        }
    } catch (Exception $e) {
        // Manejar errores de la base de datos
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    // Responder con error si la solicitud no es válida
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
}
?>