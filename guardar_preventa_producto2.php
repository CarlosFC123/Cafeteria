<?php
// Establecer el encabezado para indicar que la respuesta es JSON
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

// Recibir los datos del formulario
$data = json_decode(file_get_contents('php://input'), true);

$nombrePreventa = $data['nombrePreventa'];
$cantidad_orden = intval($data['cantidad_orden']); // Convertir a entero
$precioUnitarioPreventa = floatval($data['precioUnitarioPreventa']); // Convertir a flotante
$precioTotalPreventa = floatval($data['precioTotalPreventa']); // Convertir a flotante
$metodoPago = $data['metodoPago'];

// Validar datos
if (empty($nombrePreventa) || $cantidad_orden <= 0 || $precioUnitarioPreventa <= 0 || $precioTotalPreventa <= 0 || empty($metodoPago)) {
    die(json_encode(["error" => "Datos inválidos o incompletos."]));
}

// Verificar si hay suficiente stock
$sqlCheck = "SELECT canActual FROM inventario WHERE idProducto = (SELECT idProducto FROM productos WHERE nbProducto = '$nombrePreventa')";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['canActual'] < $cantidad_orden) {
        die(json_encode(["error" => "No hay suficiente stock para este producto."]));
    }
} else {
    die(json_encode(["error" => "Producto no encontrado en el inventario."]));
}

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
                  VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago', 'producto', 'Pendiente', NOW(), '$turnoVentaP')";
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
    echo json_encode(["success" => "Compra guardada y cantidad actualizada con éxito"]);
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}

$conn->close();
?>