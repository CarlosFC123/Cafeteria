<?php
session_start();
require 'db_connection.php';

// Verificar si se ha enviado el ID de la preventa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPreventa'])) {
    $idPreventa = $_POST['idPreventa'];

    try {
        // Iniciar transacción
        $pdo->beginTransaction();

        // Obtener los datos de la preventa
        $stmt = $pdo->prepare("SELECT nombrePreventa, cantidad_orden, tipoComida, estado_pv FROM preventa WHERE idPreventa = ?");
        $stmt->execute([$idPreventa]);
        $preventa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$preventa) {
            throw new Exception("Preventa no encontrada.");
        }

        $nombrePreventa = $preventa['nombrePreventa'];
        $cantidad_orden = $preventa['cantidad_orden'];
        $tipoComida = $preventa['tipoComida'];
        $estado_actual = $preventa['estado_pv'];

        // Solo sumar la cantidad al stock si el estado actual no es "Cancelado"
        if ($estado_actual !== 'Cancelado') {
            // Determinar el tipo de preventa y actualizar la cantidad correspondiente
            if ($tipoComida === 'desayuno') {
                // Es un desayuno: sumar cantidad_orden a cantidadDesayuno en la tabla desayuno
                $stmt = $pdo->prepare("UPDATE desayuno SET cantidadDesayuno = cantidadDesayuno + ? WHERE nombreProducto = ?");
                $stmt->execute([$cantidad_orden, $nombrePreventa]);
            } elseif (strpos($tipoComida, 'almuerzo') !== false) {
                // Es un almuerzo: determinar si es porción, media u orden
                $columnaActualizar = '';
                if ($tipoComida === 'almuerzo-porcion') {
                    $columnaActualizar = 'cantidadPorcion';
                } elseif ($tipoComida === 'almuerzo-media') {
                    $columnaActualizar = 'cantidadMedia';
                } elseif ($tipoComida === 'almuerzo-orden') {
                    $columnaActualizar = 'cantidadOrden';
                }

                if ($columnaActualizar) {
                    // Sumar cantidad_orden a la columna correspondiente en la tabla almuerzo
                    $stmt = $pdo->prepare("UPDATE almuerzo SET $columnaActualizar = $columnaActualizar + ? WHERE nombreProducto = ?");
                    $stmt->execute([$cantidad_orden, $nombrePreventa]);
                }
            } elseif ($tipoComida === 'producto') {
                // Es un producto: sumar cantidad_orden a canActual en la tabla inventario
                // Primero, obtener el idProducto basado en el nombre del producto
                $stmt = $pdo->prepare("SELECT idProducto FROM productos WHERE nbProducto = ?");
                $stmt->execute([$nombrePreventa]);
                $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($producto) {
                    $idProducto = $producto['idProducto'];
                    $stmt = $pdo->prepare("UPDATE inventario SET canActual = canActual + ? WHERE idProducto = ?");
                    $stmt->execute([$cantidad_orden, $idProducto]);
                } else {
                    throw new Exception("Producto no encontrado en la tabla productos.");
                }
            }
        }

        // Actualizar el estado de la preventa a "Cancelado"
        $stmt = $pdo->prepare("UPDATE preventa SET estado_pv = 'Cancelado' WHERE idPreventa = ?");
        $stmt->execute([$idPreventa]);

        // Confirmar la transacción
        $pdo->commit();

        // Respuesta de éxito
        echo json_encode(['success' => true, 'message' => 'Solicitud cancelada correctamente y stock actualizado.']);
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