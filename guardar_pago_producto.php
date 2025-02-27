<?php
// guardar_pago_producto.php
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
$nombrePagos = $_POST['nombrePagos'];
$canTotalP = $_POST['canTotalP'];
$fePago = $_POST['fePago'];
$metodoPago = $_POST['metodoPago'];
$cantidad_orden = $_POST['cantidad_orden'];

// Verificar si hay suficiente stock
$sqlCheck = "SELECT cantidadProducto FROM productos_proveedores WHERE nombreProducto = '$nombrePagos'";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['cantidadProducto'] < $cantidad_orden) {
        die("No hay suficiente cantidad para este producto.");
    }
} else {
    die("Producto no encontrado.");
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar en la tabla pagos
    $sqlInsert = "INSERT INTO pagos (nombrePagos, canTotalP, fePago, metodoPago) 
                  VALUES ('$nombrePagos', $canTotalP, '$fePago', '$metodoPago')";
    $conn->query($sqlInsert);

    // Actualizar la cantidad en productos_proveedores
    $sqlUpdate = "UPDATE productos_proveedores 
                  SET cantidadProducto = cantidadProducto - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePagos'";
    $conn->query($sqlUpdate);

    // Confirmar la transacción
    $conn->commit();
    echo "Pago registrado y cantidad actualizada con éxito";
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}

$conn->close();
?>