<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] != 2) {
    header("Location: login.php");
    exit;
}

require 'db_connection.php';

// Verificar si se pasó un ID de producto
if (isset($_GET['id'])) {
    $idProducto = $_GET['id'];
    $producto = $pdo->prepare("SELECT * FROM productos WHERE idProducto = :idProducto");
    $producto->bindParam(':idProducto', $idProducto, PDO::PARAM_INT);
    $producto->execute();
    $producto = $producto->fetch(PDO::FETCH_ASSOC);
    
    if (!$producto) {
        echo "Producto no encontrado.";
        exit;
    }
} else {
    echo "ID de producto no proporcionado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nbProducto = $_POST['nbProducto'];
    $desProducto = $_POST['desProducto'];
    $precioProducto = $_POST['precioProducto'];
    $idCategoria = $_POST['idCategoria'];
    $idTipo = $_POST['idTipo'];
    $idProveedor = $_POST['idProveedor'];
    $codigoBarras = $_POST['codigo_barras'];
    
    // Verificar si el código de barras existe en otro producto
    $checkBarcode = $pdo->prepare("SELECT idProducto FROM productos WHERE codigo_barras = :codigo_barras AND idProducto != :idProducto");
    $checkBarcode->bindParam(':codigo_barras', $codigoBarras);
    $checkBarcode->bindParam(':idProducto', $idProducto);
    $checkBarcode->execute();
    
    if ($checkBarcode->fetch()) {
        // Si existe el código de barras en otro producto, mostrar error
        $error = "El código de barras ya está registrado en otro producto.";
    } else {
        // Proceder con la actualización si el código de barras no existe o pertenece al mismo producto
        try {
            // Subir imagen si es nueva
            if ($_FILES['imgProducto']['name']) {
                $imgProducto = $_FILES['imgProducto'];
                $imgName = time() . '_' . $imgProducto['name'];
                move_uploaded_file($imgProducto['tmp_name'], 'uploads/' . $imgName);
            } else {
                $imgName = $producto['imgProducto'];  // Mantener la imagen actual
            }

            // Actualizar producto en la base de datos
            $update = $pdo->prepare("UPDATE productos SET nbProducto = :nbProducto, desProducto = :desProducto, precioProducto = :precioProducto, idCategoria = :idCategoria, idTipo = :idTipo, idProveedor = :idProveedor, imgProducto = :imgProducto, codigo_barras = :codigo_barras WHERE idProducto = :idProducto");
            $update->bindParam(':nbProducto', $nbProducto);
            $update->bindParam(':desProducto', $desProducto);
            $update->bindParam(':precioProducto', $precioProducto);
            $update->bindParam(':idCategoria', $idCategoria);
            $update->bindParam(':idTipo', $idTipo);
            $update->bindParam(':idProveedor', $idProveedor);
            $update->bindParam(':imgProducto', $imgName);
            $update->bindParam(':codigo_barras', $codigoBarras);
            $update->bindParam(':idProducto', $idProducto);
            $update->execute();

            header("Location: productos.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error al actualizar el producto: " . $e->getMessage();
        }
    }
}

include 'sidebar.php';
// Resto del código HTML permanece igual...
// Obtener listas para los select
$categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
$tipos = $pdo->query("SELECT * FROM tipos_categorias")->fetchAll(PDO::FETCH_ASSOC);
$proveedores = $pdo->query("SELECT * FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
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
            padding: 0.4rem 0.8rem; /* Reducir padding */
        }
        .form-control:focus, .form-select:focus {
            box-shadow: 0 0 0 3px rgba(13,110,253,0.15);
        }
        .img-preview {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 100px; /* Reducir tamaño de la imagen */
            height: auto;
        }
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.2s;
        }
        .back-button:hover {
            transform: translateX(-3px);
        }
        .form-section {
            background: #fff;
            padding: 10px; /* Reducir padding */
            border-radius: 12px;
            margin-bottom: 10px; /* Reducir espacio entre secciones */
        }
        .btn {
            padding: 0.4rem 1.2rem; /* Reducir padding */
            border-radius: 8px;
        }
        .row .col-md-4, .row .col-md-8 {
            padding-left: 5px; /* Reducir padding */
            padding-right: 5px; /* Reducir padding */
        }
    </style>
