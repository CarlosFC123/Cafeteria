<?php
session_start();
include 'sidebar.php';
require 'db_connection.php';

// Obtener los datos de la tabla tipos_categorias
$tipos = $pdo->query("
    SELECT idTipo, nbTipo 
    FROM tipos_categorias
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!-- <style>
    .content {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            left: 10px;
        }
        .action-buttons {
            opacity: 0;
            transition: opacity 0.3s;
        }
        tr:hover .action-buttons {
            opacity: 1;
        }
        
</style>-->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet"> 
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Tipos de Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h5 class="mb-0">Administración de Tipos de Categorías</h5>
                    </div>
                    <a href="agregar_tipo_categoria.php" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Agregar Tipo
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Tipo</th>
                                <th>Nombre Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tipos as $tipo): ?>
                            <tr id="tipo-<?php echo $tipo['idTipo']; ?>">
                                <td><?php echo htmlspecialchars($tipo['idTipo']); ?></td>
                                <td><?php echo htmlspecialchars($tipo['nbTipo']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="modificar_tipo_categoria.php?id=<?php echo $tipo['idTipo']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class='bx bx-edit-alt'></i> Editar
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(<?php echo $tipo['idTipo']; ?>, '<?php echo htmlspecialchars($tipo['nbTipo']); ?>')">
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
</div>

<!-- Modal de Confirmación -->
<div class="modal fade" id="confirmarEliminarModal" tabindex="-1" aria-labelledby="confirmarEliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmarEliminarModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar el tipo "<span id="tipoNombre"></span>"?
                Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btnEliminarConfirmado">Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let tipoIdEliminar = null;

    function confirmarEliminar(id, nombre) {
        tipoIdEliminar = id;
        document.getElementById('tipoNombre').textContent = nombre;
        const modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
        modal.show();
    }

    document.getElementById('btnEliminarConfirmado').addEventListener('click', function () {
    if (tipoIdEliminar) {
        // Realizar una petición AJAX para eliminar el tipo
        fetch('eliminar_tipo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({ id: tipoIdEliminar }).toString()
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Animar la eliminación de la fila
                const row = document.getElementById(`tipo-${tipoIdEliminar}`);
                row.style.transition = 'opacity 0.5s, transform 0.5s';
                row.style.opacity = '0';
                row.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    row.remove();
                    Swal.fire({
                        icon: 'success',
                        title: '¡Eliminado!',
                        text: 'El tipo ha sido eliminado correctamente.',
                        showConfirmButton: false,
                        timer: 1500
                    });
                }, 500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al intentar eliminar el tipo.',
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Ocurrió un error inesperado.',
            });
        });
    }

    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmarEliminarModal'));
    modal.hide();
});
</script>
</body>
</html>
