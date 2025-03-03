<?php
session_start();
require 'db_connection.php';

// Consulta para obtener todos los datos de la tabla pagos
$query = "SELECT nombrePagos, canTotalP, fePago, metodoPago FROM pagos ORDER BY fePago DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$compras = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estilos CSS para la tabla
echo "<style>
    .compras-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-family: Arial, sans-serif;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }
    .compras-table th, .compras-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #ddd;
    }
    .compras-table th {
        background-color: #2a9d8f;
        color: white;
        font-weight: bold;
    }
    .compras-table tr:hover {
        background-color: #f5f5f5;
    }
    .compras-table td {
        color: #333;
    }
</style>";

// Crear la tabla HTML
echo "<table class='compras-table'>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Total</th>
                <th>Fecha</th>
                <th>Método de Pago</th>
            </tr>
        </thead>
        <tbody>";

// Mostrar los datos en filas
foreach ($compras as $compra) {
    echo "<tr>
            <td>{$compra['nombrePagos']}</td>
            <td>$" . number_format($compra['canTotalP'], 2) . "</td>
            <td>{$compra['fePago']}</td>
            <td>{$compra['metodoPago']}</td>
          </tr>";
}

echo "</tbody></table>";
?>