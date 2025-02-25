<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }
include 'sidebar.php';
require 'db_connection.php';

$desayunos = $pdo->query(" 
    SELECT 
        d.idDesayuno,
        d.imgDesayuno,
        d.nombreProducto,
        d.precioDesayuno,
        d.cantidadDesayuno,
        d.precioTotalDesayuno,
        d.descripcionDesayuno 
    FROM desayuno d
    ORDER BY d.idDesayuno ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idDesayuno'])) {
    $idDesayuno = $_POST['idDesayuno'];
    $stmt = $pdo->prepare("DELETE FROM desayuno WHERE idDesayuno = ?");
    $stmt->execute([$idDesayuno]);
    echo "<script>window.location.href='desayuno.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Desayunos</title>
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
                <h5 class="mb-0">Administración de Desayunos</h5>
                <a href="agregar_desayuno.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Agregar Desayuno
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Desayuno</th>
                            <th>Imagen</th>
                            <th>Nombre Producto</th>
                            <th>Descripción</th> 
                            <th>Precio Desayuno</th>
                            <th>Cantidad Desayuno</th>
                            <th>Objetivo de ganancias</th>
                           
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($desayunos as $desayuno): ?>
                        <tr id="desayuno-<?php echo $desayuno['idDesayuno']; ?>">
                            <td><?php echo htmlspecialchars($desayuno['idDesayuno']); ?></td>
                            <td><img src="uploads/<?php echo htmlspecialchars($desayuno['imgDesayuno']); ?>" alt="Imagen Desayuno" style="width: 100px; height: auto;"></td>
                            <td><?php echo htmlspecialchars($desayuno['nombreProducto']); ?></td>
                            <td><?php echo htmlspecialchars($desayuno['descripcionDesayuno']); ?></td> 
                            <td><?php echo htmlspecialchars($desayuno['precioDesayuno']); ?></td>
                            <td><?php echo htmlspecialchars($desayuno['cantidadDesayuno']); ?></td>
                            <td><?php echo htmlspecialchars($desayuno['precioTotalDesayuno']); ?></td>
                            
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editarDesayuno(<?php echo $desayuno['idDesayuno']; ?>)">
                                        <i class='bx bx-edit-alt'></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $desayuno['idDesayuno']; ?>)">
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
    function editarDesayuno(idDesayuno) {
        window.location.href = 'editar_desayuno.php?id=' + idDesayuno;
    }

    function confirmarEliminar(idDesayuno) {
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
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idDesayuno';
                input.value = idDesayuno;

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