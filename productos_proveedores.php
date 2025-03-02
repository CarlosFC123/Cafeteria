<?php
session_start();
require 'db_connection.php';

// Consultar los datos de la tabla productos_proveedores junto con los nombres de proveedores, productos y canActual
$productos_proveedores = $pdo->query("
    SELECT pp.idProveedor, p.nbProveedor, pp.idProducto, pr.nbProducto, pp.precio_unitario_compra, 
           pp.cantidadProducto, pp.cantidad_anterior,
           (pp.precio_unitario_compra * pp.cantidadProducto) AS precioTotalCompra, i.canActual
    FROM productos_proveedores pp
    JOIN proveedores p ON pp.idProveedor = p.idProveedor
    JOIN productos pr ON pp.idProducto = pr.idProducto
    JOIN inventario i ON pp.idProducto = i.idProducto
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

// Manejar la edición de la cantidad de proveedor bajó
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idProveedor']) && isset($_POST['idProducto'])) {
    $idProveedor = $_POST['idProveedor'];
    $idProducto = $_POST['idProducto'];
    $cantidadProducto = $_POST['cantidadProducto'];

    // Validar que la cantidad a bajar sea un número positivo
    if ($cantidadProducto < 0) {
        echo "<script>alert('La cantidad a bajar debe ser un número positivo.'); window.location.href='productos_proveedores.php';</script>";
        exit;
    }

    // Obtener la cantidad actual antes de actualizar
    $stmt = $pdo->prepare("SELECT cantidadProducto FROM productos_proveedores WHERE idProveedor = :idProveedor AND idProducto = :idProducto");
    $stmt->execute(['idProveedor' => $idProveedor, 'idProducto' => $idProducto]);
    $cantidadAnterior = $stmt->fetchColumn();

    // Actualizar la cantidad de proveedor bajó y guardar el valor anterior
    $stmt = $pdo->prepare("
        UPDATE productos_proveedores
        SET cantidadProducto = :cantidadProducto, cantidad_anterior = :cantidad_anterior
        WHERE idProveedor = :idProveedor AND idProducto = :idProducto
    ");
    $stmt->execute([
        'cantidadProducto' => $cantidadProducto,
        'cantidad_anterior' => $cantidadAnterior,
        'idProveedor' => $idProveedor,
        'idProducto' => $idProducto
    ]);

    // Sumar la cantidad a bajar al canActual en la tabla inventario
    $stmt = $pdo->prepare("
        UPDATE inventario
        SET canActual = canActual + :cantidadProducto
        WHERE idProducto = :idProducto
    ");
    $stmt->execute([
        'cantidadProducto' => $cantidadProducto,
        'idProducto' => $idProducto
    ]);

    // Redirigir para evitar reenvío del formulario
    header("Location: productos_proveedores.php");
    exit;
}

include 'sidebar.php';
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
                            <th>Cantidad bajada anteriormente</th>
                            <th>Cantidad a bajar</th>
                            <th>Precio Total Compra</th>
                            <th>Cantidad Actual (Inventario)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos_proveedores as $item): ?>
                        <tr id="producto-proveedor-<?php echo $item['idProveedor']; ?>">
                            <td><?php echo htmlspecialchars($item['nbProveedor']); ?></td>
                            <td><?php echo htmlspecialchars($item['nbProducto']); ?></td>
                            <td><?php echo htmlspecialchars($item['precio_unitario_compra']); ?></td>
                            <td><?php echo htmlspecialchars($item['cantidad_anterior'] ?? 'N/A'); ?></td> <!-- Mostrar cantidad anterior -->
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="idProveedor" value="<?php echo $item['idProveedor']; ?>">
                                    <input type="hidden" name="idProducto" value="<?php echo $item['idProducto']; ?>">
                                    <input type="number" name="cantidadProducto" value="<?php echo htmlspecialchars($item['cantidadProducto']); ?>" min="0" style="width: 80px;">
                                    <button type="submit" class="btn btn-sm btn-outline-primary">
                                        <i class='bx bx-save'></i> Guardar
                                    </button>
                                </form>
                            </td>
                            <td><?php echo htmlspecialchars($item['precioTotalCompra']); ?></td>
                            <td><?php echo htmlspecialchars($item['canActual']); ?></td>
                            <td>
                                <div class="action-buttons">
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