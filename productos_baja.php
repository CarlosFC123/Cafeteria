<?php
// session_start();
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }
include 'sidebar.php';
require 'db_connection.php';

// Obtener productos dados de baja
$productos_baja = $pdo->query("
    SELECT 
        pb.idProductoBaja,
        pb.idProducto,
        p.nbProducto AS nbProducto,
        pb.feBaja,
        pb.desProducto,
        pb.cantidad_baja,
        pb.cantidadProducto, 
        pb.TotalCantidadP  
    FROM productos_baja pb
    JOIN productos p ON pb.idProducto = p.idProducto
    ORDER BY pb.idProductoBaja ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Manejar la solicitud de eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idProductoBaja'])) {
    $idProductoBaja = $_POST['idProductoBaja'];

    // Obtener la cantidad_baja y el idProducto del producto dado de baja
    $stmt = $pdo->prepare("SELECT idProducto, cantidad_baja FROM productos_baja WHERE idProductoBaja = ?");
    $stmt->execute([$idProductoBaja]);
    $producto_baja = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto_baja) {
        $idProducto = $producto_baja['idProducto'];
        $cantidad_baja = $producto_baja['cantidad_baja'];

        // Sumar la cantidad_baja de nuevo a canActual en la tabla inventario
        $stmt = $pdo->prepare("UPDATE inventario SET canActual = canActual + ? WHERE idProducto = ?");
        $stmt->execute([$cantidad_baja, $idProducto]);

        // Eliminar el registro de productos_baja
        $stmt = $pdo->prepare("DELETE FROM productos_baja WHERE idProductoBaja = ?");
        $stmt->execute([$idProductoBaja]);

        echo "<script>window.location.href='productos_baja.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Productos Dados de Baja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
            cursor: pointer;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Administración de Productos Dados de Baja</h5>
                <a href="agregar_productos_baja.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Agregar Producto
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Producto Baja</th>
                            <th>Nombre Producto</th>
                            <th>Fecha Baja</th>
                            <th>Descripción Producto</th>
                            <th>Cantidad Producto antes</th>
                            <th>Cantidad Baja</th>
                            <th>Total Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos_baja as $producto): ?>
                        <tr id="producto-baja-<?php echo $producto['idProductoBaja']; ?>">
                            <td><?php echo htmlspecialchars($producto['idProductoBaja']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nbProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['feBaja']); ?></td>
                            <td><?php echo htmlspecialchars($producto['desProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['cantidadProducto']); ?></td>
                            <td><?php echo htmlspecialchars($producto['cantidad_baja']); ?></td>
                            <td><?php echo htmlspecialchars($producto['TotalCantidadP']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editarProductoBaja(<?php echo $producto['idProductoBaja']; ?>)">
                                        <i class='bx bx-edit-alt'></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $producto['idProductoBaja']; ?>)">
                                        <i class='bx bx-trash'></i> Eliminar
                                    </button>
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

<script>
    function editarProductoBaja(idProductoBaja) {
        window.location.href = 'editar_productos_baja.php?id=' + idProductoBaja;
    }

    function confirmarEliminar(idProductoBaja) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "No podrás revertir esta acción",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Enviar formulario para eliminar el producto
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idProductoBaja';
                input.value = idProductoBaja;

                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>