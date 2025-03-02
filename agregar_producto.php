<?php
require 'db_connection.php';


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreProducto = $_POST['nbProducto'];
    $descripcion = $_POST['desProducto'];
    $precio = $_POST['precioProducto'];
    $idCategoria = $_POST['idCategoria'];
    $idProveedor = $_POST['idProveedor'];
    $idTipo = $_POST['idTipo'];
    $codigoBarras = $_POST['codigo_barras'];
    $imgProducto = $_FILES['imgProducto'];
    $target_dir = "uploads/";

    // Crear carpeta si no existe
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Generar un nombre único para la imagen
    $imgFileName = uniqid() . "_" . basename($imgProducto["name"]);
    $target_file = $target_dir . $imgFileName;

    // Verificar si el archivo es una imagen válida
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $valid_extensions = ["jpg", "jpeg", "png", "gif"];

    if (in_array($imageFileType, $valid_extensions)) {
        if (move_uploaded_file($imgProducto["tmp_name"], $target_file)) {
            // Insertar el nuevo producto
            $stmt = $pdo->prepare("INSERT INTO productos (nbProducto, desProducto, precioProducto, idCategoria, idProveedor, idTipo, imgProducto, codigo_barras) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombreProducto, $descripcion, $precio, $idCategoria, $idProveedor, $idTipo, $imgFileName, $codigoBarras]);
            
            $idProducto = $pdo->lastInsertId();

            // Insertar en la tabla productos_proveedores
            $stmtProveedores = $pdo->prepare("INSERT INTO productos_proveedores (idProducto, idProveedor, precio_unitario_compra, cantidad_anterior, cantidadProducto, precioTotalCompra) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtProveedores->execute([$idProducto, $idProveedor, $precio, 0, 0, 0]); // Inicialmente, cantidad y precio total son 0

            // Insertar en la tabla inventario
           // Insertar en la tabla inventario con un valor inicial para canActual
$canActualInicial = 0; // Puedes cambiar este valor según tus necesidades
$stmtInventario = $pdo->prepare("INSERT INTO inventario (idProducto, canActual, feActualizacion, estado_inventario) VALUES (?, ?, NOW(), ?)");
$stmtInventario->execute([$idProducto, $canActualInicial, ($canActualInicial <= 0 ? 'Agotado' : 'Disponible')]);
            // Redirigir a productos.php después de guardar
            header("Location: productos.php");
            exit;
        } else {
            $error = "Error al subir la imagen.";
        }
    } else {
        $error = "Formato de imagen no válido. Solo se permiten JPG, JPEG, PNG y GIF.";
    }
}
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container mt-4">
        <h4>Agregar Producto</h4>
        <div class="card p-4">
            <!-- Contenedor para el mensaje de error -->
            <div id="mensajeError"></div>

            <form method="POST" action="agregar_producto.php" enctype="multipart/form-data">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold mb-2">Código de barras</label>
                        <div class="form-label">
                            <input type="text" name="codigo_barras" id="barcodeInput" class="form-control" placeholder="Ingrese el código de barras" required>
                        </div>
                        <small class="text-muted mt-1">Escanee o ingrese el código de barras</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nombre del Producto</label>
                        <input type="text" name="nbProducto" class="form-control" placeholder="Ingrese el nombre del producto" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Descripción</label>
                        <input type="text" name="desProducto" class="form-control" placeholder="Ingrese la descripción" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Precio</label>
                        <input type="number" step="0.01" name="precioProducto" class="form-control" placeholder="Ingrese el precio" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Categoría</label>
                        <select name="idCategoria" class="form-select" required>
                            <option value="" selected>-- Seleccione una categoría --</option>
                            <?php
                            $categorias = $pdo->query("SELECT idCategoria, nbCategoria FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($categorias as $categoria) {
                                echo "<option value='{$categoria['idCategoria']}'>{$categoria['nbCategoria']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tipo de Categoría</label>
                        <select name="idTipo" class="form-select" required>
                            <option value="" selected>-- Seleccione un tipo --</option>
                            <?php
                            $tipos = $pdo->query("SELECT idTipo, nbTipo FROM tipos_categorias")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tipos as $tipo) {
                                echo "<option value='{$tipo['idTipo']}'>{$tipo['nbTipo']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Proveedor</label>
                        <select name="idProveedor" class="form-select" required>
                            <option value="" selected>-- Seleccione un proveedor --</option>
                            <?php
                            $proveedores = $pdo->query("SELECT idProveedor, nbProveedor FROM proveedores")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($proveedores as $proveedor) {
                                echo "<option value='{$proveedor['idProveedor']}'>{$proveedor['nbProveedor']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Imagen del Producto</label>
                        <input type="file" name="imgProducto" class="form-control" accept="image/*" required>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">Guardar</button>
                        <a href="productos.php" class="btn btn-danger">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("barcodeInput").focus();
        });
        $(document).ready(function() {
            // Escuchar cambios en el campo de código de barras
            $('#barcodeInput').on('input', function() {
                var codigoBarras = $(this).val();

                if (codigoBarras.length > 0) {
                    // Enviar una solicitud AJAX para verificar el código de barras
                    $.ajax({
                        url: 'verificar_codigo_barras.php',
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