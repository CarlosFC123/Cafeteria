<?php
// session_start();
// if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
//     header("Location: login.php");
//     exit;
// }

require 'db_connection.php';

// Verificar si se pasó un ID de almuerzo
if (isset($_GET['id'])) {
    $idAlmuerzo = $_GET['id'];
    $almuerzo = $pdo->prepare("SELECT * FROM almuerzo WHERE idAlmuerzo = :idAlmuerzo");
    $almuerzo->bindParam(':idAlmuerzo', $idAlmuerzo, PDO::PARAM_INT);
    $almuerzo->execute();
    $almuerzo = $almuerzo->fetch(PDO::FETCH_ASSOC);
    
    if (!$almuerzo) {
        echo "Almuerzo no encontrado.";
        exit;
    }
} else {
    echo "ID de almuerzo no proporcionado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombreProducto = $_POST['nombreProducto'];
    $precioPorcion = $_POST['precioPorcion'];
    $precioMedia = $_POST['precioMedia'];
    $precioOrden = $_POST['precioOrden'];
    $cantidadPorcion = $_POST['cantidadPorcion'];
    $cantidadMedia = $_POST['cantidadMedia'];
    $cantidadOrden = $_POST['cantidadOrden'];
    $precioTotalAlmuerzo = $_POST['precioTotalAlmuerzo'];
    $descripcionAlmuerzo = $_POST['descripcionAlmuerzo']; // Captura la descripción

    // Subir imagen si es nueva
    if ($_FILES['imgAlmuerzo']['name']) {
        $imgAlmuerzo = $_FILES['imgAlmuerzo'];
        $imgName = time() . '_' . $imgAlmuerzo['name'];
        move_uploaded_file($imgAlmuerzo['tmp_name'], 'uploads/' . $imgName);
    } else {
        $imgName = $almuerzo['imgAlmuerzo'];  // Mantener la imagen actual
    }

    // Actualizar almuerzo en la base de datos
    $update = $pdo->prepare("UPDATE almuerzo SET nombreProducto = :nombreProducto, precioPorcion = :precioPorcion, precioMedia = :precioMedia, precioOrden = :precioOrden, cantidadPorcion = :cantidadPorcion, cantidadMedia = :cantidadMedia, cantidadOrden = :cantidadOrden, precioTotalAlmuerzo = :precioTotalAlmuerzo, imgAlmuerzo = :imgAlmuerzo, descripcionAlmuerzo = :descripcionAlmuerzo WHERE idAlmuerzo = :idAlmuerzo");
    $update->bindParam(':nombreProducto', $nombreProducto);
    $update->bindParam(':precioPorcion', $precioPorcion);
    $update->bindParam(':precioMedia', $precioMedia);
    $update->bindParam(':precioOrden', $precioOrden);
    $update->bindParam(':cantidadPorcion', $cantidadPorcion);
    $update->bindParam(':cantidadMedia', $cantidadMedia);
    $update->bindParam(':cantidadOrden', $cantidadOrden);
    $update->bindParam(':precioTotalAlmuerzo', $precioTotalAlmuerzo);
    $update->bindParam(':imgAlmuerzo', $imgName);
    $update->bindParam(':descripcionAlmuerzo', $descripcionAlmuerzo); // Vincular la descripción
    $update->bindParam(':idAlmuerzo', $idAlmuerzo);
    $update->execute();

    header("Location: almuerzo.php"); // Redirigir después de actualizar
    exit;
}

