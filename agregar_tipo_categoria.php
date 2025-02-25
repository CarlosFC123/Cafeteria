<?php

require 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreTipo = $_POST['nbTipo'];

    // Insertar el nombre del tipo en la tabla tipos_categorias
    $stmt = $pdo->prepare("INSERT INTO tipos_categorias (nbTipo) VALUES (?)");
    $stmt->execute([$nombreTipo]);

    // Redirigir a la página principal de tipos
    header("Location: tipo_categorias.php");
    exit;
}
include 'sidebar.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Tipo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-4">
        <h4>Agregar Tipo</h4>
        <div class="card p-4">
            <form method="POST" action="agregar_tipo_categoria.php">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nombre del Tipo</label>
                        <input type="text" name="nbTipo" class="form-control" placeholder="Ingrese el nombre del tipo" required>
                    </div>
                    <div class="col-md-6 align-self-end">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <a href="tipo_categorias.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>