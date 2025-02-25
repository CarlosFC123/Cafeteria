<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }
include 'sidebar.php';
require 'db_connection.php';

// Obtener los datos de la tabla preventa
$preventas = $pdo->query("
    SELECT 
        idPreventa, 
        nombrePreventa, 
        cantidad_orden,       
        precioUnitarioPreventa, 
        (cantidad_orden * precioUnitarioPreventa) AS precioTotalpreventa,
        metodoPago,
        turnoVentaP
    FROM preventa
    ORDER BY idPreventa ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Manejar eliminación de preventas
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idPreventa'])) {
    $idPreventa = $_POST['idPreventa'];
    $stmt = $pdo->prepare("DELETE FROM preventa WHERE idPreventa = ?");
    $stmt->execute([$idPreventa]);
    echo "<script>window.location.href='preventa.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Preventas</title>
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
                <h5 class="mb-0">Administración de Preventas</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Preventa</th>
                            <th>Nombre Preventa</th>
                            <th>Cantidad Orden</th>
                            <th>Precio Unitario</th>
                            <th>Precio Total</th>
                            <th>Metodo de pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($preventas as $preventa): ?>
                        <tr id="preventa-<?php echo $preventa['idPreventa']; ?>">
                            <td><?php echo htmlspecialchars($preventa['idPreventa']); ?></td>
                            <td><?php echo htmlspecialchars($preventa['nombrePreventa']); ?></td>                            
                            <td><?php echo htmlspecialchars($preventa['cantidad_orden']); ?></td>
                            <td>$<?php echo number_format($preventa['precioUnitarioPreventa'], 2); ?></td>
                            <td>$<?php echo number_format($preventa['precioTotalpreventa'], 2); ?></td>
                            <td><?php echo htmlspecialchars($preventa['metodoPago']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $preventa['idPreventa']; ?>)">
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
    function confirmarEliminar(idPreventa) {
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
                // Enviar formulario para eliminar la preventa
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idPreventa';
                input.value = idPreventa;

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