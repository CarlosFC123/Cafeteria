<?php
session_start();
include 'sidebar.php';
require 'db_connection.php';

// Consultar los datos de la tabla productos_proveedores junto con los nombres de proveedores y productos
$productos_proveedores = $pdo->query("
    SELECT pp.idProveedor, p.nbProveedor, pp.idProducto, pr.nbProducto, pp.precio_unitario_compra, pp.cantidadProducto,
           (pp.precio_unitario_compra * pp.cantidadProducto) AS precioTotalCompra
    FROM productos_proveedores pp
    JOIN proveedores p ON pp.idProveedor = p.idProveedor
    JOIN productos pr ON pp.idProducto = pr.idProducto
")->fetchAll(PDO::FETCH_ASSOC);

// Actualizar la base de datos con el precio total de la compra
foreach ($productos_proveedores as $item) {
    $precioTotalCompra = $item['precio_unitario_compra'] * $item['cantidadProducto'];
    $stmt = $pdo->prepare("
        UPDATE productos_proveedores
        SET precioTotalCompra = :precioTotalCompra
        WHERE idProveedor = :idProveedor AND idProducto = :idProducto
    ");
    $stmt->execute([
        'precioTotalCompra' => $precioTotalCompra,
        'idProveedor' => $item['idProveedor'],
        'idProducto' => $item['idProducto']
    ]);
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h5 class="mb-0">Administración de Productos Proveedores</h5>
                    </div>
                   <!-- <a href="agregar_producto_proveedor.php" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Agregar Producto/Proveedor
                    </a> -->
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Proveedor</th>
                                <th>Producto</th>
                                <th>Precio Unitario Compra</th>
                                <th>Cantidad Producto</th>
                                <th>Precio Total Compra</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($productos_proveedores as $item): ?>
                            <tr id="producto-proveedor-<?php echo $item['idProveedor']; ?>">
                                <td><?php echo htmlspecialchars($item['nbProveedor']); ?></td>
                                <td><?php echo htmlspecialchars($item['nbProducto']); ?></td>
                                <td><?php echo htmlspecialchars($item['precio_unitario_compra']); ?></td>
                                <td><?php echo htmlspecialchars($item['cantidadProducto']); ?></td>
                                <td><?php echo htmlspecialchars($item['precioTotalCompra']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_productos_proveedores.php?idProveedor=<?php echo $item['idProveedor']; ?>&idProducto=<?php echo $item['idProducto']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class='bx bx-edit-alt'></i> Editar
                                        </a>
                                        <a href="eliminar_productos_proveedores.php?idProveedor=<?php echo $item['idProveedor']; ?>&idProducto=<?php echo $item['idProducto']; ?>" class="btn btn-sm btn-outline-danger">
                                            <i class='bx bx-trash'></i> Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>