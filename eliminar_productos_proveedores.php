<?php
session_start();
require 'db_connection.php';

// Verificar si el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['usuario_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SESSION['usuario_rol'] != 2) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
    exit;
}

// Verificar si se han proporcionado los parámetros necesarios
if (!isset($_GET['idProveedor']) || !isset($_GET['idProducto'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Parámetros inválidos']);
    exit;
}

// Validar que los parámetros sean números enteros
$idProveedor = filter_var($_GET['idProveedor'], FILTER_VALIDATE_INT);
$idProducto = filter_var($_GET['idProducto'], FILTER_VALIDATE_INT);

if ($idProveedor === false || $idProducto === false) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Los parámetros deben ser números enteros']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Verificar si hay registros relacionados en otras tablas
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM otra_tabla WHERE idProveedor = ? AND idProducto = ?");
    $stmt->execute([$idProveedor, $idProducto]);
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se puede eliminar el registro porque tiene dependencias']);
        exit;
    }

    // Eliminar el registro de productos_proveedores
    $stmt = $pdo->prepare("DELETE FROM productos_proveedores WHERE idProveedor = ? AND idProducto = ?");
    $stmt->execute([$idProveedor, $idProducto]);

    // Verificar si se eliminó correctamente
    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Registro eliminado correctamente']);
    } else {
        $pdo->rollBack();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'No se encontró el registro']);
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el registro: ' . $e->getMessage()]);
}
?>