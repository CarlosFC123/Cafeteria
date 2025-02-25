<?php 

require 'db_connection.php';

if (isset($_GET['id'])) {
    $idInventario = $_GET['id'];
    
    // Obtener los datos del inventario por ID
    $stmt = $pdo->prepare("SELECT i.idInventario, i.idProducto, i.canActual, i.feActualizacion, i.estado_inventario, p.nbProducto 
                           FROM inventario i
                           JOIN productos p ON i.idProducto = p.idProducto
                           WHERE i.idInventario = ?");
    $stmt->execute([$idInventario]);
    $inventario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$inventario) {
        echo "Inventario no encontrado.";
        exit;
    }
}

// Actualizar los datos del inventario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProducto = $_POST['idProducto'];
    $canActual = $_POST['canActual'];
    $estado_inventario = $_POST['estado_inventario'];
    $feActualizacion = date('Y-m-d H:i:s');  // Fecha actual de actualización

    // Actualizar la información en la base de datos
    $stmt = $pdo->prepare("UPDATE inventario 
                           SET idProducto = ?, canActual = ?, estado_inventario = ?, feActualizacion = ?
                           WHERE idInventario = ?");
    $stmt->execute([$idProducto, $canActual, $estado_inventario, $feActualizacion, $idInventario]);

    // Redirigir después de la actualización
    header('Location: inventario.php');
    exit;
}

// Obtener los productos para el select
$productos = $pdo->query("SELECT idProducto, nbProducto FROM productos ORDER BY nbProducto")->fetchAll(PDO::FETCH_ASSOC);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inventario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Editar Inventario</h5>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="mb-3">
                    <label for="idProducto" class="form-label">Producto</label>
                    <select name="idProducto" id="idProducto" class="form-select" required>
                        <option value="">Seleccione un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo $producto['idProducto']; ?>" 
                                <?php echo ($producto['idProducto'] == $inventario['idProducto']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($producto['nbProducto']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="canActual" class="form-label">Cantidad Actual</label>
                    <input type="number" name="canActual" id="canActual" class="form-control" value="<?php echo htmlspecialchars($inventario['canActual']); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="estado_inventario" class="form-label">Estado del Inventario</label>
                    <select name="estado_inventario" id="estado_inventario" class="form-select" required>
                        <option value="Disponible" <?php echo ($inventario['estado_inventario'] == 'Disponible') ? 'selected' : ''; ?>>Disponible</option>
                        <option value="Agotado" <?php echo ($inventario['estado_inventario'] == 'Agotado') ? 'selected' : ''; ?>>Agotado</option>
                    </select>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary">Actualizar Inventario</button>
                    <a href="inventario.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
