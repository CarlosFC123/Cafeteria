<?php 

include 'sidebar.php';
require 'db_connection.php';

$inventario = $pdo->query(" 
    SELECT 
        i.idInventario,
        i.idProducto,
        p.nbProducto,  -- Nombre del producto
        i.canActual,
        i.feActualizacion,
        CASE 
            WHEN i.canActual > 0 THEN 'Disponible'
            ELSE 'Agotado'
        END AS estado_inventario
    FROM inventario i
    JOIN productos p ON i.idProducto = p.idProducto  -- JOIN con la tabla productos
    ORDER BY i.idInventario ASC
")->fetchAll(PDO::FETCH_ASSOC);

// Handle delete request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['idInventario'])) {
    $idInventario = $_POST['idInventario'];
    $stmt = $pdo->prepare("DELETE FROM inventario WHERE idInventario = ?");
    $stmt->execute([$idInventario]);
    echo "<script>window.location.href='inventario.php';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Inventario</title>
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
                <h5 class="mb-0">Administración de Inventario</h5>
                <a href="agregar_inventario.php" class="btn btn-primary">
                    <i class='bx bx-plus'></i> Agregar al Inventario
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID Inventario</th>
                            <th>Nombre Producto</th> <!-- Cambié el título aquí -->
                            <th>Cantidad Actual</th>
                            <th>Fecha de Actualización</th>
                            <th>Estado del Inventario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($inventario as $item): ?>
                        <tr id="inventario-<?php echo $item['idInventario']; ?>">
                            <td><?php echo htmlspecialchars($item['idInventario']); ?></td>
                            <td><?php echo htmlspecialchars($item['nbProducto']); ?></td> <!-- Aquí se muestra el nombre del producto -->
                            <td><?php echo htmlspecialchars($item['canActual']); ?></td>
                            <td><?php echo htmlspecialchars($item['feActualizacion']); ?></td>
                            <td><?php echo htmlspecialchars($item['estado_inventario']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-outline-primary me-1" 
                                            onclick="editarInventario(<?php echo $item['idInventario']; ?>)">
                                        <i class='bx bx-edit-alt'></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="confirmarEliminar(<?php echo $item['idInventario']; ?>)">
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
    function editarInventario(idInventario) {
        window.location.href = 'editar_inventario.php?id=' + idInventario;
    }

    function confirmarEliminar(idInventario) {
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
                // Enviar formulario para eliminar el inventario
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '';

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'idInventario';
                input.value = idInventario;

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
