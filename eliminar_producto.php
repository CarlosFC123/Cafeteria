<?php
//eliminar_producto.php
session_start();
require 'db_connection.php';

// Verificar si el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar si la solicitud es POST y tiene el ID del producto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $idProducto = $_POST['id'];

    try {
        // Obtener el nombre del producto
        $stmt = $pdo->prepare("SELECT nbProducto FROM productos WHERE idProducto = :idProducto");
        $stmt->execute(['idProducto' => $idProducto]);
        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$producto) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
            exit;
        }

        $nbProducto = $producto['nbProducto'];

        // Tablas relacionadas y sus columnas
        $tablasRelacionadas = [
            'inventario' => 'idProducto',
            'productos_baja' => 'idProducto',
            'productos_proveedores' => 'idProducto',
            'ordenes' => 'nombreOrden',
            'pagos' => 'nombrePagos',
            'preventa' => 'nombrePreventa',
        ];

        // Verificar si el producto está en uso
        $enUso = [];
        foreach ($tablasRelacionadas as $tabla => $columna) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM $tabla WHERE $columna = :idProducto OR $columna = :nbProducto");
            $stmt->execute(['idProducto' => $idProducto, 'nbProducto' => $nbProducto]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $enUso[] = $tabla;
            }
        }

        // Si el producto está en uso, devolver un mensaje de advertencia
        if (!empty($enUso)) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'El producto está en uso en las siguientes tablas: ' . implode(', ', $enUso) 
                             
            ]);
            exit;
        }

        // Iniciar la transacción
        $pdo->beginTransaction();

        // Eliminar de las tablas relacionadas
        foreach ($tablasRelacionadas as $tabla => $columna) {
            $stmt = $pdo->prepare("DELETE FROM $tabla WHERE $columna = :idProducto OR $columna = :nbProducto");
            $stmt->execute(['idProducto' => $idProducto, 'nbProducto' => $nbProducto]);
        }

        // Eliminar el producto de la tabla productos
        $stmt = $pdo->prepare("DELETE FROM productos WHERE idProducto = :idProducto");
        $stmt->execute(['idProducto' => $idProducto]);

        // Confirmar la transacción
        $pdo->commit();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
    } catch (PDOException $e) {
        // Revertir la transacción solo si está activa
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto: ' . $e->getMessage()]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Solicitud no válida']);
}
?>