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
$canTotalP = floatval($_POST['canTotalP']); // Convertir a flotante
$fePago = $_POST['fePago'];
$metodoPago = $_POST['metodoPago'];
$cantidad_orden = intval($_POST['cantidad_orden']); // Convertir a entero
$tipoComida = $_POST['tipoComida']; // porcion, media, orden

// Validar datos
if (empty($nombrePagos) || $canTotalP <= 0 || empty($fePago) || empty($metodoPago) || $cantidad_orden <= 0 || empty($tipoComida)) {
    die("Datos inválidos o incompletos.");
}

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

// Verificar si hay suficiente stock en la tabla almuerzo
$sqlCheck = "SELECT $columnaRestar FROM almuerzo WHERE nombreProducto = '$nombrePagos'";
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
    // Insertar en la tabla pagos
    $sqlInsert = "INSERT INTO pagos (nombrePagos, canTotalP, fePago, metodoPago, hora_compra, turnoVentaP) 
                  VALUES ('$nombrePagos', $canTotalP, '$fePago', '$metodoPago', NOW(), '$turnoVentaP')";
    if (!$conn->query($sqlInsert)) {
        throw new Exception("Error al insertar en la tabla pagos: " . $conn->error);
    }

    // Actualizar la cantidad en la tabla almuerzo
    $sqlUpdate = "UPDATE almuerzo 
                  SET $columnaRestar = $columnaRestar - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePagos'";
    if (!$conn->query($sqlUpdate)) {
        throw new Exception("Error al actualizar la cantidad en almuerzo: " . $conn->error);
    }

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