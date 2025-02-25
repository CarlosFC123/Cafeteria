<?php

include 'sidebar.php';
require 'db_connection.php';

// Establecer la zona horaria correcta
date_default_timezone_set('America/Merida');

// Obtener los parámetros de filtro
$fechaInicio = $_GET['fechaInicio'] ?? null;
$fechaFin = $_GET['fechaFin'] ?? null;
$turnoFiltro = $_GET['turnoFiltro'] ?? '';

// Consulta para obtener los datos de corte_caja
$queryCorteCaja = "
    SELECT feCorte, turno, montoTotal
    FROM corte_caja
";
$paramsCorteCaja = [];
if ($fechaInicio && $fechaFin) {
    $queryCorteCaja .= " WHERE feCorte BETWEEN :fechaInicio AND :fechaFin";
    $paramsCorteCaja['fechaInicio'] = $fechaInicio;
    $paramsCorteCaja['fechaFin'] = $fechaFin;
}
if (!empty($turnoFiltro)) {
    $queryCorteCaja .= $fechaInicio && $fechaFin ? " AND" : " WHERE";
    $queryCorteCaja .= " turno = :turnoFiltro";
    $paramsCorteCaja['turnoFiltro'] = $turnoFiltro;
}
$queryCorteCaja .= " ORDER BY feCorte DESC";
$stmtCorteCaja = $pdo->prepare($queryCorteCaja);
$stmtCorteCaja->execute($paramsCorteCaja);
$historialCorteCaja = $stmtCorteCaja->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener los datos de ventas
$queryVentas = "
    SELECT DATE(fechaVenta) AS feCorte, turnoVenta AS turno, SUM(totalVenta) AS montoTotal
    FROM ventas
    WHERE estadoVenta = 'Completada'
";
$paramsVentas = [];
if ($fechaInicio && $fechaFin) {
    $queryVentas .= " AND DATE(fechaVenta) BETWEEN :fechaInicio AND :fechaFin";
    $paramsVentas['fechaInicio'] = $fechaInicio;
    $paramsVentas['fechaFin'] = $fechaFin;
}
if (!empty($turnoFiltro)) {
    $queryVentas .= " AND turnoVenta = :turnoFiltro";
    $paramsVentas['turnoFiltro'] = $turnoFiltro;
}
$queryVentas .= " GROUP BY feCorte, turno ORDER BY feCorte DESC";
$stmtVentas = $pdo->prepare($queryVentas);
$stmtVentas->execute($paramsVentas);
$historialVentas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

// Combinar resultados de ambas consultas
$historialCompleto = array_merge($historialCorteCaja, $historialVentas);

// Calcular la suma total de los cortes de caja y ventas
$totalCorteCaja = 0;
$totalVentas = 0;
foreach ($historialCompleto as $corte) {
    if (isset($corte['montoTotal'])) {
        $totalCorteCaja += $corte['montoTotal'];
    } else {
        $totalVentas += $corte['montoTotal'];
    }
}
$totalGeneral = $totalCorteCaja + $totalVentas;
?>

<div class="content">
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">Corte de Caja</h5>
            </div>
            <div class="card-body">
                <form method="GET" id="filtrosForm">
                    <div class="row">
                        <div class="col-md-3 mb-4">
                            <label for="fechaInicio" class="form-label">Fecha Inicio</label>
                            <input type="date" class="form-control" id="fechaInicio" name="fechaInicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label for="fechaFin" class="form-label">Fecha Fin</label>
                            <input type="date" class="form-control" id="fechaFin" name="fechaFin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                        </div>
                        <div class="col-md-3 mb-4">
                            <label for="turnoFiltro" class="form-label">Filtrar por Turno</label>
                            <select class="form-control" id="turnoFiltro" name="turnoFiltro">
                                <option value="">Seleccione un turno</option>
                                <option value="Descanso 1" <?php if ($turnoFiltro === 'Descanso 1') echo 'selected'; ?>>Descanso 1</option>
                                <option value="Descanso 2" <?php if ($turnoFiltro === 'Descanso 2') echo 'selected'; ?>>Descanso 2</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-sm btn-primary me-2">Aplicar Filtros</button>
                            <a href="corte_caja.php" class="btn btn-sm btn-secondary">Limpiar Filtros</a>
                        </div>
                    </div>
                </form>
                <hr>
                <h5 class="mb-3">Historial de Cortes de Caja</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha de Corte</th>
                            <th>Turno</th>
                            <th>Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historialCompleto as $corte): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($corte['feCorte']); ?></td>
                                <td><?php echo htmlspecialchars($corte['turno']); ?></td>
                                <td>$<?php echo number_format($corte['montoTotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td colspan="2"><strong>Total Corte de Caja</strong></td>
                            <td><strong>$<?php echo number_format($totalCorteCaja, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelector('.btn-secondary').addEventListener('click', function() {
        document.getElementById('fechaInicio').value = '';
        document.getElementById('fechaFin').value = '';
        document.getElementById('turnoFiltro').value = '';
    });
</script>