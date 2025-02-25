<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 1) {
    header("Location: login.php");
    exit;
}
require 'db_connection.php';

$productos = $pdo->query(" 
    SELECT 
        p.idProducto,
        c.nbCategoria,
        pr.nbProveedor,
        p.nbProducto,
        p.desProducto,
        p.precioProducto,
        p.imgProducto,
        t.nbTipo
    FROM productos p
    INNER JOIN categorias c ON p.idCategoria = c.idCategoria
    INNER JOIN proveedores pr ON p.idProveedor = pr.idProveedor
    INNER JOIN tipos_categorias t ON p.idTipo = t.idTipo
    ORDER BY p.idProducto ASC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Cafetería UTR</title>
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
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
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
    <div class="container-fluid">
        <div class="card">
            <div class="card-header bg-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-4">
                        <h5 class="mb-0">Todos los Productos</h5>
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
                                    <th>Imagen</th>
                                    <th>Producto</th>
                                    <th>Categoría</th>
                                    <th>Tipo</th>
                                    <th>Proveedor</th>
                                    <th>Precio</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($producto['idProducto']); ?></td>
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
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts necesarios -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>