<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 1) { // Asumiendo rol 1 para administrador de ventas
//     header("Location: login.php");
//     exit;
// }
include 'sidebar.php';
require 'db_connection.php';

$ventas = $pdo->query(" 
    SELECT 
        v.idVenta,
        v.idUsuario,
        v.fechaVenta,
        v.turnoVenta,
        v.metodoPago,
        v.estadoVenta,
        v.totalVenta,
        p.nbProducto  -- Obtener el nombre del producto
    FROM ventas v
    LEFT JOIN productos p ON v.idProducto = p.idProducto  -- Hacer JOIN con la tabla productos
    ORDER BY v.idVenta ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idVenta'])) {
    $idVenta = $_POST['idVenta'];
    $stmt = $pdo->prepare("DELETE FROM ventas WHERE idVenta = ?");
    $stmt->execute([$idVenta]);
    echo "<script>window.location.href='ventas.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Ventas</title>
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
                <h5 class="mb-0">Administración de Ventas</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Venta</th>
                            <th>ID Usuario</th>
                            <th>Producto</th>
                            <th>Fecha de Venta</th>
                            <th>Turno de Venta</th>
                            <th>Metodo de Pago</th>
                            <th>Estado de Venta</th>
                            <th>Total de Venta</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ventas as $venta): ?>
                        <tr id="venta-<?php echo $venta['idVenta']; ?>">
                            <td><?php echo htmlspecialchars($venta['idVenta']); ?></td>
                            <td><?php echo htmlspecialchars($venta['idUsuario']); ?></td>
                            <td><?php echo htmlspecialchars($venta['nbProducto']); ?></td> 
                            <td><?php echo htmlspecialchars($venta['fechaVenta']); ?></td>
                            <td><?php echo htmlspecialchars($venta['turnoVenta']); ?></td>
                            <td><?php echo htmlspecialchars($venta['metodoPago']); ?></td>
                            <td><?php echo htmlspecialchars($venta['estadoVenta']); ?></td>
                            <td><?php echo htmlspecialchars($venta['totalVenta']); ?></td>
                            
                            <td>
                                <div class="action-buttons">
                                    <!-- <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editarVenta(<?php echo $venta['idVenta']; ?>)">
                                        <i class='bx bx-edit-alt'></i> Editar
                                    </button> -->
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $venta['idVenta']; ?>)">
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
    function editarVenta(idVenta) {
        window.location.href = 'editar_venta.php?id=' + idVenta;
    }

    function confirmarEliminar(idVenta) {
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
                // Enviar formulario para eliminar la venta
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idVenta';
                input.value = idVenta;

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
