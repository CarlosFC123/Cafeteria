<?php
session_start();
include 'sidebar.php';
require 'db_connection.php';

// Obtener los datos de la tabla proveedores
$proveedores = $pdo->query("
    SELECT p.idProveedor, p.nbProveedor, p.numTelefono, p.desMarca
    FROM proveedores p
")->fetchAll(PDO::FETCH_ASSOC);
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h5 class="mb-0">Administración de Proveedores</h5>
                    </div>
                    <a href="agregar_proveedor.php" class="btn btn-primary">
                        <i class='bx bx-plus'></i> Agregar Proveedor
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>ID Proveedor</th>
                                <th>Nombre Proveedor</th>
                                <th>Teléfono</th>
                                <th>Descripción Marca</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($proveedores as $proveedor): ?>
                            <tr id="proveedor-<?php echo $proveedor['idProveedor']; ?>">
                                <td><?php echo htmlspecialchars($proveedor['idProveedor']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['nbProveedor']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['numTelefono']); ?></td>
                                <td><?php echo htmlspecialchars($proveedor['desMarca']); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="editar_proveedor.php?id=<?php echo $proveedor['idProveedor']; ?>" class="btn btn-sm btn-outline-primary me-1">
                                            <i class='bx bx-edit-alt'></i> Editar
                                        </a>
                                        <a href="eliminar_proveedor.php?id=<?php echo $proveedor['idProveedor']; ?>" class="btn btn-sm btn-outline-danger">
                                            <i class='bx bx-trash'></i> Eliminar
                                        </a>
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