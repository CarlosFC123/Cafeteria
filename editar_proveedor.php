<?php

require 'db_connection.php';

// Obtener el proveedor seleccionado desde la URL
$proveedorSeleccionado = null;
if (isset($_GET['id'])) {
    $idProveedor = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM proveedores WHERE idProveedor = ?");
    $stmt->execute([$idProveedor]);
    $proveedorSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proveedorSeleccionado) {
        echo "<script>alert('Proveedor no encontrado'); window.location.href='proveedores.php';</script>";
        exit;
    }
}

// Procesar actualización de datos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $idProveedor = $_POST['idProveedor'];
    $nombre = $_POST['nbProveedor'];
    $telefono = $_POST['numTelefono'];
    $marca = $_POST['desMarca'];

    $stmt = $pdo->prepare("UPDATE proveedores SET nbProveedor=?, numTelefono=?, desMarca=? WHERE idProveedor=?");
    $stmt->execute([$nombre, $telefono, $marca, $idProveedor]);

    header("Location: proveedores.php");
    exit;
}
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Editar Proveedor</h4>
        <div class="card p-4">
            <form method="POST">
                <input type="hidden" name="idProveedor" value="<?php echo $proveedorSeleccionado['idProveedor']; ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre del Proveedor</label>
                        <input type="text" name="nbProveedor" class="form-control" placeholder="Nuevo Nombre" value="<?php echo htmlspecialchars($proveedorSeleccionado['nbProveedor']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="numTelefono" class="form-control" placeholder="Nuevo Teléfono" value="<?php echo htmlspecialchars($proveedorSeleccionado['numTelefono']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Descripción de la Marca</label>
                        <input type="text" name="desMarca" class="form-control" placeholder="Nueva Descripción de la Marca" value="<?php echo htmlspecialchars($proveedorSeleccionado['desMarca']); ?>" required>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" name="update" class="btn btn-primary">Guardar Cambios</button>
                        <a href="proveedores.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>