<?php

require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nbProveedor'];
    $telefono = $_POST['numTelefono'];
    $marca = $_POST['desMarca'];

    // Preparar la consulta de inserción
    $stmt = $pdo->prepare("INSERT INTO proveedores (nbProveedor, numTelefono, desMarca) VALUES (?, ?, ?)");
    $stmt->execute([$nombre, $telefono, $marca]);

    // Redirigir al dashboard de proveedores
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
    <title>Agregar Proveedor</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Agregar Proveedor</h4>
        <div class="card p-4">
            <form method="POST" action="agregar_proveedor.php">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Nombre del Proveedor</label>
                        <input type="text" name="nbProveedor" class="form-control" placeholder="Ingrese el nombre del proveedor" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Teléfono</label>
                        <input type="text" name="numTelefono" class="form-control" placeholder="Ingrese el teléfono" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Descripción de la Marca</label>
                        <input type="text" name="desMarca" class="form-control" placeholder="Ingrese la descripción de la marca" required>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="proveedores.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>