</head>
<body>
    <div class="container py-3"> <!-- Reducir espacio superior -->
        <div class="card">
            <div class="card-header py-2"> <!-- Reducir padding en el header -->
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Modificar Producto</h5>
                </div>
            </div>
            <div class="card-body p-3"> 
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?><!-- Reducir padding en el body -->
                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <!-- Columna izquierda -->
                        <div class="col-md-8">
                            <div class="form-section">
                                <h6 class="mb-2">Información básica</h6> <!-- Reducir margen -->
                                <div class="col-md-4">
                                    <label class="form-label fw-bold mb-2">Código de barras</label>
                                    <div class="form-label">
                                        <input type="text" name="codigo_barras" id="barcodeInput" class="form-control" 
                                            value="<?php echo htmlspecialchars($producto['codigo_barras']); ?>" 
                                            placeholder="Ingrese el código de barras" required>
                                    </div>
                                    <small class="text-muted mt-1">Escanee o ingrese manualmente el código de barras</small>
                                    <!-- Contenedor para el mensaje de error -->
                                    <div id="mensajeError" class="mt-2"></div>
                                </div>
                                <div class="mb-2"> <!-- Reducir espacio entre elementos -->
                                    <label for="nbProducto" class="form-label">Nombre del Producto</label>
                                    <input type="text" class="form-control" id="nbProducto" name="nbProducto" 
                                           value="<?php echo htmlspecialchars($producto['nbProducto']); ?>" required>
                                </div>
                                <div class="mb-2">
                                    <label for="desProducto" class="form-label">Descripción del Producto</label>
                                    <textarea class="form-control" id="desProducto" name="desProducto" 
                                              rows="3"><?php echo htmlspecialchars($producto['desProducto']); ?></textarea> <!-- Reducir altura -->
                                </div>
                                <div class="mb-2">
                                    <label for="precioProducto" class="form-label">Precio</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" step="0.01" class="form-control" id="precioProducto" 
                                               name="precioProducto" value="<?php echo htmlspecialchars($producto['precioProducto']); ?>" required>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h6 class="mb-2">Clasificación</h6> <!-- Reducir margen -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <label for="idCategoria" class="form-label">Categoría</label>
                                            <select class="form-select" id="idCategoria" name="idCategoria">
                                                <?php foreach ($categorias as $categoria): ?>
                                                    <option value="<?php echo $categoria['idCategoria']; ?>" 
                                                            <?php if ($producto['idCategoria'] == $categoria['idCategoria']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($categoria['nbCategoria']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <label for="idTipo" class="form-label">Tipo</label>
                                            <select class="form-select" id="idTipo" name="idTipo">
                                                <?php foreach ($tipos as $tipo): ?>
                                                    <option value="<?php echo $tipo['idTipo']; ?>" 
                                                            <?php if ($producto['idTipo'] == $tipo['idTipo']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($tipo['nbTipo']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-2">
                                            <label for="idProveedor" class="form-label">Proveedor</label>
                                            <select class="form-select" id="idProveedor" name="idProveedor">
                                                <?php foreach ($proveedores as $proveedor): ?>
                                                    <option value="<?php echo $proveedor['idProveedor']; ?>" 
                                                            <?php if ($producto['idProveedor'] == $proveedor['idProveedor']) echo 'selected'; ?>>
                                                        <?php echo htmlspecialchars($proveedor['nbProveedor']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Columna derecha -->
                        <div class="col-md-4">
                            <div class="form-section">
                                <h6 class="mb-2">Imagen del Producto</h6>
                                <div class="mb-3">
                                    <div class="text-center mb-2">
                                        <img src="uploads/<?php echo htmlspecialchars($producto['imgProducto']); ?>" 
                                             alt="Imagen Actual" class="img-preview mb-2">
                                    </div>
                                    <input type="file" class="form-control" id="imgProducto" name="imgProducto">
                                    <small class="text-muted">Formatos aceptados: JPG, PNG. Máximo 2MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-3"> <!-- Reducir margen -->
                        <a href="productos.php" class="btn btn-light">Cancelar</a>
                        <button type="submit" class="btn btn-primary">
                            <i class='bx bx-save me-1'></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        // Enfocar el campo de código de barras al cargar la página
        $('#barcodeInput').focus();

        // Escuchar cambios en el campo de código de barras
        $('#barcodeInput').on('input', function() {
            var codigoBarras = $(this).val();

            if (codigoBarras.length > 0) {
                // Enviar una solicitud AJAX para verificar el código de barras
                $.ajax({
                    url: 'verificar_codigo_barras2.php',
                    type: 'GET',
                    data: { codigo_barras: codigoBarras },
                    success: function(response) {
                        if (response === "existe") {
                            // Mostrar mensaje de error
                            $('#mensajeError').html(`
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    El código de barras ya está registrado para otro producto.
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            `);
                            // Deshabilitar el botón de guardar
                            $('#btnGuardar').prop('disabled', true);
                        } else {
                            // Ocultar mensaje de error
                            $('#mensajeError').html('');
                            // Habilitar el botón de guardar
                            $('#btnGuardar').prop('disabled', false);
                        }
                    }
                });
            } else {
                // Si el campo está vacío, ocultar el mensaje de error
                $('#mensajeError').html('');
                // Habilitar el botón de guardar
                $('#btnGuardar').prop('disabled', false);
            }
        });
    });
</script>
</body>
</html>
