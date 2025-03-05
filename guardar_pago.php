<?php
// guardar_pago.php

require 'db_connection.php'; // Incluye la conexión PDO

// Recibir los datos del formulario
$nombrePagos = $_POST['nombrePagos'];
$canTotalP = floatval($_POST['canTotalP']); // Convertir a flotante
$fePago = $_POST['fePago'];
$metodoPago = $_POST['metodoPago'];
$cantidad_orden = intval($_POST['cantidad_orden']); // Convertir a entero
$tipoComida = $_POST['tipoComida']; // porcion, media, orden

// Validar datos
if (empty($nombrePagos) || $canTotalP <= 0 || empty($fePago) || empty($metodoPago) || $cantidad_orden <= 0 || empty($tipoComida)) {
    die(json_encode(["error" => "Datos inválidos o incompletos."]));
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
        die(json_encode(["error" => "Tipo de comida no válido."]));
}

try {
    // Verificar si hay suficiente stock en la tabla almuerzo
    $sqlCheck = "SELECT $columnaRestar FROM almuerzo WHERE nombreProducto = :nombrePagos";
    $stmtCheck = $pdo->prepare($sqlCheck);
    $stmtCheck->execute(['nombrePagos' => $nombrePagos]);
    $row = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        die(json_encode(["error" => "Producto no encontrado en el inventario."]));
    }

    if ($row[$columnaRestar] < $cantidad_orden) {
        die(json_encode(["error" => "No hay suficiente stock para este producto."]));
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
    $pdo->beginTransaction();

    // Insertar en la tabla pagos
    $sqlInsert = "INSERT INTO pagos (nombrePagos, canTotalP, fePago, metodoPago, hora_compra, turnoVentaP) 
                  VALUES (:nombrePagos, :canTotalP, :fePago, :metodoPago, NOW(), :turnoVentaP)";
    $stmtInsert = $pdo->prepare($sqlInsert);
    $stmtInsert->execute([
        'nombrePagos' => $nombrePagos,
        'canTotalP' => $canTotalP,
        'fePago' => $fePago,
        'metodoPago' => $metodoPago,
        'turnoVentaP' => $turnoVentaP
    ]);

    // Actualizar la cantidad en la tabla almuerzo
    $sqlUpdate = "UPDATE almuerzo 
                  SET $columnaRestar = $columnaRestar - :cantidad_orden 
                  WHERE nombreProducto = :nombrePagos";
    $stmtUpdate = $pdo->prepare($sqlUpdate);
    $stmtUpdate->execute([
        'cantidad_orden' => $cantidad_orden,
        'nombrePagos' => $nombrePagos
    ]);

    // Confirmar la transacción
    $pdo->commit();
    echo json_encode(["success" => "Pago registrado y cantidad actualizada con éxito"]);
} catch (PDOException $e) {
    // Revertir la transacción en caso de error
    $pdo->rollBack();
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>