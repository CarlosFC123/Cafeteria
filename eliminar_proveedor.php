<?php
require 'db_connection.php';

// Procesar eliminación de proveedor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProveedor = $_POST['idProveedor'];

    // Eliminar el proveedor de la base de datos
    $stmt = $pdo->prepare("DELETE FROM proveedores WHERE idProveedor=?");
    $stmt->execute([$idProveedor]);

    // Redirigir al dashboard de proveedores
    header("Location: proveedores.php");
    exit;
}

// Obtener todos los proveedores
$proveedores = $pdo->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <title>Eliminar Proveedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Eliminar Proveedor</h4>
        <form method="POST">
            <label>Selecciona un proveedor a eliminar:</label>
            <select name="idProveedor" class="form-select mb-3">
                <option value="">-- Seleccionar --</option>
                <?php foreach ($proveedores as $proveedor): ?>
                    <option value="<?php echo $proveedor['idProveedor']; ?>">
                        <?php echo $proveedor['nbProveedor']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-success">Eliminar</button>
            <a href="proveedores.php" class="btn btn-danger">Cancelar</a>
        </form>
    </div>
</body>
</html>