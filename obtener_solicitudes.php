<?php
session_start();
require 'db_connection.php';

// Consulta para obtener todas las solicitudes pendientes (estado_pv = 'Pendiente')
$query = "SELECT idPreventa, nombrePreventa, cantidad_orden, precioUnitarioPreventa, precioTotalpreventa, metodoPago, tipoComida, estado_pv, hora_compra 
          FROM preventa 
          WHERE estado_pv = 'Pendiente' 
          ORDER BY hora_compra DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(); // No se pasa ningún parámetro
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estilos CSS para la tabla
echo "<style>
    .solicitudes-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-family: Arial, sans-serif;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    .solicitudes-table th, .solicitudes-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .solicitudes-table th {
        background-color: #2a9d8f;
        color: white;
        font-weight: bold;
    }
    .solicitudes-table tr:hover {
        background-color: #f5f5f5;
    }
    .solicitudes-table td {
        color: #333;
    }
    .cancelar-solicitud {
        background-color: #ff4d4d;
        color: white;
        border: none;
        padding: 8px 12px;
        border-radius: 5px;
        cursor: pointer;
    }
    .cancelar-solicitud:hover {
        background-color: #cc0000;
    }
</style>";

// Crear la tabla HTML
echo "<table class='solicitudes-table'>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Total</th>
                <th>Método de Pago</th>
                <th>Tipo de Comida</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>";

// Mostrar los datos en filas
foreach ($solicitudes as $solicitud) {
    echo "<tr data-id='{$solicitud['idPreventa']}'>
            <td>{$solicitud['nombrePreventa']}</td>
            <td>{$solicitud['cantidad_orden']}</td>
            <td>$" . number_format($solicitud['precioUnitarioPreventa'], 2) . "</td>
            <td>$" . number_format($solicitud['precioTotalpreventa'], 2) . "</td>
            <td>{$solicitud['metodoPago']}</td>
            <td>{$solicitud['tipoComida']}</td>
            <td>{$solicitud['estado_pv']}</td>
            <td>{$solicitud['hora_compra']}</td>
            <td>
                <button class='cancelar-solicitud' data-id='{$solicitud['idPreventa']}'>Cancelar</button>
            </td>
          </tr>";
}

echo "</tbody></table>";
?>