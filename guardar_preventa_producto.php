<?php
// guardar_preventa_producto.php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Recibir los datos del formulario
$nombrePreventa = $_POST['nombrePreventa'];
$cantidad_orden = intval($_POST['cantidad_orden']); // Convertir a entero
$precioUnitarioPreventa = floatval($_POST['precioUnitarioPreventa']); // Convertir a flotante
$precioTotalPreventa = floatval($_POST['precioTotalPreventa']); // Convertir a flotante
$metodoPago = $_POST['metodoPago'];

// Validar datos
if (empty($nombrePreventa) || $cantidad_orden <= 0 || $precioUnitarioPreventa <= 0 || $precioTotalPreventa <= 0 || empty($metodoPago)) {
    die("Datos inválidos o incompletos.");
}

// Verificar si hay suficiente stock
$sqlCheck = "SELECT canActual FROM inventario WHERE idProducto = (SELECT idProducto FROM productos WHERE nbProducto = '$nombrePreventa')";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['canActual'] < $cantidad_orden) {
        die("No hay suficiente stock para este producto.");
    }
} else {
    die("Producto no encontrado en el inventario.");
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar en la tabla preventa
    $sqlInsert = "INSERT INTO preventa (nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalPreventa, metodoPago)
                  VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago')";
    if (!$conn->query($sqlInsert)) {
        throw new Exception("Error al insertar en la tabla preventa: " . $conn->error);
    }

    // Actualizar la cantidad en el inventario
    $sqlUpdate = "UPDATE inventario 
                  SET canActual = canActual - $cantidad_orden 
                  WHERE idProducto = (SELECT idProducto FROM productos WHERE nbProducto = '$nombrePreventa')";
    if (!$conn->query($sqlUpdate)) {
        throw new Exception("Error al actualizar el inventario: " . $conn->error);
    }

    // Confirmar la transacción
    $conn->commit();
    echo "Compra guardada y cantidad actualizada con éxito";
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>