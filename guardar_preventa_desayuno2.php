<?php
// guardar_preventa_desayuno.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(["error" => "Conexión fallida: " . $conn->connect_error]));
}

$data = json_decode(file_get_contents('php://input'), true);

$nombrePreventa = $data['nombrePreventa'];
$cantidad_orden = intval($data['cantidad_orden']);
$precioUnitarioPreventa = floatval($data['precioUnitarioPreventa']);
$precioTotalPreventa = floatval($data['precioTotalPreventa']);
$metodoPago = $data['metodoPago'];

if (empty($nombrePreventa) || $cantidad_orden <= 0 || $precioUnitarioPreventa <= 0 || $precioTotalPreventa <= 0 || empty($metodoPago)) {
    die(json_encode(["error" => "Datos inválidos o incompletos."]));
}

$sqlCheck = "SELECT cantidadDesayuno FROM desayuno WHERE nombreProducto = '$nombrePreventa'";
$result = $conn->query($sqlCheck);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['cantidadDesayuno'] < $cantidad_orden) {
        die(json_encode(["error" => "No hay suficiente stock para este producto."]));
    }
} else {
    die(json_encode(["error" => "Producto no encontrado en el inventario."]));
}

$hora_compra_12h = date("h:i A");
$hora_compra = date("H:i:s", strtotime($hora_compra_12h));

if ($hora_compra >= '7:00:00' && $hora_compra <= '11:59:59') {
    $turnoVentaP = 'Descanso 1';
} elseif ($hora_compra >= '12:00:00' && $hora_compra <= '22:59:59') {
    $turnoVentaP = 'Descanso 2';
} else {
    $turnoVentaP = 'Fuera de horario';
}

$conn->begin_transaction();

try {
    $sqlInsert = "INSERT INTO preventa (nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalPreventa, metodoPago, tipoComida, estado_pv, hora_compra, turnoVentaP)
                  VALUES ('$nombrePreventa', $cantidad_orden, $precioUnitarioPreventa, $precioTotalPreventa, '$metodoPago', 'desayuno', 'Pendiente', NOW(), '$turnoVentaP')";
    if (!$conn->query($sqlInsert)) {
        throw new Exception("Error al insertar en la tabla preventa: " . $conn->error);
    }

    $sqlUpdate = "UPDATE desayuno 
                  SET cantidadDesayuno = cantidadDesayuno - $cantidad_orden 
                  WHERE nombreProducto = '$nombrePreventa'";
    if (!$conn->query($sqlUpdate)) {
        throw new Exception("Error al actualizar la cantidad en desayuno: " . $conn->error);
    }

    $conn->commit();
    echo json_encode(["success" => "Compra guardada y cantidad actualizada con éxito"]);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}

$conn->close();
?>