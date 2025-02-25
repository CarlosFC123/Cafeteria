<?php

require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreCategoria = $_POST['nbCategoria'];

    $stmt = $pdo->prepare("INSERT INTO categorias (nbCategoria) VALUES (?)");
    $stmt->execute([$nombreCategoria]);

    header("Location: categorias.php");
    exit;
}
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Categoría</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Agregar Categoría</h4>
        <div class="card p-4">
            <form method="POST" action="agregar_categoria.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre de la Categoría</label>
                        <input type="text" name="nbCategoria" class="form-control" placeholder="Ingrese el nombre de la categoría" required>
                    </div>
                    <div class="col-md-6 align-self-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="categorias.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>