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
$cantidad_orden = $_POST['cantidad_orden'];
$precioUnitarioPreventa = $_POST['precioUnitarioPreventa'];
$precioTotalPreventa = $_POST['precioTotalPreventa'];
$metodoPago = $_POST['metodoPago'];

// Verificar si hay suficiente stock
$sqlCheck = "SELECT cantidadProducto FROM productos_proveedores WHERE nombreProducto = '$nombrePreventa'";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['cantidadProducto'] < $cantidad_orden) {
        die("No hay suficiente stock para este producto.");
    }
} else {
    die("Producto no encontrado.");
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar en la tabla preventa
    $sqlInsert = "INSERT INTO preventa (nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalPreventa, metodoPago)
                  VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago')";
    $conn->query($sqlInsert);

    // Actualizar la cantidad en productos_proveedores
    $sqlUpdate = "UPDATE productos_proveedores 
                  SET cantidadProducto = cantidadProducto - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePreventa'";
    $conn->query($sqlUpdate);

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