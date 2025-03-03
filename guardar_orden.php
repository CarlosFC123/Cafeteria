<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$ordenes = json_decode($_POST['ordenes'], true);
$metodoPago = $_POST['metodoPago'];

if (empty($ordenes)) {
    echo json_encode(['success' => false, 'message' => 'No hay productos en la orden']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Insertar la orden principal
    $stmt = $pdo->prepare("INSERT INTO ordenes (usuario_id, metodo_pago, fecha) VALUES (:usuario_id, :metodo_pago, NOW())");
    $stmt->execute([
        ':usuario_id' => $_SESSION['usuario_id'],
        ':metodo_pago' => $metodoPago
    ]);
    $ordenId = $pdo->lastInsertId();

    // Insertar los detalles de la orden
    foreach ($ordenes as $orden) {
        $stmt = $pdo->prepare("INSERT INTO detalles_orden (orden_id, nombre_producto, cantidad, precio_unitario, precio_total) VALUES (:orden_id, :nombre_producto, :cantidad, :precio_unitario, :precio_total)");
        $stmt->execute([
            ':orden_id' => $ordenId,
            ':nombre_producto' => $orden['nombre'],
            ':cantidad' => $orden['cantidad'],
            ':precio_unitario' => $orden['precioUnitario'],
            ':precio_total' => $orden['precioTotal']
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => 'Orden guardada correctamente']);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al guardar la orden: ' . $e->getMessage()]);
}