<?php
// guardar_pago.php

// Conexión a la base de datos
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
$tipoComida = $_POST['tipoComida']; // porcion, media, orden

// Insertar los datos en la tabla pagos
$sqlInsert = "INSERT INTO pagos (nombrePagos, canTotalP, fePago, metodoPago) 
              VALUES ('$nombrePagos', $canTotalP, '$fePago', '$metodoPago')";

if ($conn->query($sqlInsert) === TRUE) {
    // Determinar qué columna restar según el tipo de comida
    $columnaRestar = '';
    switch ($tipoComida) {
        case 'porcion':
            $columnaRestar = 'cantidadPorcion';
            break;
        case 'media':
            $columnaRestar = 'cantidadMedia';
            break;
        case 'orden':
            $columnaRestar = 'cantidadOrden';
            break;
        default:
            die("Tipo de comida no válido.");
    }

    // Actualizar la cantidad disponible en la tabla almuerzo
    $sqlUpdate = "UPDATE almuerzo 
                  SET $columnaRestar = $columnaRestar - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePagos'";

    if ($conn->query($sqlUpdate) === TRUE) {
        echo "Pago registrado y cantidad actualizada con éxito";
    } else {
        echo "Error al actualizar la cantidad: " . $conn->error;
    }
} else {
    echo "Error al guardar el pago: " . $conn->error;
}

$conn->close();
?>