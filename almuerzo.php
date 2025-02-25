<?php 
// session_start(); 
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }
include 'sidebar.php';
require 'db_connection.php';

$almuerzos = $pdo->query(" 
    SELECT 
        a.idAlmuerzo,
        a.imgAlmuerzo,
        a.nombreProducto,
        a.descripcionAlmuerzo,
        a.precioPorcion,
        a.precioMedia,
        a.precioOrden,
        a.cantidadPorcion,
        a.cantidadMedia,
        a.cantidadOrden,
        a.precioTotalAlmuerzo
    FROM almuerzo a
    ORDER BY a.idAlmuerzo ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idAlmuerzo'])) {
    $idAlmuerzo = $_POST['idAlmuerzo'];
    $stmt = $pdo->prepare("DELETE FROM almuerzo WHERE idAlmuerzo = ?");
    $stmt->execute([$idAlmuerzo]);
    echo "<script>window.location.href='almuerzo.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Almuerzos</title>
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
                <h5 class="mb-0">Administración de Almuerzos</h5>
                <a href="agregar_almuerzo.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Agregar Almuerzo
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Almuerzo</th>
                            <th>Imagen</th>
                            <th>Nombre Producto</th>
                            <th>Descripción</th> 
                            <th>Precio Porción</th>
                            <th>Precio Media</th>
                            <th>Precio Orden</th>
                            <th>Cantidad Porción</th>
                            <th>Cantidad Media</th>
                            <th>Cantidad Orden</th>
                            <th>Objetivo de ganancias</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($almuerzos as $almuerzo): ?>
                        <tr id="almuerzo-<?php echo $almuerzo['idAlmuerzo']; ?>">
                            <td><?php echo htmlspecialchars($almuerzo['idAlmuerzo']); ?></td>
                            <td><img src="uploads/<?php echo htmlspecialchars($almuerzo['imgAlmuerzo']); ?>" alt="Imagen Almuerzo" style="width: 100px; height: auto;"></td>
                            <td><?php echo htmlspecialchars($almuerzo['nombreProducto']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['descripcionAlmuerzo']); ?></td> 
                            <td><?php echo htmlspecialchars($almuerzo['precioPorcion']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['precioMedia']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['precioOrden']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['cantidadPorcion']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['cantidadMedia']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['cantidadOrden']); ?></td>
                            <td><?php echo htmlspecialchars($almuerzo['precioTotalAlmuerzo']); ?></td>
                            
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editarAlmuerzo(<?php echo $almuerzo['idAlmuerzo']; ?>)">
                                        <i class='bx bx-edit-alt'></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $almuerzo['idAlmuerzo']; ?>)">
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
    function editarAlmuerzo(idAlmuerzo) {
        window.location.href = 'editar_almuerzo.php?id=' + idAlmuerzo;
    }

    function confirmarEliminar(idAlmuerzo) {
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
                // Enviar formulario para eliminar el almuerzo
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idAlmuerzo';
                input.value = idAlmuerzo;

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