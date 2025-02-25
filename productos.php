<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
    header("Location: login.php");
    exit;
}
include 'sidebar.php';
require 'db_connection.php';

// Verificar si se ha enviado un código de barras para buscar
$barcode = isset($_GET['barcode']) ? trim($_GET['barcode']) : '';

// Construir la consulta SQL
$sql = "
    SELECT 
        p.idProducto,
        c.nbCategoria,
        pr.nbProveedor,
        p.nbProducto,
        p.desProducto,
        p.precioProducto,
        p.imgProducto,
        t.nbTipo,
        p.codigo_barras
    FROM productos p
    INNER JOIN categorias c ON p.idCategoria = c.idCategoria
    INNER JOIN proveedores pr ON p.idProveedor = pr.idProveedor
    INNER JOIN tipos_categorias t ON p.idTipo = t.idTipo
";

// Si hay un código de barras, agregar el filtro a la consulta
if (!empty($barcode)) {
    $sql .= " WHERE p.codigo_barras = :barcode";
}

$sql .= " ORDER BY p.idProducto ASC";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);

if (!empty($barcode)) {
    $stmt->bindParam(':barcode', $barcode, PDO::PARAM_STR);
}

$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.075);
            cursor: pointer;
        }
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        /* .action-buttons {
            opacity: 0;
            transition: opacity 0.3s;
        }
        tr:hover .action-buttons {
            opacity: 1;
        } */
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            left: 10px;
        }
        .table th {
            border-top: none;
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .badge {
            font-weight: 500;
            padding: 0.5em 1em;
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .back-button:hover {
            transform: translateX(-3px);
        }
        .modal-confirm {
            color: #636363;
        }
        .modal-confirm .modal-content {
            padding: 20px;
            border-radius: 15px;
            border: none;
        }
        .modal-confirm .modal-header {
            border-bottom: none;
            position: relative;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .modal-confirm .icon-box {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            border-radius: 50%;
            z-index: 9;
            text-align: center;
            border: 3px solid #f15e5e;
        }
        .modal-confirm .icon-box i {
            color: #f15e5e;
            font-size: 46px;
            display: inline-block;
            margin-top: 13px;
        }
        .modal-confirm .btn {
            min-width: 100px;
        }
        .icon-box-wrapper {
            text-align: center;
            padding: 20px;
        }

        /* Nuevos estilos para el scroll */
        .table-container {
            position: relative;
            width: 100%;
            padding: 1rem;
        }

        .table-responsive {
            height: 84vh;
            overflow-y: auto;
            border-radius: 0.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
            border-bottom: 2px solid #dee2e6;
        }

        /* Estilos para la barra de desplazamiento */
        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body class="bg-light">
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-confirm">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <h5 class="modal-title w-100" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="icon-box-wrapper">
                        <div class="icon-box">
                            <i class="bx bx-trash"></i>
                        </div>
                        <p class="mt-4">¿Estás seguro de que deseas eliminar este producto?</p>
                        <p class="text-muted small">Esta acción no se puede deshacer</p>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid ">
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h5 class="mb-0">Administración de Productos</h5>
                    </div>
                    <div class="d-flex gap-3">
                    <form method="GET" action="" class="d-flex gap-2">
                        <input type="text" name="barcode" class="form-control" placeholder="Escanear o ingresar código de barras" 
                            value="<?php echo isset($_GET['barcode']) ? htmlspecialchars($_GET['barcode']) : ''; ?>" required>
                        
                        <?php if (isset($_GET['barcode'])): ?>
                            <!-- Mostrar el botón "Mostrar Todos" si hay una búsqueda activa -->
                            <a href="productos.php" class="btn btn-outline-secondary">
                                <i class='bx bx-refresh'></i> Mostrar Todos
                            </a>
                        <?php else: ?>
                            <!-- Mostrar el botón "Buscar" si no hay una búsqueda activa -->
                            <button type="submit" class="btn btn-outline-primary">
                                <i class='bx bx-search'></i> Buscar
                            </button>
                        <?php endif; ?>
                    </form>

                        <a href="agregar_producto.php" class="btn btn-primary">
                            <i class='bx bx-plus'></i> Agregar Producto
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código de Barras</th>
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Tipo</th>
                                    <th>Proveedor</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($productos)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No se encontraron productos con el código de barras ingresado.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                <?php foreach ($productos as $producto): ?>
                                <tr id="producto-<?php echo $producto['idProducto']; ?>">
                                    <td><?php echo htmlspecialchars($producto['idProducto']); ?></td>
                                    <td><?php echo htmlspecialchars($producto['codigo_barras']); ?></td>             
                                    <td>
                                        <img src="uploads/<?php echo htmlspecialchars($producto['imgProducto']); ?>"
                                             alt="<?php echo htmlspecialchars($producto['nbProducto']); ?>"
                                             class="product-img">
                                    </td>
                                    <td>
                                        <div class="fw-bold"><?php echo htmlspecialchars($producto['nbProducto']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($producto['desProducto']); ?></small>
                                    </td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($producto['nbCategoria']); ?></span></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($producto['nbTipo']); ?></span></td>
                                    <td><?php echo htmlspecialchars($producto['nbProveedor']); ?></td>
                                    <td class="fw-bold">$<?php echo number_format($producto['precioProducto'], 2); ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline-primary me-1" 
                                                    onclick="editarProducto(<?php echo $producto['idProducto']; ?>)">
                                                <i class='bx bx-edit-alt'></i> Editar
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger"
                                                    onclick="confirmarEliminar(<?php echo $producto['idProducto']; ?>)">
                                                <i class='bx bx-trash'></i> Eliminar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let productoIdAEliminar = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));

        function editarProducto(id) {
            window.location.href = `modificar_producto.php?id=${id}`;
        }

        function confirmarEliminar(id) {
            productoIdAEliminar = id;
            deleteModal.show();
        }

        document.getElementById('btnConfirmDelete').addEventListener('click', function() {
            if (productoIdAEliminar) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "eliminar_producto.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        const row = document.getElementById('producto-' + productoIdAEliminar);
                        row.style.transition = 'opacity 0.3s ease';
                        row.style.opacity = '0';
                        
                        setTimeout(() => {
                            row.remove();
                        }, 300);
                        
                        deleteModal.hide();
                    } else {
                        alert("Hubo un error al intentar eliminar el producto.");
                    }
                };
                
                xhr.send("id=" + productoIdAEliminar);
            }
        });
    </script>
</body>
</html>