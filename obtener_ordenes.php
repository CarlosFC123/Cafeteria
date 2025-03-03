<?php
session_start();
require 'db_connection.php';

if (!isset($_SESSION['usuario_id'])) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

$query = "SELECT * FROM ordenes WHERE usuario_id = ? ORDER BY fecha_orden DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$ordenes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($ordenes as $orden) {
    echo "<div class='orden-item'>
            <p><strong>Producto:</strong> {$orden['nombreProducto']}</p>
            <p><strong>Total:</strong> {$orden['precioTotal']}</p>
            <p><strong>Fecha:</strong> {$orden['fecha_orden']}</p>
          </div>";
}
?>