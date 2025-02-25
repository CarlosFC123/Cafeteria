<?php
session_start();
require 'db_connection.php';

// Verifica si los datos fueron enviados por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProveedor = $_POST['idProveedor'];
    $idProducto = $_POST['idProducto'];
    $precio_unitario_compra = $_POST['precio_unitario_compra'];
    $cantidadProducto = $_POST['cantidadProducto'];
    $precioTotalCompra = $precio_unitario_compra * $cantidadProducto;

    // Actualizar los datos en la base de datos
    $stmt = $pdo->prepare("
        UPDATE productos_proveedores
        SET precio_unitario_compra = :precio_unitario_compra, cantidadProducto = :cantidadProducto, precioTotalCompra = :precioTotalCompra
        WHERE idProveedor = :idProveedor AND idProducto = :idProducto
    ");
    $stmt->execute([
        'precio_unitario_compra' => $precio_unitario_compra,
        'cantidadProducto' => $cantidadProducto,
        'precioTotalCompra' => $precioTotalCompra,
        'idProveedor' => $idProveedor,
        'idProducto' => $idProducto
    ]);

    // Redirigir a la página de administración después de guardar los cambios
    header('Location: productos_proveedores.php');
    exit;
}

include 'sidebar.php'; // Incluye el sidebar después de realizar todas las lógicas del PHP

// Obtener los datos del producto proveedor a editar
if (isset($_GET['idProveedor']) && isset($_GET['idProducto'])) {
    $idProveedor = $_GET['idProveedor'];
    $idProducto = $_GET['idProducto'];

    $stmt = $pdo->prepare("
        SELECT pp.idProveedor, pp.idProducto, pp.precio_unitario_compra, pp.cantidadProducto, (pp.precio_unitario_compra * pp.cantidadProducto) AS precioTotalCompra
        FROM productos_proveedores pp
        JOIN proveedores p ON pp.idProveedor = p.idProveedor
        JOIN productos pr ON pp.idProducto = pr.idProducto
        WHERE pp.idProveedor = :idProveedor AND pp.idProducto = :idProducto
    ");
    $stmt->execute(['idProveedor' => $idProveedor, 'idProducto' => $idProducto]);
    $producto_proveedor = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- HTML para la vista de edición -->
<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Editar Producto Proveedor</h5>
            </div>
            <div class="card-body">
                <?php if ($producto_proveedor): ?>
                <form method="POST">
                    <input type="hidden" name="idProveedor" value="<?php echo htmlspecialchars($producto_proveedor['idProveedor']); ?>">
                    <input type="hidden" name="idProducto" value="<?php echo htmlspecialchars($producto_proveedor['idProducto']); ?>">

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="precio_unitario_compra" class="form-label">Precio Unitario Compra</label>
                            <input type="text" class="form-control" id="precio_unitario_compra" name="precio_unitario_compra" value="<?php echo htmlspecialchars($producto_proveedor['precio_unitario_compra']); ?>" required>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="cantidadProducto" class="form-label">Cantidad Producto</label>
                            <input type="text" class="form-control" id="cantidadProducto" name="cantidadProducto" value="<?php echo htmlspecialchars($producto_proveedor['cantidadProducto']); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="precioTotalCompra" class="form-label">Precio Total Compra</label>
                            <input type="text" class="form-control" id="precioTotalCompra" name="precioTotalCompra" value="<?php echo htmlspecialchars($producto_proveedor['precioTotalCompra']); ?>" readonly>
                        </div>

                        <div class="col-md-6 mb-4 d-flex align-items-end">
                            <button type="submit" name="update" class="btn btn-primary me-3">Guardar Cambios</button>
                            <a href="productos_proveedores.php" class="btn btn-danger">Cancelar</a>
                        </div>
                    </div>

                </form>
                <?php else: ?>
                <p>Producto proveedor no encontrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
