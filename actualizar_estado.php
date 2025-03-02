<?php
session_start();
require 'db_connection.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si se han enviado los datos necesarios
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPreventa']) && isset($_POST['estado_pv'])) {
    $idPreventa = $_POST['idPreventa'];
    $estado_pv = $_POST['estado_pv'];

    // Validar que el estado sea uno de los permitidos
    $estadosPermitidos = ['Pendiente', 'Completado', 'Cancelado'];
    if (!in_array($estado_pv, $estadosPermitidos)) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Estado no válido']);
        exit;
    }

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Obtener los datos de la preventa
        $stmt = $pdo->prepare("SELECT nombrePreventa, precioTotalpreventa, metodoPago, turnoVentaP, estado_pv FROM preventa WHERE idPreventa = ?");
        $stmt->execute([$idPreventa]);
        $preventa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$preventa) {
            throw new Exception("Preventa no encontrada.");
        }

        // Obtener el estado actual de la preventa
        $estado_actual = $preventa['estado_pv'];

        // Actualizar el estado de la preventa
        $stmt = $pdo->prepare("UPDATE preventa SET estado_pv = ? WHERE idPreventa = ?");
        $stmt->execute([$estado_pv, $idPreventa]);

        // Si el estado cambia a "Completado", insertar los datos en la tabla pagos
        if ($estado_pv === 'Completado') {
            $nombrePagos = $preventa['nombrePreventa'];
            $canTotalP = $preventa['precioTotalpreventa'];
            $metodoPago = $preventa['metodoPago']; // Mantener el método de pago como "Efectivo"
            $turnoVentaP = $preventa['turnoVentaP'];
            $fePago = date('Y-m-d H:i:s'); // Fecha y hora actual

            // Insertar en la tabla pagos
            $stmt = $pdo->prepare("
                INSERT INTO pagos (
                    nombrePagos, 
                    canTotalP, 
                    fePago, 
                    metodoPago, 
                    turnoVentaP
                ) VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $nombrePagos,
                $canTotalP,
                $fePago,
                $metodoPago,
                $turnoVentaP
            ]);
        } 
        // Si el estado cambia de "Completado" a "Pendiente" o "Cancelado", eliminar el registro de pagos
        elseif ($estado_actual === 'Completado' && ($estado_pv === 'Pendiente' || $estado_pv === 'Cancelado')) {
            // Eliminar el registro correspondiente de la tabla pagos
            $stmt = $pdo->prepare("DELETE FROM pagos WHERE nombrePagos = ? AND canTotalP = ? AND metodoPago = ? AND turnoVentaP = ?");
            $stmt->execute([
                $preventa['nombrePreventa'],
                $preventa['precioTotalpreventa'],
                $preventa['metodoPago'],
                $preventa['turnoVentaP']
            ]);
        }

        // Confirmar la transacción
        $pdo->commit();

        // Redirigir de vuelta a la página de preventas
        header("Location: preventa.php");
        exit;
    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
}
?>