<?php
// session_start();
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }

require 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreProducto = $_POST['nombreProducto'];
    $precioDesayuno = $_POST['precioDesayuno'];
    $cantidadDesayuno = $_POST['cantidadDesayuno'];
    $precioTotalDesayuno = $_POST['precioTotalDesayuno'];
    $descripcionDesayuno = $_POST['descripcionDesayuno']; // Captura la descripción

    // Manejar la subida de la imagen
    if (isset($_FILES['imgDesayuno']) && $_FILES['imgDesayuno']['error'] == 0) {
        $imgDesayuno = $_FILES['imgDesayuno'];
        $imgName = time() . '_' . $imgDesayuno['name'];
        move_uploaded_file($imgDesayuno['tmp_name'], 'uploads/' . $imgName);
    } else {
        $imgName = 'default.jpg'; // Imagen por defecto si no se sube ninguna
    }

    // Insertar nuevo desayuno en la base de datos
    $insert = $pdo->prepare("INSERT INTO desayuno (nombreProducto, precioDesayuno, cantidadDesayuno, precioTotalDesayuno, imgDesayuno, descripcionDesayuno) VALUES (:nombreProducto, :precioDesayuno, :cantidadDesayuno, :precioTotalDesayuno, :imgDesayuno, :descripcionDesayuno)");
    
    $insert->bindParam(':nombreProducto', $nombreProducto);
    $insert->bindParam(':precioDesayuno', $precioDesayuno);
    $insert->bindParam(':cantidadDesayuno', $cantidadDesayuno);
    $insert->bindParam(':precioTotalDesayuno', $precioTotalDesayuno);
    $insert->bindParam(':imgDesayuno', $imgName);
    $insert->bindParam(':descripcionDesayuno', $descripcionDesayuno); // Vincular la descripción
    
    $insert->execute();

    header("Location: desayuno.php");
    exit;
}

include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Nuevo Desayuno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border: none;
            box-shadow: 0 0 20px rgba(0,0,0,0.08);
            border-radius: 15px;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .form-label {
            font-size: 0.85rem;
            font-weight: 500;
            color: #666;
            margin-bottom: 0.3rem;
        }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="card">
        <div class="card-header py-2">
            <h5 class="mb-0">Agregar Nuevo Desayuno</h5>
        </div>
        <div class="card-body p-3">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Columna izquierda -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" required>
                        </div>
                        <div class="mb-3">
                            <label for="imgDesayuno" class="form-label">Imagen del Desayuno</label>
                            <input type="file" class="form-control" id="imgDesayuno" name="imgDesayuno" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcionDesayuno" class="form-label">Descripción del Desayuno</label>
                            <textarea class="form-control" id="descripcionDesayuno" name="descripcionDesayuno" rows="3" required></textarea>
                        </div>
                    </div>

                    <!-- Columna derecha - Precio y Cantidad -->
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="precioDesayuno" class="form-label">Precio Desayuno</label>
                            <input type="number" class="form-control" id="precioDesayuno" name="precioDesayuno" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="cantidadDesayuno" class="form-label">Cantidad Desayuno</label>
                            <input type="number" class="form-control" id="cantidadDesayuno" name="cantidadDesayuno" required>
                        </div>
                        <div class="mb-3">
                            <label for="precioTotalDesayuno" class="form-label">Objetivo de Ganancias</label>
                            <input type="number" class="form-control" id="precioTotalDesayuno" name="precioTotalDesayuno" step="0.01" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <a href="desayuno.php" class="btn btn-secondary px-4 me-2">
                            <i class="bx bx-x me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i> Guardar Desayuno
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Función para calcular el precio total
    function calcularPrecioTotal() {
        const precioDesayuno = parseFloat(document.getElementById('precioDesayuno').value) || 0;
        const cantidadDesayuno = parseFloat(document.getElementById('cantidadDesayuno').value) || 0;

        const total = precioDesayuno * cantidadDesayuno;
        document.getElementById('precioTotalDesayuno').value = total.toFixed(2);
    }

    // Agregar eventos para recalcular el total cuando se cambien los valores
    document.getElementById('precioDesayuno').addEventListener('input', calcularPrecioTotal);
    document.getElementById('cantidadDesayuno').addEventListener('input', calcularPrecioTotal);
</script>

</body>
</html>