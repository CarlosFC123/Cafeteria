<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }
include 'sidebar.php';
require 'db_connection.php';

$pagos = $pdo->query(" 
    SELECT 
        p.idPagos,
        p.nombrePagos,
        p.canTotalP,
        p.fePago,
        p.metodoPago
    FROM pagos p
    
    ORDER BY p.idPagos ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPagos'])) {
    $idPagos = $_POST['idPagos'];
    $stmt = $pdo->prepare("DELETE FROM pagos WHERE idPagos = ?");
    $stmt->execute([$idPagos]);
    echo "<script>window.location.href='pagos.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Pagos</title>
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
                <h5 class="mb-0">Administración de Pagos-ventas</h5>
                <!-- <a href="agregar_pago.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Agregar Pago
                </a> -->
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Pago</th>
                            <th>Nombre</th>
                            <th>Ganancia</th>
                            <th>Fecha de Pago</th>
                            <th>Metodo de Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pagos as $pago): ?>
                        <tr id="pago-<?php echo $pago['idPagos']; ?>">
                            <td><?php echo htmlspecialchars($pago['idPagos']); ?></td>
                            <td><?php echo htmlspecialchars($pago['nombrePagos']); ?></td>
                            <td><?php echo htmlspecialchars($pago['canTotalP']); ?></td>
                            <td><?php echo htmlspecialchars($pago['fePago']); ?></td>
                            <td><?php echo htmlspecialchars($pago['metodoPago']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $pago['idPagos']; ?>)">
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
    function editarPago(idPagos) {
        window.location.href = 'editar_pago.php?id=' + idPagos;
    }

    function confirmarEliminar(idPagos) {
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
                // Enviar formulario para eliminar el pago
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idPagos';
                input.value = idPagos;

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
