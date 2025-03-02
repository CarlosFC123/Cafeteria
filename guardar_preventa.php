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
$cantidad_orden = intval($_POST['cantidad_orden']); // Convertir a entero
$precioUnitarioPreventa = floatval($_POST['precioUnitarioPreventa']); // Convertir a flotante
$precioTotalPreventa = floatval($_POST['precioTotalPreventa']); // Convertir a flotante
$metodoPago = $_POST['metodoPago'];
$tipoComida = $_POST['tipoComida']; // porcion, media, orden

// Validar datos
if (empty($nombrePreventa) || $cantidad_orden <= 0 || $precioUnitarioPreventa <= 0 || $precioTotalPreventa <= 0 || empty($metodoPago) || empty($tipoComida)) {
    die("Datos inválidos o incompletos.");
}

// Determinar el valor de tipoComida para la tabla preventa
$tipoComidaPreventa = 'almuerzo-' . $tipoComida; // Ejemplo: almuerzo-porcion

// Verificar si hay suficiente stock en la tabla almuerzo
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

$sqlCheck = "SELECT $columnaRestar FROM almuerzo WHERE nombreProducto = '$nombrePreventa'";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row[$columnaRestar] < $cantidad_orden) {
        die("No hay suficiente stock para este producto.");
    }
} else {
    die("Producto no encontrado en el inventario.");
}

// Obtener la hora actual en formato de 12 horas con AM/PM
$hora_compra_12h = date("h:i A");

// Convertir la hora al formato de 24 horas
$hora_compra = date("H:i:s", strtotime($hora_compra_12h));

// Determinar el turno de venta
if ($hora_compra >= '07:00:00' && $hora_compra <= '11:59:59') {
    $turnoVentaP = 'Descanso 1';
} elseif ($hora_compra >= '12:00:00' && $hora_compra <= '22:59:59') {
    $turnoVentaP = 'Descanso 2';
} else {
    $turnoVentaP = 'Fuera de horario';
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Insertar en la tabla preventa
    $sqlInsert = "INSERT INTO preventa (nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalPreventa, metodoPago, tipoComida, estado_pv, hora_compra, turnoVentaP)
                  VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago', '$tipoComidaPreventa', 'Pendiente', NOW(), '$turnoVentaP')";
    if (!$conn->query($sqlInsert)) {
        throw new Exception("Error al insertar en la tabla preventa: " . $conn->error);
    }

    // Actualizar la cantidad en la tabla almuerzo
    $sqlUpdate = "UPDATE almuerzo 
                  SET $columnaRestar = $columnaRestar - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePreventa'";
    if (!$conn->query($sqlUpdate)) {
        throw new Exception("Error al actualizar la cantidad en almuerzo: " . $conn->error);
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