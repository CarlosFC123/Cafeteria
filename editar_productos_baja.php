<?php
require 'db_connection.php';

// Obtener el id del producto de baja
$idProductoBaja = $_GET['id'] ?? null;
$producto_baja = null;

if ($idProductoBaja) {
    // Consulta para obtener el producto de baja
    $stmt = $pdo->prepare("SELECT pb.idProductoBaja, pb.idProducto, p.nbProducto AS nombreProducto, pb.feBaja, pb.desProducto, pb.cantidad_baja, pb.cantidadProducto, pb.TotalCantidadP
                           FROM productos_baja pb
                           JOIN productos p ON pb.idProducto = p.idProducto
                           WHERE pb.idProductoBaja = :idProductoBaja");
    $stmt->execute(['idProductoBaja' => $idProductoBaja]);
    $producto_baja = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$producto_baja) {
        echo "<script>alert('Producto no encontrado.'); window.location.href='productos_baja.php';</script>";
        exit;
    }
}

$stmtProductos = $pdo->query("SELECT idProducto, nbProducto FROM productos");
$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProductoBaja = $_POST['idProductoBaja'];
    $idProducto = $_POST['idProducto'];
    $feBaja = $_POST['feBaja'];
    $desProducto = $_POST['desProducto'];
    $cantidad_baja = $_POST['cantidad_baja'];

    // Validar que los campos no estén vacíos
    if (empty($idProducto) || empty($feBaja) || empty($desProducto) || empty($cantidad_baja)) {
        echo "<script>alert('Todos los campos son obligatorios.');</script>";
    } else {
        // Obtener la cantidad_baja anterior
        $stmt = $pdo->prepare("SELECT cantidad_baja FROM productos_baja WHERE idProductoBaja = :idProductoBaja");
        $stmt->execute(['idProductoBaja' => $idProductoBaja]);
        $cantidad_baja_anterior = $stmt->fetchColumn();

        // Calcular la diferencia entre la cantidad_baja nueva y la anterior
        $diferencia = $cantidad_baja - $cantidad_baja_anterior;

        // Actualizar el inventario
        if ($diferencia != 0) {
            $stmt = $pdo->prepare("UPDATE inventario SET canActual = canActual - :diferencia WHERE idProducto = :idProducto");
            $stmt->execute(['diferencia' => $diferencia, 'idProducto' => $idProducto]);
        }

        // Actualizar el producto de baja
        $stmt = $pdo->prepare("UPDATE productos_baja
                               SET idProducto = :idProducto, feBaja = :feBaja, desProducto = :desProducto, cantidad_baja = :cantidad_baja
                               WHERE idProductoBaja = :idProductoBaja");
        $stmt->execute([
            'idProducto' => $idProducto,
            'feBaja' => $feBaja,
            'desProducto' => $desProducto,
            'cantidad_baja' => $cantidad_baja,
            'idProductoBaja' => $idProductoBaja
        ]);

        header('Location: productos_baja.php');
        exit;
    }
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto Dado de Baja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <style>
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
        }
        .form-control, .form-select {
            border-radius: 8px;
            appearance: none;
            padding-right: 2rem;
        }
        .btn-group-horizontal {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
        }
    </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Editar Producto Dado de Baja</h5>
        </div>
        <div class="card-body">
            <?php if ($producto_baja): ?>
            <form method="POST">
                <input type="hidden" name="idProductoBaja" value="<?php echo htmlspecialchars($producto_baja['idProductoBaja']); ?>">

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="idProducto" class="form-label">Nombre Producto</label>
                        <select class="form-select" id="idProducto" name="idProducto" required>
                            <option value="">-- Seleccionar Producto --</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo htmlspecialchars($producto['idProducto']); ?>" 
                                    <?php echo ($producto['idProducto'] == $producto_baja['idProducto']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($producto['nbProducto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-6 mb-4">
                        <label for="feBaja" class="form-label">Fecha Baja</label>
                        <input type="date" class="form-control" id="feBaja" name="feBaja" value="<?php echo htmlspecialchars($producto_baja['feBaja']); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="desProducto" class="form-label">Descripción Producto</label>
                        <select class="form-select" id="desProducto" name="desProducto" required>
                            <option value="">-- Seleccionar Descripción --</option>
                            <option value="Caducidad" <?php echo ($producto_baja['desProducto'] == 'Caducidad') ? 'selected' : ''; ?>>Caducidad</option>
                            <option value="Daño" <?php echo ($producto_baja['desProducto'] == 'Daño') ? 'selected' : ''; ?>>Daño</option>
                            <option value="Otros" <?php echo ($producto_baja['desProducto'] == 'Otros') ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-4">
                        <label for="cantidad_baja" class="form-label">Cantidad Baja</label>
                        <input type="number" class="form-control" id="cantidad_baja" name="cantidad_baja" value="<?php echo htmlspecialchars($producto_baja['cantidad_baja']); ?>" required>
                    </div>
                </div>

                <div class="btn-group-horizontal">
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    <a href="productos_baja.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
            <?php else: ?>
            <p class="text-danger">Producto dado de baja no encontrado.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>