include 'sidebar.php'; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Almuerzo</title>
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
        .img-preview {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0.5rem 0;
        }
        .btn-success {
            padding: 0.5rem 2rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
<div class="container py-3">
    <div class="card">
        <div class="card-header py-2">
            <h5 class="mb-0">Modificar Almuerzo</h5>
        </div>
        <div class="card-body p-3">
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Columna izquierda -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="nombreProducto" class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" id="nombreProducto" name="nombreProducto" 
                                   value="<?php echo htmlspecialchars($almuerzo['nombreProducto']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="imgAlmuerzo" class="form-label">Imagen del Almuerzo</label>
                            <input type="file" class="form-control" id="imgAlmuerzo" name="imgAlmuerzo">
                            <?php if ($almuerzo['imgAlmuerzo']): ?>
                                <img src="uploads/<?php echo htmlspecialchars($almuerzo['imgAlmuerzo']); ?>" alt="Imagen Almuerzo" class="img-preview mt-2">
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label for="descripcionAlmuerzo" class="form-label">Descripción del Almuerzo</label>
                            <textarea class="form-control" id="descripcionAlmuerzo" name="descripcionAlmuerzo" rows="3" required><?php echo htmlspecialchars($almuerzo['descripcionAlmuerzo']); ?></textarea>
                        </div>
                    </div>

                    <!-- Columna central - Precios -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="precioPorcion" class="form-label">Precio Porción</label>
                            <input type="number" class="form-control" id="precioPorcion" name="precioPorcion" 
                                   value="<?php echo htmlspecialchars($almuerzo['precioPorcion']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="precioMedia" class="form-label">Precio Media</label>
                            <input type="number" class="form-control" id="precioMedia" name="precioMedia" 
                                   value="<?php echo htmlspecialchars($almuerzo['precioMedia']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="precioOrden" class="form-label">Precio Orden</label>
                            <input type="number" class="form-control" id="precioOrden" name="precioOrden" 
                                   value="<?php echo htmlspecialchars($almuerzo['precioOrden']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="precioTotalAlmuerzo" class="form-label">Objetivo de Ganancias</label>
                            <input type="number" class="form-control" id="precioTotalAlmuerzo" name="precioTotalAlmuerzo" 
                                   value="<?php echo htmlspecialchars($almuerzo['precioTotalAlmuerzo']); ?>" readonly>
                        </div>
                    </div>

                    <!-- Columna derecha - Cantidades -->
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label for="cantidadPorcion" class="form-label">Cantidad Porción</label>
                            <input type="number" class="form-control" id="cantidadPorcion" name="cantidadPorcion" 
                                   value="<?php echo htmlspecialchars($almuerzo['cantidadPorcion']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="cantidadMedia" class="form-label">Cantidad Media</label>
                            <input type="number" class="form-control" id="cantidadMedia" name="cantidadMedia" 
                                   value="<?php echo htmlspecialchars($almuerzo['cantidadMedia']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="cantidadOrden" class="form-label">Cantidad Orden</label>
                            <input type="number" class="form-control" id="cantidadOrden" name="cantidadOrden" 
                                   value="<?php echo htmlspecialchars($almuerzo['cantidadOrden']); ?>" required>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <a href="almuerzo.php" class="btn btn-secondary px-4 me-2">
                            <i class="bx bx-x me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bx bx-save me-1"></i> Actualizar Almuerzo
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
        const precioPorcion = parseFloat(document.getElementById('precioPorcion').value) || 0;
        const cantidadPorcion = parseFloat(document.getElementById('cantidadPorcion').value) || 0;
        const precioMedia = parseFloat(document.getElementById('precioMedia').value) || 0;
        const cantidadMedia = parseFloat(document.getElementById('cantidadMedia').value) || 0;
        const precioOrden = parseFloat(document.getElementById('precioOrden').value) || 0;
        const cantidadOrden = parseFloat(document.getElementById('cantidadOrden').value) || 0;

        const total = (precioPorcion * cantidadPorcion) + (precioMedia * cantidadMedia) + (precioOrden * cantidadOrden);
        document.getElementById('precioTotalAlmuerzo').value = total.toFixed(2);
    }

    // Agregar eventos para recalcular el total cuando se cambien los valores
    document.getElementById('precioPorcion').addEventListener('input', calcularPrecioTotal);
    document.getElementById('cantidadPorcion').addEventListener('input', calcularPrecioTotal);
    document.getElementById('precioMedia').addEventListener('input', calcularPrecioTotal);
    document.getElementById('cantidadMedia').addEventListener('input', calcularPrecioTotal);
    document.getElementById('precioOrden').addEventListener('input', calcularPrecioTotal);
    document.getElementById('cantidadOrden').addEventListener('input', calcularPrecioTotal);

    // Llamar a la función al cargar la página para que se calcule el precio total inicialmente
    calcularPrecioTotal();
</script>
</body>
</html>