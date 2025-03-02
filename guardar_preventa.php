<?php
// guardar_preventa.php

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
$nombrePreventa = $_POST['nombrePreventa'];
$cantidad_orden = $_POST['cantidad_orden'];
$precioUnitarioPreventa = $_POST['precioUnitarioPreventa'];
$precioTotalPreventa = $_POST['precioTotalPreventa'];
$metodoPago = $_POST['metodoPago'];
$tipoComida = $_POST['tipoComida']; // porcion, media, orden

// Determinar el valor de tipoComida para la tabla preventa
$tipoComidaPreventa = 'almuerzo-' . $tipoComida; // Ejemplo: almuerzo-porcion

// Insertar los datos en la tabla preventa
$sqlInsert = "INSERT INTO preventa (nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalPreventa, metodoPago, tipoComida)
              VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago', '$tipoComidaPreventa')";

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
                  WHERE nombreProducto = '$nombrePreventa'"; // Usar nombreProducto en lugar de nombreAlmuerzo

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