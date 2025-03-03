<?php
session_start();
require 'db_connection.php';

// Verificar si se ha enviado el ID de la preventa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPreventa'])) {
    $idPreventa = $_POST['idPreventa'];

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Actualizar el estado de la preventa a "Cancelado"
        $stmt = $pdo->prepare("UPDATE preventa SET estado_pv = 'Cancelado' WHERE idPreventa = ?");
        $stmt->execute([$idPreventa]);

        // Confirmar la transacción
        $pdo->commit();

        // Respuesta de éxito
        echo json_encode(['success' => true, 'message' => 'Solicitud cancelada correctamente.']);
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al cancelar la solicitud: ' . $e->getMessage()]);
    }
} else {
    // Respuesta en caso de solicitud no válida
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
}
?>