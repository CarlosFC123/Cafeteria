<?php

require 'db_connection.php';

// Obtener todos los productos disponibles
$productos = $pdo->query("SELECT idProducto, nbProducto FROM productos")->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProducto = $_POST['idProducto'];
    $canActual = $_POST['canActual'];
    $estadoInventario = $_POST['estado_inventario']; // Obtener el estado del inventario

    // Insertar nuevo inventario
    $stmt = $pdo->prepare("INSERT INTO inventario (idProducto, canActual, estado_inventario, feActualizacion) 
                           VALUES (?, ?, ?, NOW())");
    $stmt->execute([$idProducto, $canActual, $estadoInventario]);

    // Redirigir al inventario después de agregar
    echo "<script>window.location.href='inventario.php';</script>";
    exit;
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar al Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Agregar al Inventario</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="idProducto" class="form-label">Seleccionar Producto</label>
                    <select class="form-select" name="idProducto" id="idProducto" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['idProducto']; ?>">
                                <?php echo htmlspecialchars($producto['nbProducto']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="canActual" class="form-label">Cantidad</label>
                    <input type="number" class="form-control" id="canActual" name="canActual" required min="1">
                </div>
                <!-- Campo de estado del inventario con opciones 'Disponible' y 'Agotado' -->
                <div class="mb-3">
                    <label for="estado_inventario" class="form-label">Estado del Inventario</label>
                    <select class="form-select" name="estado_inventario" id="estado_inventario" required>
                        <option value="Disponible">Disponible</option>
                        <option value="Agotado">Agotado</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Agregar al Inventario</button>
                <a href="inventario.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
