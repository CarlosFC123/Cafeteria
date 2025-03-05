<?php
// guardar_pago2.php
header('Content-Type: application/json');

// Conexión a la base de datos
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cafeteria";

$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Conexión fallida: ' . $conn->connect_error]));
}

// Recibir los datos del formulario
$data = json_decode(file_get_contents('php://input'), true);
$nombrePagos = $data['nombrePagos'];
$canTotalP = floatval($data['canTotalP']); // Convertir a flotante
$metodoPago = $data['metodoPago'];
$cantidad_orden = intval($data['cantidad_orden']); // Convertir a entero
$tipoComida = $data['tipoComida']; // porcion, media, orden

// Validar datos
if (empty($nombrePagos) || $canTotalP <= 0 || empty($metodoPago) || $cantidad_orden <= 0 || empty($tipoComida)) {
    die(json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos.']));
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
        die(json_encode(['success' => false, 'message' => 'Tipo de comida no válido.']));
}

// Verificar si hay suficiente stock en la tabla almuerzo
$sqlCheck = "SELECT $columnaRestar FROM almuerzo WHERE nombreProducto = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $nombrePagos);
$stmtCheck->execute();
$result = $stmtCheck->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row[$columnaRestar] < $cantidad_orden) {
        die(json_encode(['success' => false, 'message' => 'No hay suficiente stock para este producto.']));
    }
} else {
    die(json_encode(['success' => false, 'message' => 'Producto no encontrado en el inventario.']));
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
                  VALUES (?, ?, NOW(), ?, NOW(), ?)";
    $stmtInsert = $conn->prepare($sqlInsert);
    $stmtInsert->bind_param("sdss", $nombrePagos, $canTotalP, $metodoPago, $turnoVentaP);
    if (!$stmtInsert->execute()) {
        throw new Exception("Error al insertar en la tabla pagos: " . $stmtInsert->error);
    }

    // Actualizar la cantidad en la tabla almuerzo
    $sqlUpdate = "UPDATE almuerzo 
                  SET $columnaRestar = $columnaRestar - ? 
                  WHERE nombreProducto = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("is", $cantidad_orden, $nombrePagos);
    if (!$stmtUpdate->execute()) {
        throw new Exception("Error al actualizar la cantidad en almuerzo: " . $stmtUpdate->error);
    }

    // Confirmar la transacción
    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Pago registrado y cantidad actualizada con éxito']);
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>