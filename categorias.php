<?php
session_start();
include 'sidebar.php';
require 'db_connection.php';

// Obtener los datos de la tabla categorias
$categorias = $pdo->query("
    SELECT idCategoria, nbCategoria 
    FROM categorias
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Categorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet"> 
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h5 class="mb-0">Administración de Categorías</h5>
                    </div>
                    <a href="agregar_categoria.php" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Agregar Categoría
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Categoría</th>
                                <th>Nombre Categoría</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $categoria): ?>
                            <tr id="categoria-<?php echo $categoria['idCategoria']; ?>">
                                <td><?php echo htmlspecialchars($categoria['idCategoria']); ?></td>
                                <td><?php echo htmlspecialchars($categoria['nbCategoria']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_categoria.php?id=<?php echo $categoria['idCategoria']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class='bx bx-edit-alt'></i> Editar
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" onclick="confirmarEliminar(<?php echo $categoria['idCategoria']; ?>, '<?php echo htmlspecialchars($categoria['nbCategoria']); ?>')">
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
                ¿Estás seguro de que deseas eliminar la categoría "<span id="categoriaNombre"></span>"?
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
    let categoriaIdEliminar = null;

function confirmarEliminar(id, nombre) {
    categoriaIdEliminar = id;
    document.getElementById('categoriaNombre').textContent = nombre;
    const modal = new bootstrap.Modal(document.getElementById('confirmarEliminarModal'));
    modal.show();
}

document.getElementById('btnEliminarConfirmado').addEventListener('click', function () {
    if (categoriaIdEliminar) {
        // Realizar una petición AJAX para eliminar la categoría
        fetch('eliminar_categoria.php', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
    },
    body: `id=${categoriaIdEliminar}`  // Corregido el formato del cuerpo de la solicitud
})
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Eliminar la fila de la tabla
                document.getElementById(`categoria-${categoriaIdEliminar}`).remove();
            } else {
                alert('Ocurrió un error al intentar eliminar la categoría.');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    // Cerrar el modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('confirmarEliminarModal'));
    modal.hide();
});
</script>
</body>
</html>