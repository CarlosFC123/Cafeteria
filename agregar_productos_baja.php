<?php
// session_start();
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }

require 'db_connection.php';

// Manejo del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProducto = $_POST['idProducto'] ?? null;
    $cantidadBaja = $_POST['cantidad_baja'] ?? null;
    $desProducto = $_POST['desProducto'] ?? null;
    $feBaja = $_POST['feBaja'] ?? null;

    if ($idProducto && $cantidadBaja && $desProducto && $feBaja) {
        try {
            // Iniciar una transacción
            $pdo->beginTransaction();

            // Obtener la cantidad actual del producto en el inventario
            $stmt = $pdo->prepare("SELECT canActual FROM inventario WHERE idProducto = :idProducto");
            $stmt->execute([':idProducto' => $idProducto]);
            $inventario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$inventario || $inventario['canActual'] < $cantidadBaja) {
                throw new Exception("No hay suficiente stock para dar de baja.");
            }

            // Calcular la nueva cantidad en el inventario
            $nuevaCantidad = $inventario['canActual'] - $cantidadBaja;

            // Actualizar el inventario
            $stmt = $pdo->prepare("UPDATE inventario SET canActual = :nuevaCantidad WHERE idProducto = :idProducto");
            $stmt->execute([':nuevaCantidad' => $nuevaCantidad, ':idProducto' => $idProducto]);

            // Insertar el registro en productos_baja
            $stmt = $pdo->prepare("
                INSERT INTO productos_baja (idProducto, cantidad_baja, desProducto, feBaja, cantidadProducto, TotalCantidadP)
                VALUES (:idProducto, :cantidad_baja, :desProducto, :feBaja, :cantidadProducto, :TotalCantidadP)
            ");
            $stmt->execute([
                ':idProducto' => $idProducto,
                ':cantidad_baja' => $cantidadBaja,
                ':desProducto' => $desProducto,
                ':feBaja' => $feBaja,
                ':cantidadProducto' => $inventario['canActual'], // Cantidad antes de la baja
                ':TotalCantidadP' => $nuevaCantidad, // Cantidad después de la baja
            ]);

            // Confirmar la transacción
            $pdo->commit();

            header("Location: productos_baja.php?success=1");
            exit;
        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = "Por favor, completa todos los campos.";
    }
}

// Obtener productos existentes
$productos = $pdo->query("SELECT idProducto, nbProducto FROM productos ORDER BY nbProducto ASC")->fetchAll(PDO::FETCH_ASSOC);
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto Dado de Baja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="card">
        <div class="card-header bg-white">
            <h5 class="mb-0">Agregar Producto Dado de Baja</h5>
        </div>
        <div class="card-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"> <?php echo htmlspecialchars($error); ?> </div>
            <?php endif; ?>

            <form action="" method="POST">
                <div class="mb-3">
                    <label for="idProducto" class="form-label">Producto</label>
                    <select name="idProducto" id="idProducto" class="form-select" required>
                        <option value="">Selecciona un producto</option>
                        <?php foreach ($productos as $producto): ?>
                            <option value="<?php echo htmlspecialchars($producto['idProducto']); ?>">
                                <?php echo htmlspecialchars($producto['nbProducto']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="cantidad_baja" class="form-label">Cantidad Dada de Baja</label>
                    <input type="number" name="cantidad_baja" id="cantidad_baja" class="form-control" required min="1">
                </div>

                <div class="mb-3">
                    <label for="desProducto" class="form-label">Descripción</label>
                    <select class="form-select" id="desProducto" name="desProducto" required>
                        <option value="">-- Seleccionar Descripción --</option>
                        <option value="Caducidad">Caducidad</option>
                        <option value="Daño">Daño</option>
                        <option value="Otros">Otros</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="feBaja" class="form-label">Fecha de Baja</label>
                    <input type="date" name="feBaja" id="feBaja" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="productos_baja.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>