<?php
// guardar_preventa_desayuno.php
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

// Insertar los datos en la tabla preventa
$sqlInsert = "INSERT INTO preventa (nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalPreventa, metodoPago, tipoComida)
              VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago', 'desayuno')";

if ($conn->query($sqlInsert) === TRUE) {
    // Actualizar la cantidad disponible en la tabla desayuno
    $sqlUpdate = "UPDATE desayuno 
                  SET cantidadDesayuno = cantidadDesayuno - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePreventa'";

    if ($conn->query($sqlUpdate) === TRUE) {
        echo "Compra guardada y cantidad actualizada con éxito";
    } else {
        echo "Error al actualizar la cantidad: " . $conn->error;
    }
} else {
    echo "Error al guardar la compra: " . $conn->error;
}

$conn->close();
?>