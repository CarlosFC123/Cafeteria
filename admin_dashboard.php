<?php
session_start();
include 'sidebar.php';
require 'db_connection.php';

$usuarios = $pdo->query("
    SELECT u.idUsuario, u.nbUsuario, u.apellidoUsuario, u.numTelefonoU, u.email, r.nombreRol 
    FROM usuario u
    INNER JOIN roles r ON u.idRol = r.idRol
")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
    .content {
        border: none;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
        left: 10px;
    }
    .action-buttons {
        opacity: 0;
        transition: opacity 0.3s;
    }
    tr:hover .action-buttons {
        opacity: 1;
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Administración de Usuarios</h5>
                    <a href="admin.php" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Agregar Usuario
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Usuario</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                            <tr id="usuario-<?php echo $usuario['idUsuario']; ?>">
                                <td><?php echo htmlspecialchars($usuario['idUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nbUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['apellidoUsuario']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['numTelefonoU']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombreRol']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="modificar_usuario.php?id=<?php echo $usuario['idUsuario']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class='bx bx-edit-alt'></i> Editar
                                        </a>
                                        <button 
                                            class="btn btn-sm btn-outline-danger eliminar-usuario" 
                                            data-id="<?php echo $usuario['idUsuario']; ?>" 
                                            data-nombre="<?php echo htmlspecialchars($usuario['nbUsuario']); ?>">
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

<script>
    // Manejar el evento de clic en el botón de eliminar
    document.querySelectorAll('.eliminar-usuario').forEach(button => {
        button.addEventListener('click', function () {
            const idUsuario = this.getAttribute('data-id');
            const nombreUsuario = this.getAttribute('data-nombre');

            // Mostrar cuadro de confirmación
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Estás a punto de eliminar a ${nombreUsuario}. Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Llamar a AJAX para eliminar el usuario
                    fetch('eliminar_usuario.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ idUsuario: idUsuario })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Eliminar la fila de la tabla
                            const row = document.getElementById(`usuario-${idUsuario}`);
                            if (row) row.remove();

                            // Mostrar mensaje de éxito
                            Swal.fire(
                                'Eliminado',
                                `El usuario ${nombreUsuario} ha sido eliminado.`,
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error',
                                'No se pudo eliminar el usuario. Intenta de nuevo.',
                                'error'
                            );
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire(
                            'Error',
                            'Ocurrió un problema al intentar eliminar el usuario.',
                            'error'
                        );
                    });
                }
            });
        });
    });
</script>
